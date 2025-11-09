<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LoginLogsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $logs;

    public function __construct($logs)
    {
        $this->logs = $logs;
    }

    public function collection()
    {
        return $this->logs;
    }

    public function headings(): array
    {
        return [
            '用戶帳號',
            '備註',
            'Email',
            '登入時間',
            '登出時間',
            '線上時長',
            'IP 位址',
        ];
    }

    public function map($log): array
    {
        return [
            $log->user->name ?? '-',
            $log->user->remarks ?? '-',
            $log->user->email ?? '-',
            $log->logged_in_at->format('Y-m-d H:i:s'),
            $log->logged_out_at ? $log->logged_out_at->format('Y-m-d H:i:s') : '線上',
            $log->formatted_duration ?? '-',
            $log->ip_address ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E9ECEF']
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, // 用戶帳號
            'B' => 40, // 備註
            'C' => 30, // Email
            'D' => 20, // 登入時間
            'E' => 20, // 登出時間
            'F' => 20, // 線上時長
            'G' => 20, // IP 位址
        ];
    }
}
