<?php

namespace App\Services;

use App\Models\BusinessCard;
use App\Models\BusinessCardStatistic;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BusinessCardReportExport;

class BusinessCardReportService
{
    /**
     * 產生本週報表
     */
    public function generateWeeklyReport(BusinessCard $card)
    {
        $startDate = Carbon::now()->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();

        return $this->generateCustomReport($card, $startDate, $endDate, '本週');
    }

    /**
     * 產生本月報表
     */
    public function generateMonthlyReport(BusinessCard $card)
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        return $this->generateCustomReport($card, $startDate, $endDate, '本月');
    }

    /**
     * 產生自訂區間報表
     */
    public function generateCustomReport(BusinessCard $card, $startDate, $endDate, $period = '自訂區間')
    {
        // 取得統計數據
        $statistics = BusinessCardStatistic::where('business_card_id', $card->id)
            ->dateRange($startDate, $endDate)
            ->orderBy('date')
            ->get();

        // 計算總計
        $totalViews = $statistics->sum('views');
        $totalShares = $statistics->sum('shares');

        // 準備資料
        $data = [
            'card' => $card,
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'statistics' => $statistics,
            'totalViews' => $totalViews,
            'totalShares' => $totalShares,
        ];

        // 產生 Excel
        $fileName = sprintf(
            '%s_報表_%s_%s.xlsx',
            $card->title,
            $period,
            Carbon::now()->format('YmdHis')
        );

        return Excel::download(new BusinessCardReportExport($data), $fileName);
    }
}
