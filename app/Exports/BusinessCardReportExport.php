<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BusinessCardReportExport implements WithMultipleSheets
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        return [
            new BusinessCardInfoSheet($this->data),
            new BusinessCardStatisticsSheet($this->data),
        ];
    }
}

/**
 * 名片資訊工作表
 */
class BusinessCardInfoSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $card = $this->data['card'];

        return collect([
            [
                $card->title,
                $card->subtitle ?? '-',
                $card->user->name ?? '-',
                $card->active ? '啟用' : '停用',
                $this->data['totalViews'],
                $this->data['totalShares'],
                $this->data['period'],
                $this->data['startDate']->format('Y-m-d'),
                $this->data['endDate']->format('Y-m-d'),
                $card->created_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            '名片標題',
            '副標題',
            '所屬用戶',
            '狀態',
            '總點閱數',
            '總分享數',
            '統計期間',
            '起始日期',
            '結束日期',
            '建立時間',
        ];
    }

    public function title(): string
    {
        return '名片資訊';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

/**
 * 每日統計工作表
 */
class BusinessCardStatisticsSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data['statistics']->map(function ($stat, $index) {
            return [
                $index + 1,
                $stat->date->format('Y-m-d'),
                $stat->date->translatedFormat('l'), // 星期幾
                $stat->views,
                $stat->shares,
            ];
        });
    }

    public function headings(): array
    {
        return [
            '#',
            '日期',
            '星期',
            '點閱數',
            '分享數',
        ];
    }

    public function title(): string
    {
        return '每日統計數據';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
