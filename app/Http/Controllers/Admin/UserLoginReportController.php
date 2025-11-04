<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserLoginReportService;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;

class UserLoginReportController extends Controller
{
    protected $reportService;

    public function __construct(UserLoginReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * 下載單一會員登入紀錄
     *
     * @param Request $request
     * @param int $user 用戶ID
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadSingle(Request $request, $user)
    {
        try {
            // 查詢用戶並確認存在且為子帳號
            $userModel = User::where('id', $user)
                ->where('role', 'sub_user')
                ->first();

            if (!$userModel) {
                Flash::error('會員不存在或無權限下載');
                return redirect()->route('sub-users.index');
            }

            // 驗證日期格式
            $validated = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ], [
                'end_date.after_or_equal' => '結束日期不能早於開始日期',
            ]);

            $startDate = $validated['start_date'] ?? null;
            $endDate = $validated['end_date'] ?? null;

            return $this->reportService->generateSingleUserReport($userModel, $startDate, $endDate);
        } catch (\Exception $e) {
            \Log::error('下載登入紀錄失敗', [
                'user_id' => $user,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Flash::error('下載失敗: ' . $e->getMessage());
            return redirect()->route('sub-users.index');
        }
    }

    /**
     * 批次下載會員登入紀錄
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadBatch(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1|max:50',
            'user_ids.*' => 'exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ], [
            'user_ids.required' => '請至少選擇一個會員',
            'user_ids.max' => '批次下載最多支援 50 個會員',
            'user_ids.*.exists' => '選擇的會員不存在',
            'end_date.after_or_equal' => '結束日期不能早於開始日期',
        ]);

        $userIds = $validated['user_ids'];
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;

        // 確認所有選擇的都是子帳號
        $invalidCount = User::whereIn('id', $userIds)
            ->where('role', '!=', 'sub_user')
            ->count();

        if ($invalidCount > 0) {
            Flash::error('僅能下載子帳號的登入紀錄');
            return redirect()->route('sub-users.index');
        }

        return $this->reportService->generateBatchReport($userIds, $startDate, $endDate);
    }
}
