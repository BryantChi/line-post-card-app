<?php

namespace App\Services;

use App\Models\CardTemplate;
use App\Models\User;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkCardExcelTemplateBuilder
{
    public const META_SHEET = '__meta__';
    public const CARDS_SHEET = 'cards';
    public const USER_LIST_SHEET = 'user_list';
    public const META_VERSION = 2; // bumped from 1 (multi-sheet structure)
    public const DATA_START_ROW = 3;

    /** cards 主檔 sheet 的欄位 */
    public const CARD_FIELDS = [
        ['key' => 'user_id',       'label' => '使用者',       'required' => true,  'help' => '必填:從下拉選「ID - 姓名 (email)」'],
        ['key' => 'user_name',     'label' => '姓名(自動)', 'required' => false, 'help' => '由 INDEX/MATCH 自動帶出,勿手動修改'],
        ['key' => 'user_email',    'label' => 'Email(自動)','required' => false, 'help' => '由 INDEX/MATCH 自動帶出,勿手動修改'],
        ['key' => 'override_mode', 'label' => '覆蓋模式',     'required' => true,  'help' => '必填:從下拉選「跳過 / 追加 / 取代」'],
        ['key' => 'card_title',    'label' => '名片標題',     'required' => true,  'help' => '必填'],
        ['key' => 'card_subtitle', 'label' => '名片副標題',   'required' => false, 'help' => ''],
        ['key' => 'card_content',  'label' => '名片描述',     'required' => false, 'help' => ''],
    ];

    public const OVERRIDE_MODE_OPTIONS = [
        '跳過' => 'skip',
        '追加' => 'append',
        '取代' => 'replace',
    ];

    public static function bubbleSheetName(int $bubbleNum): string
    {
        return 'bubble' . $bubbleNum;
    }

    /**
     * 計算 template 的可填欄位摘要(供 Step1 顯示用)
     *
     * @return array{fillable_count:int, image_count:int, image_keys:array<string>}
     */
    public static function summarizeTemplateFields(CardTemplate $template): array
    {
        $allFields = self::rawFields($template);

        $imageKeys = [];
        $fillableCount = 0;
        foreach ($allFields as $f) {
            if (self::isImageField($f)) {
                $imageKeys[] = $f['key'];
            } else {
                $fillableCount++;
            }
        }

        return [
            'fillable_count' => $fillableCount,
            'image_count' => count($imageKeys),
            'image_keys' => $imageKeys,
        ];
    }

    /**
     * 取得 template 過濾掉圖片類後的可填欄位
     *
     * @return array<int,array{key:string,label:string,required:bool,default:mixed,type:string}>
     */
    public static function extractTemplateFields(CardTemplate $template): array
    {
        $fields = self::rawFields($template);
        return array_values(array_filter($fields, fn($f) => !self::isImageField($f)));
    }

    /**
     * 從 editable_fields 或 schema 抽出原始欄位列表(未過濾圖片)
     */
    private static function rawFields(CardTemplate $template): array
    {
        $editable = $template->getEditableFields();
        if (is_array($editable) && !empty($editable)) {
            return array_map(function ($key, $settings) {
                return [
                    'key' => $key,
                    'label' => $settings['label'] ?? $key,
                    'required' => (bool)($settings['required'] ?? false),
                    'default' => $settings['default'] ?? null,
                    'type' => $settings['type'] ?? 'text',
                ];
            }, array_keys($editable), $editable);
        }

        $schema = is_array($template->template_schema)
            ? json_encode($template->template_schema, JSON_UNESCAPED_UNICODE)
            : (string)$template->template_schema;
        preg_match_all('/\{\{([A-Za-z0-9_]+)\}\}/', $schema, $matches);
        $keys = array_values(array_unique($matches[1] ?? []));

        return array_map(fn($k) => [
            'key' => $k,
            'label' => $k,
            'required' => false,
            'default' => null,
            'type' => str_contains(strtolower($k), 'image') ? 'image_url' : 'text',
        ], $keys);
    }

    /**
     * 判斷欄位是否為圖片類(用於批次匯入時整個略過)
     */
    public static function isImageField(array $field): bool
    {
        $type = strtolower((string)($field['type'] ?? 'text'));
        if (in_array($type, ['image', 'image_url', 'file', 'photo', 'picture'], true)) {
            return true;
        }

        $key = strtolower((string)($field['key'] ?? ''));
        foreach (['image', 'photo', 'pic', 'avatar', 'logo', 'icon'] as $needle) {
            if (str_contains($key, $needle)) {
                return true;
            }
        }

        $label = (string)($field['label'] ?? '');
        if (str_contains($label, '圖片') || str_contains($label, '圖像')
            || str_contains($label, '頭像') || str_contains($label, '照片')) {
            return true;
        }
        $labelLower = strtolower($label);
        foreach (['image', 'photo', 'avatar', 'logo', 'icon'] as $needle) {
            if (str_contains($labelLower, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 產生 .xlsx 下載 response
     *  - cards sheet:名片主檔(每個 user 一列)
     *  - bubble1 / bubble2 ...:每張 bubble 一個 sheet(用 user_id 為 key)
     *  - user_list:可選 user 名單(含 display 拼接字串)
     *  - __meta__:隱藏 metadata
     *
     * @param array<int> $templateIds 選定的 template ids,順序代表 bubble 順序
     * @param Collection<int,User> $userOptions
     */
    public function generate(array $templateIds, Collection $userOptions): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $userSheet = $this->createUserListSheet($spreadsheet, $userOptions);
        $userLastRow = max($userSheet->getHighestDataRow(), 2);

        $this->createCardsSheet($spreadsheet, $userLastRow);

        $templates = CardTemplate::whereIn('id', $templateIds)->get()->keyBy('id');
        foreach ($templateIds as $index => $tid) {
            $bubbleNum = $index + 1;
            $template = $templates->get($tid);
            $this->createBubbleSheet($spreadsheet, $bubbleNum, $template, $userLastRow);
        }

        $this->createMetaSheet($spreadsheet, $templateIds);

        $spreadsheet->setActiveSheetIndexByName(self::CARDS_SHEET);

        $filename = 'bulk_cards_template_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function createUserListSheet(Spreadsheet $spreadsheet, Collection $userOptions): Worksheet
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle(self::USER_LIST_SHEET);
        $sheet->fromArray(['user_id', 'name', 'email', 'display'], null, 'A1');
        $sheet->getStyle('A1:D1')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');
        $row = 2;
        foreach ($userOptions as $u) {
            $sheet->setCellValue('A' . $row, $u->id);
            $sheet->setCellValue('B' . $row, $u->name);
            $sheet->setCellValue('C' . $row, $u->email);
            $sheet->setCellValue('D' . $row, $u->id . ' - ' . $u->name . ' (' . $u->email . ')');
            $row++;
        }
        $sheet->getColumnDimension('D')->setWidth(40);
        return $sheet;
    }

    private function createCardsSheet(Spreadsheet $spreadsheet, int $userLastRow): Worksheet
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle(self::CARDS_SHEET);

        $this->writeSheetHeaders($sheet, self::CARD_FIELDS);
        $this->applyUserIdValidation($sheet, self::CARD_FIELDS, $userLastRow);
        $this->applyUserAutoFill($sheet, self::CARD_FIELDS, $userLastRow);
        $this->applyOverrideValidation($sheet, self::CARD_FIELDS);

        $sheet->freezePane('B' . self::DATA_START_ROW); // 凍結 user_id 欄,捲動時看得到
        return $sheet;
    }

    private function createBubbleSheet(Spreadsheet $spreadsheet, int $bubbleNum, ?CardTemplate $template, int $userLastRow): Worksheet
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle(self::bubbleSheetName($bubbleNum));

        $fields = [
            ['key' => 'user_id',   'label' => '使用者',      'required' => true,  'help' => '必填:從下拉選擇,與 cards 工作表對應'],
            ['key' => 'user_name', 'label' => '姓名(自動)','required' => false, 'help' => '自動帶出'],
            ['key' => 'title',     'label' => '卡片標題',    'required' => true,  'help' => '必填:此卡片在後台管理列表顯示的標題'],
        ];

        if ($template) {
            $sheet->setCellValue('A1', '(對應模板:#' . $template->id . ' ' . $template->name . ')');
            $sheet->getStyle('A1')->getFont()->setBold(true);
            $sheet->mergeCells('A1:D1');
            // 把實際 header 改到 row 2,資料起始改 row 4 — 但為了一致仍用 DATA_START_ROW(3)
            // 採另一策略:header row=2、help row=3、data 從 row 4 起
        }

        $templateFields = $template ? self::extractTemplateFields($template) : [];
        foreach ($templateFields as $f) {
            $fields[] = [
                'key' => $f['key'],
                'label' => $f['label'],
                'required' => $f['required'],
                'help' => '套用模板:' . ($template->name ?? '')
                    . ($f['default'] !== null ? '(預設:' . $f['default'] . ')' : ''),
            ];
        }

        // 重置:在 bubble sheet 中 row 1 是模板註記,row 2 是欄位 header,row 3 是說明,row 4 起為資料
        $this->writeBubbleSheetHeaders($sheet, $fields);
        $this->applyUserIdValidation($sheet, $fields, $userLastRow, 4);
        $this->applyUserAutoFill($sheet, $fields, $userLastRow, 4);

        $sheet->freezePane('B4');
        return $sheet;
    }

    /**
     * cards sheet header(row 1 label / row 2 help / data 從 row 3)
     */
    private function writeSheetHeaders(Worksheet $sheet, array $fields): void
    {
        $col = 1;
        foreach ($fields as $f) {
            $sheet->getCellByColumnAndRow($col, 1)
                ->setValue($f['label'] . ($f['required'] ? ' *' : ''));
            $sheet->getCellByColumnAndRow($col, 2)->setValue($f['help'] ?? '');

            if (!empty($f['required'])) {
                $sheet->getCellByColumnAndRow($col, 1)
                    ->getStyle()->getFont()->getColor()->setARGB('FFCC0000');
            }
            $sheet->getCellByColumnAndRow($col, 2)
                ->getStyle()->getFont()->setItalic(true)->getColor()->setARGB('FF888888');

            if ($f['key'] === 'user_id') {
                $sheet->getColumnDimensionByColumn($col)->setWidth(38);
            } else {
                $sheet->getColumnDimensionByColumn($col)->setWidth(22);
            }
            $col++;
        }
        $sheet->getRowDimension(1)->setRowHeight(22);
    }

    /**
     * bubble sheet header(row 1 是模板註記 / row 2 label / row 3 help / data 從 row 4)
     */
    private function writeBubbleSheetHeaders(Worksheet $sheet, array $fields): void
    {
        $col = 1;
        foreach ($fields as $f) {
            $sheet->getCellByColumnAndRow($col, 2)
                ->setValue($f['label'] . ($f['required'] ? ' *' : ''));
            $sheet->getCellByColumnAndRow($col, 3)->setValue($f['help'] ?? '');

            if (!empty($f['required'])) {
                $sheet->getCellByColumnAndRow($col, 2)
                    ->getStyle()->getFont()->getColor()->setARGB('FFCC0000');
            }
            $sheet->getCellByColumnAndRow($col, 3)
                ->getStyle()->getFont()->setItalic(true)->getColor()->setARGB('FF888888');

            if ($f['key'] === 'user_id') {
                $sheet->getColumnDimensionByColumn($col)->setWidth(38);
            } else {
                $sheet->getColumnDimensionByColumn($col)->setWidth(22);
            }
            $col++;
        }
        $sheet->getRowDimension(1)->setRowHeight(18);
        $sheet->getRowDimension(2)->setRowHeight(22);
    }

    /**
     * 套用 user_id 下拉(來源:user_list!$D$2:$D$N)
     */
    private function applyUserIdValidation(Worksheet $sheet, array $fields, int $userLastRow, int $dataStartRow = self::DATA_START_ROW): void
    {
        $colIdx = $this->columnIndex($fields, 'user_id');
        if ($colIdx === null) return;

        $colLetter = Coordinate::stringFromColumnIndex($colIdx);
        for ($r = $dataStartRow; $r <= $dataStartRow + 200; $r++) {
            $validation = $sheet->getCell($colLetter . $r)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowDropDown(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('使用者無效');
            $validation->setError('請從下拉選擇使用者');
            $validation->setFormula1('user_list!$D$2:$D$' . $userLastRow);
        }
    }

    /**
     * 套用 user_name / user_email 的 INDEX/MATCH 自動帶
     */
    private function applyUserAutoFill(Worksheet $sheet, array $fields, int $userLastRow, int $dataStartRow = self::DATA_START_ROW): void
    {
        $userIdCol = $this->columnIndex($fields, 'user_id');
        $userNameCol = $this->columnIndex($fields, 'user_name');
        $userEmailCol = $this->columnIndex($fields, 'user_email');
        if ($userIdCol === null || ($userNameCol === null && $userEmailCol === null)) return;

        $userIdLetter = Coordinate::stringFromColumnIndex($userIdCol);

        for ($r = $dataStartRow; $r <= $dataStartRow + 200; $r++) {
            $userIdRef = $userIdLetter . $r;
            if ($userNameCol !== null) {
                $col = Coordinate::stringFromColumnIndex($userNameCol);
                $sheet->setCellValue(
                    $col . $r,
                    sprintf('=IFERROR(INDEX(user_list!$B$2:$B$%d,MATCH(%s,user_list!$D$2:$D$%d,0)),"")',
                        $userLastRow, $userIdRef, $userLastRow)
                );
            }
            if ($userEmailCol !== null) {
                $col = Coordinate::stringFromColumnIndex($userEmailCol);
                $sheet->setCellValue(
                    $col . $r,
                    sprintf('=IFERROR(INDEX(user_list!$C$2:$C$%d,MATCH(%s,user_list!$D$2:$D$%d,0)),"")',
                        $userLastRow, $userIdRef, $userLastRow)
                );
            }
        }

        foreach ([$userNameCol, $userEmailCol] as $autoCol) {
            if ($autoCol === null) continue;
            $col = Coordinate::stringFromColumnIndex($autoCol);
            $sheet->getStyle($col . $dataStartRow . ':' . $col . ($dataStartRow + 200))
                ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF5F5F5');
        }
    }

    /**
     * 套用 override_mode 中文下拉(僅 cards sheet 用)
     */
    private function applyOverrideValidation(Worksheet $sheet, array $fields): void
    {
        $colIdx = $this->columnIndex($fields, 'override_mode');
        if ($colIdx === null) return;

        $colLetter = Coordinate::stringFromColumnIndex($colIdx);
        $optionsList = implode(',', array_keys(self::OVERRIDE_MODE_OPTIONS));
        for ($r = self::DATA_START_ROW; $r <= self::DATA_START_ROW + 200; $r++) {
            $validation = $sheet->getCell($colLetter . $r)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(false);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"' . $optionsList . '"');
        }
        $sheet->getCellByColumnAndRow($colIdx, 2)->setValue(
            '跳過=已有名片時不動 / 追加=在現有名片加 bubble / 取代=刪除舊名片重建'
        );
    }

    private function createMetaSheet(Spreadsheet $spreadsheet, array $templateIds): void
    {
        $meta = $spreadsheet->createSheet();
        $meta->setTitle(self::META_SHEET);
        $meta->setCellValue('A1', json_encode([
            'template_ids' => array_map('intval', $templateIds),
            'version' => self::META_VERSION,
        ], JSON_UNESCAPED_UNICODE));
        $meta->setSheetState(Worksheet::SHEETSTATE_HIDDEN);
    }

    private function columnIndex(array $fields, string $key): ?int
    {
        foreach ($fields as $i => $f) {
            if (($f['key'] ?? null) === $key) {
                return $i + 1;
            }
        }
        return null;
    }
}
