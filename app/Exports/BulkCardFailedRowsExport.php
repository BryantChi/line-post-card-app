<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class BulkCardFailedRowsExport implements FromArray, WithHeadings, WithTitle
{
    /**
     * @param array<int> $templateIds 與當初範本一致的 template_ids(供未來追蹤用,目前未使用)
     * @param array<int,array> $failedRows 每筆含 row_index / user_id / error_reason / raw_row
     */
    public function __construct(
        protected array $templateIds,
        protected array $failedRows
    ) {}

    public function headings(): array
    {
        return [
            '列號 (cards sheet)',
            'user_id',
            '名片標題',
            '覆蓋模式',
            '失敗原因',
        ];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->failedRows as $row) {
            $raw = $row['raw_row'] ?? [];
            $rows[] = [
                $row['row_index'] ?? '-',
                $row['user_id'] ?? ($raw['user_id'] ?? '-'),
                $raw['card_title'] ?? '-',
                $raw['override_mode'] ?? '-',
                $row['error_reason'] ?? '-',
            ];
        }
        return $rows;
    }

    public function title(): string
    {
        return 'failed_rows';
    }
}
