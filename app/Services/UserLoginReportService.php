<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserLoginLog;
use App\Exports\UserLoginReportExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class UserLoginReportService
{
    /**
     * 產生單一會員登入報表
     *
     * @param User $user
     * @param string|null $startDate
     * @param string|null $endDate
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function generateSingleUserReport(User $user, $startDate = null, $endDate = null)
    {
        // 預設日期範圍:最近 30 天
        $startDate = $startDate ? Carbon::parse($startDate) : Carbon::now()->subDays(30);
        $endDate = $endDate ? Carbon::parse($endDate) : Carbon::now();

        // 預載父帳號關聯,避免 N+1 查詢
        $user->load('parentUser');

        // 取得登入紀錄
        $loginLogs = UserLoginLog::where('user_id', $user->id)
            ->dateRange($startDate, $endDate)
            ->orderBy('logged_in_at', 'desc')
            ->get();

        // 準備資料
        $data = [
            'users' => collect([$user]), // 轉換為 Collection,才能使用 map()
            'loginLogs' => [$user->id => $loginLogs],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'isBatch' => false,
        ];

        // 產生 Excel (清理檔名防止路徑穿越攻擊)
        $safeName = preg_replace('/[^a-zA-Z0-9\x{4e00}-\x{9fa5}_-]/u', '_', $user->name);
        $fileName = sprintf(
            '會員登入紀錄_%s_%s.xlsx',
            $safeName,
            Carbon::now()->format('YmdHis')
        );

        return Excel::download(new UserLoginReportExport($data), $fileName);
    }

    /**
     * 產生多個會員登入報表 (批次下載)
     *
     * @param array $userIds
     * @param string|null $startDate
     * @param string|null $endDate
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function generateBatchReport(array $userIds, $startDate = null, $endDate = null)
    {
        // 預設日期範圍:最近 30 天
        $startDate = $startDate ? Carbon::parse($startDate) : Carbon::now()->subDays(30);
        $endDate = $endDate ? Carbon::parse($endDate) : Carbon::now();

        // 取得所有會員(預載父帳號關聯)
        $users = User::whereIn('id', $userIds)
            ->where('role', 'sub_user')
            ->with('parentUser')
            ->get();

        // 取得所有會員的登入紀錄
        $loginLogs = [];
        foreach ($users as $user) {
            $loginLogs[$user->id] = UserLoginLog::where('user_id', $user->id)
                ->dateRange($startDate, $endDate)
                ->orderBy('logged_in_at', 'desc')
                ->get();
        }

        // 準備資料
        $data = [
            'users' => $users,
            'loginLogs' => $loginLogs,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'isBatch' => true,
        ];

        // 產生 Excel
        $fileName = sprintf(
            '批次會員登入紀錄_%s.xlsx',
            Carbon::now()->format('YmdHis')
        );

        return Excel::download(new UserLoginReportExport($data), $fileName);
    }
}
