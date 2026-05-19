<?php

namespace App\Services;

use App\Models\BusinessCard;
use App\Models\CardBubble;
use App\Models\CardTemplate;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class BulkBusinessCardImportService
{
    public function __construct(protected CustomFlexMessageBuilder $flexBuilder) {}

    /**
     * 從 Excel 的 __meta__ 工作表取出 template_ids
     *
     * @throws \RuntimeException 若範本檔損毀
     */
    public function parseTemplateFromMeta(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::createReaderForFile($file->getRealPath())->load($file->getRealPath());
        $meta = $spreadsheet->getSheetByName(BulkCardExcelTemplateBuilder::META_SHEET);
        if (!$meta) {
            throw new \RuntimeException('範本檔損毀:缺少 __meta__ 工作表,請重新下載範本');
        }

        $raw = $meta->getCell('A1')->getValue();
        $data = json_decode((string)$raw, true);

        if (!is_array($data) || !isset($data['template_ids']) || !is_array($data['template_ids'])) {
            throw new \RuntimeException('範本檔損毀:__meta__ 內容不正確');
        }

        return array_values(array_map('intval', $data['template_ids']));
    }

    /**
     * 解析 cards 工作表(row 1 header / row 2 說明 / row 3+ 資料),回傳 normalized rows
     *
     * 每筆 row:[ 'row_index' => int, 'raw_row' => array(by header key) ]
     */
    public function parseRows(UploadedFile $file, array $templateIds): Collection
    {
        $spreadsheet = IOFactory::createReaderForFile($file->getRealPath())->load($file->getRealPath());

        $cardsSheet = $spreadsheet->getSheetByName(BulkCardExcelTemplateBuilder::CARDS_SHEET);
        if (!$cardsSheet) {
            throw new \RuntimeException('範本檔損毀:缺少 cards 工作表');
        }

        $overrideMap = BulkCardExcelTemplateBuilder::OVERRIDE_MODE_OPTIONS;

        // Step 1: 讀 cards 主表(每 user_id 一列)
        $cardFields = BulkCardExcelTemplateBuilder::CARD_FIELDS;
        $cardFieldKeys = array_map(fn($f) => $f['key'], $cardFields);
        $rowsByUserId = []; // user_id => ['row_index'=>..., 'raw_row'=>main fields]

        $highest = $cardsSheet->getHighestDataRow();
        for ($r = BulkCardExcelTemplateBuilder::DATA_START_ROW; $r <= $highest; $r++) {
            $raw = [];
            $hasData = false;
            foreach ($cardFieldKeys as $i => $key) {
                $val = $cardsSheet->getCellByColumnAndRow($i + 1, $r)->getCalculatedValue();
                $val = is_string($val) ? trim($val) : $val;
                $raw[$key] = $val;
                if ($val !== null && $val !== '') $hasData = true;
            }
            if (!$hasData) continue;

            $userId = $this->parseUserId($raw['user_id'] ?? null);
            if ($userId !== null) {
                $raw['user_id'] = $userId;
            }

            $mode = $raw['override_mode'] ?? null;
            if (is_string($mode) && isset($overrideMap[$mode])) {
                $raw['override_mode'] = $overrideMap[$mode];
            }

            $key = $userId !== null ? (string)$userId : ('__no_user_' . $r);
            $rowsByUserId[$key] = [
                'row_index' => $r,
                'sheet' => BulkCardExcelTemplateBuilder::CARDS_SHEET,
                'raw_row' => $raw,
            ];
        }

        // Step 2: 依序讀每張 bubble{N} sheet,以 user_id 為 key 合併
        $templates = \App\Models\CardTemplate::whereIn('id', $templateIds)->get()->keyBy('id');

        foreach ($templateIds as $index => $tid) {
            $bubbleNum = $index + 1;
            $bubbleSheetName = BulkCardExcelTemplateBuilder::bubbleSheetName($bubbleNum);
            $bubbleSheet = $spreadsheet->getSheetByName($bubbleSheetName);
            if (!$bubbleSheet) continue;

            $template = $templates->get($tid);
            $bubbleFieldKeys = ['user_id', 'user_name'];
            if ($template) {
                foreach (BulkCardExcelTemplateBuilder::extractTemplateFields($template) as $f) {
                    $bubbleFieldKeys[] = $f['key'];
                }
            }

            // bubble sheet 的 data 從 row 4 起(row 1 是模板註記、row 2 label、row 3 help)
            $bubbleDataStart = 4;
            $bubbleHighest = $bubbleSheet->getHighestDataRow();

            for ($r = $bubbleDataStart; $r <= $bubbleHighest; $r++) {
                $bubbleRowUserId = $this->parseUserId(
                    $bubbleSheet->getCellByColumnAndRow(1, $r)->getCalculatedValue()
                );
                if ($bubbleRowUserId === null) continue;

                $userKey = (string)$bubbleRowUserId;
                if (!isset($rowsByUserId[$userKey])) continue; // bubble 有但 cards 沒,略過

                foreach ($bubbleFieldKeys as $i => $fieldKey) {
                    if ($fieldKey === 'user_id' || $fieldKey === 'user_name') continue;
                    $val = $bubbleSheet->getCellByColumnAndRow($i + 1, $r)->getCalculatedValue();
                    $val = is_string($val) ? trim($val) : $val;
                    $rowsByUserId[$userKey]['raw_row']["bubble{$bubbleNum}_{$fieldKey}"] = $val;
                }
            }
        }

        return collect(array_values($rowsByUserId));
    }

    /**
     * user_id 欄位可能是 int 或 "5 - 姓名 (email)" 拼接字串
     */
    private function parseUserId($value): ?int
    {
        if (is_int($value)) return $value;
        if (is_numeric($value)) return (int)$value;
        if (is_string($value) && preg_match('/^\s*(\d+)\s*-/', $value, $m)) {
            return (int)$m[1];
        }
        return null;
    }

    /**
     * 驗證列,回傳 ['validRows' => Collection, 'failedRows' => array]
     */
    public function validateRows(Collection $rows, array $templateIds, User $operator): array
    {
        $valid = collect();
        $failed = [];

        $userIds = $rows->pluck('raw_row.user_id')
            ->map(fn($v) => is_numeric($v) ? (int)$v : null)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $users = User::whereIn('id', $userIds)
            ->get()
            ->keyBy('id');

        $existingCardsCount = BusinessCard::whereIn('user_id', $userIds)
            ->selectRaw('user_id, COUNT(*) as cnt')
            ->groupBy('user_id')
            ->pluck('cnt', 'user_id')
            ->all();

        $existingActiveCard = BusinessCard::whereIn('user_id', $userIds)
            ->where('active', true)
            ->orderByDesc('id')
            ->get()
            ->keyBy('user_id');

        $templates = CardTemplate::whereIn('id', $templateIds)->get()->keyBy('id');

        foreach ($rows as $row) {
            $reason = $this->validateSingleRow(
                $row,
                $templateIds,
                $templates,
                $users,
                $existingCardsCount,
                $existingActiveCard,
                $operator
            );

            if ($reason === null) {
                $valid->push($row);
            } else {
                $failed[] = [
                    'row_index' => $row['row_index'],
                    'user_id' => $row['raw_row']['user_id'] ?? null,
                    'error_reason' => $reason,
                    'raw_row' => $row['raw_row'],
                ];
            }
        }

        return ['validRows' => $valid, 'failedRows' => $failed];
    }

    private function validateSingleRow(
        array $row,
        array $templateIds,
        Collection $templates,
        Collection $users,
        array $existingCardsCount,
        Collection $existingActiveCard,
        User $operator
    ): ?string {
        $raw = $row['raw_row'];

        $userId = isset($raw['user_id']) && is_numeric($raw['user_id']) ? (int)$raw['user_id'] : null;
        if (!$userId) {
            return 'user_id 為空或格式錯誤';
        }
        $user = $users->get($userId);
        if (!$user) {
            return "找不到 user_id={$userId}";
        }

        if (!$this->canOperatorTarget($operator, $user)) {
            return '操作者無權限對該 user 建立名片';
        }

        $mode = $raw['override_mode'] ?? null;
        if (!in_array($mode, ['skip', 'append', 'replace'], true)) {
            return '覆蓋模式必須為「跳過 / 追加 / 取代」';
        }

        if (empty($raw['card_title'])) {
            return '名片標題(card_title)必填';
        }

        // 驗證 cards 主檔其他 required 欄位
        foreach (BulkCardExcelTemplateBuilder::CARD_FIELDS as $f) {
            if (!empty($f['required']) && !in_array($f['key'], ['user_id', 'override_mode', 'card_title'], true)) {
                $v = $raw[$f['key']] ?? null;
                if ($v === null || $v === '') {
                    return "{$f['label']} 必填";
                }
            }
        }

        // 驗證每張 bubble 的 required 欄位
        foreach ($templateIds as $idx => $tid) {
            if (!$templates->has($tid)) {
                return "模板 ID {$tid} 不存在或已停用";
            }
            $bubbleNum = $idx + 1;
            $template = $templates->get($tid);
            foreach (BulkCardExcelTemplateBuilder::extractTemplateFields($template) as $f) {
                if (!empty($f['required'])) {
                    $key = "bubble{$bubbleNum}_{$f['key']}";
                    $v = $raw[$key] ?? null;
                    if ($v === null || $v === '') {
                        return "卡片{$bubbleNum}「{$f['label']}」必填(請在 {$bubbleNum} 工作表 bubble{$bubbleNum} 填寫)";
                    }
                }
            }
        }

        $hasActiveCard = $existingActiveCard->has($userId);
        $bubbleCountNeeded = count($templateIds);
        $maxBubbles = $user->getMaxCardBubbles();

        if ($mode === 'skip') {
            if ($hasActiveCard) {
                return null;
            }
            if (!$user->canCreateBusinessCard()) {
                return "user_id={$userId} 已達名片數量上限,無法新建";
            }
            if ($bubbleCountNeeded > $maxBubbles) {
                return "卡片數({$bubbleCountNeeded}) 超過 user 上限({$maxBubbles})";
            }
        }

        if ($mode === 'append') {
            $card = $existingActiveCard->get($userId);
            if (!$card) {
                if (!$user->canCreateBusinessCard()) {
                    return "user_id={$userId} 無現有名片且已達數量上限";
                }
                if ($bubbleCountNeeded > $maxBubbles) {
                    return "卡片數({$bubbleCountNeeded}) 超過 user 上限({$maxBubbles})";
                }
            } else {
                $existing = $card->bubbles()->count();
                if ($existing + $bubbleCountNeeded > $maxBubbles) {
                    return "append 後卡片數(" . ($existing + $bubbleCountNeeded) . ") 超過 user 上限({$maxBubbles})";
                }
            }
        }

        if ($mode === 'replace') {
            if ($bubbleCountNeeded > $maxBubbles) {
                return "卡片數({$bubbleCountNeeded}) 超過 user 上限({$maxBubbles})";
            }
        }

        return null;
    }

    private function canOperatorTarget(User $operator, User $target): bool
    {
        // 目標必須是 sub_user(批次建立僅針對子帳號)
        if (!$target->isSubUser()) {
            return false;
        }
        if ($operator->isSuperAdmin()) {
            return true;
        }
        if ($operator->isMainUser()) {
            return $target->parent_id === $operator->id;
        }
        return false;
    }

    /**
     * 執行批次建立,回傳報告:
     *  - successRows: array of ['row_index','user_id','card_id','mode']
     *  - failedRows:  array of ['row_index','user_id','error_reason','raw_row']
     *  - skippedRows: array of ['row_index','user_id','reason']
     */
    public function execute(Collection $validRows, array $templateIds, User $operator): array
    {
        $success = [];
        $failed = [];
        $skipped = [];

        $userIds = $validRows->pluck('raw_row.user_id')->map(fn($v) => (int)$v)->unique()->values()->all();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');
        $templates = CardTemplate::whereIn('id', $templateIds)->get()->keyBy('id');

        foreach ($validRows as $row) {
            $raw = $row['raw_row'];
            $userId = (int)$raw['user_id'];
            $mode = $raw['override_mode'];
            $user = $users->get($userId);

            if (!$user) {
                $failed[] = [
                    'row_index' => $row['row_index'],
                    'user_id' => $userId,
                    'error_reason' => "user_id={$userId} 在執行階段已不存在",
                    'raw_row' => $raw,
                ];
                continue;
            }

            try {
                $result = DB::transaction(function () use ($user, $row, $mode, $templateIds, $templates) {
                    return $this->createForUser($user, $row, $mode, $templateIds, $templates);
                });

                if ($result['status'] === 'skipped') {
                    $skipped[] = [
                        'row_index' => $row['row_index'],
                        'user_id' => $userId,
                        'reason' => $result['reason'],
                    ];
                } else {
                    $success[] = [
                        'row_index' => $row['row_index'],
                        'user_id' => $userId,
                        'card_id' => $result['card_id'],
                        'mode' => $mode,
                    ];
                }
            } catch (Throwable $e) {
                Log::error('BulkBusinessCardImport row failed', [
                    'row_index' => $row['row_index'],
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
                $failed[] = [
                    'row_index' => $row['row_index'],
                    'user_id' => $userId,
                    'error_reason' => '執行例外:' . $e->getMessage(),
                    'raw_row' => $raw,
                ];
            }
        }

        return [
            'successRows' => $success,
            'failedRows' => $failed,
            'skippedRows' => $skipped,
        ];
    }

    /**
     * 單一 user 的建立流程,回傳 ['status' => 'created'|'skipped', 'card_id' => int|null, 'reason' => string|null]
     */
    private function createForUser(User $user, array $row, string $mode, array $templateIds, Collection $templates): array
    {
        $raw = $row['raw_row'];
        $existingActive = BusinessCard::where('user_id', $user->id)
            ->where('active', true)
            ->orderByDesc('id')
            ->first();

        if ($mode === 'skip' && $existingActive) {
            return ['status' => 'skipped', 'card_id' => null, 'reason' => '已有 active 名片,依設定略過'];
        }

        if ($mode === 'replace' && $existingActive) {
            $existingActive->bubbles()->delete();
            $existingActive->delete();
            $existingActive = null;
        }

        if ($mode === 'append' && $existingActive) {
            $card = $existingActive;
            $startOrder = (int)CardBubble::where('card_id', $card->id)->max('order');
        } else {
            $card = BusinessCard::create([
                'user_id' => $user->id,
                'title' => (string)($raw['card_title'] ?? ''),
                'subtitle' => $raw['card_subtitle'] ?: null,
                'profile_image' => $raw['card_profile_image'] ?: null,
                'content' => $raw['card_content'] ?: null,
                'active' => true,
            ]);
            $startOrder = 0;
        }

        foreach ($templateIds as $idx => $tid) {
            $bubbleNum = $idx + 1;
            $template = $templates->get($tid);

            $bubbleData = $this->extractBubbleData($raw, $bubbleNum, $template);

            $jsonContent = $this->flexBuilder->buildBubbleJson($tid, $bubbleData);

            CardBubble::create([
                'card_id' => $card->id,
                'template_id' => $tid,
                'title' => $bubbleData['title'] ?? null,
                'subtitle' => $bubbleData['subtitle'] ?? null,
                'image' => $bubbleData['image'] ?? null,
                'content' => $bubbleData['content'] ?? null,
                'bubble_data' => $bubbleData,
                'json_content' => $jsonContent,
                'order' => $startOrder + $idx + 1,
                'active' => true,
            ]);
        }

        $card->refresh();
        $card->updateFlexJson();

        return ['status' => 'created', 'card_id' => $card->id, 'reason' => null];
    }

    /**
     * 從 row 中抽出某張 bubble 的欄位資料(把 bubble{N}_xxx 前綴拿掉,套用 default)
     * 對 template_schema 中所有 {{xxx}} placeholder 補上空字串 fallback,
     * 避免渲染後留下未替換的 {{...}} 字串(尤其圖片欄位已從 Excel 移除)。
     */
    private function extractBubbleData(array $raw, int $bubbleNum, ?CardTemplate $template): array
    {
        $prefix = "bubble{$bubbleNum}_";
        $data = [];

        foreach ($raw as $key => $value) {
            if (strpos($key, $prefix) === 0) {
                $fieldKey = substr($key, strlen($prefix));
                $data[$fieldKey] = $value;
            }
        }

        if ($template) {
            $fields = BulkCardExcelTemplateBuilder::extractTemplateFields($template);
            foreach ($fields as $f) {
                if ((!isset($data[$f['key']]) || $data[$f['key']] === null || $data[$f['key']] === '')
                    && $f['default'] !== null) {
                    $data[$f['key']] = $f['default'];
                }
            }

            $schema = is_array($template->template_schema)
                ? json_encode($template->template_schema, JSON_UNESCAPED_UNICODE)
                : (string)$template->template_schema;
            preg_match_all('/\{\{([A-Za-z0-9_]+)\}\}/', $schema, $matches);
            foreach (array_unique($matches[1] ?? []) as $placeholder) {
                if (!array_key_exists($placeholder, $data) || $data[$placeholder] === null) {
                    $data[$placeholder] = '';
                }
            }
        }

        return $data;
    }
}
