<?php

namespace App\Http\Controllers\Admin;

use App\Exports\BulkCardFailedRowsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkBusinessCardRequest;
use App\Models\CardTemplate;
use App\Models\User;
use App\Services\BulkBusinessCardImportService;
use App\Services\BulkCardExcelTemplateBuilder;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class BulkBusinessCardController extends Controller
{
    public function __construct(
        protected BulkBusinessCardImportService $importService,
        protected BulkCardExcelTemplateBuilder $templateBuilder,
    ) {}

    /**
     * Step1 顯示精靈頁,列出可選 templates 與 user 概況
     */
    public function showWizard()
    {
        $this->ensureAccess();

        $templates = CardTemplate::where('active', true)
            ->orderBy('id')
            ->get();

        $targetUsers = $this->resolveTargetableUsers(Auth::user());

        return view('admin.business_cards.bulk_create.wizard', [
            'templates' => $templates,
            'targetUsers' => $targetUsers,
        ]);
    }

    /**
     * Step2 下載動態 Excel 範本
     */
    public function downloadTemplate(BulkBusinessCardRequest $request)
    {
        $this->ensureAccess();

        $templateIds = array_map('intval', $request->input('template_ids', []));
        $targetUsers = $this->resolveTargetableUsers(Auth::user());

        return $this->templateBuilder->generate($templateIds, $targetUsers);
    }

    /**
     * Step3 上傳並回傳預覽結果(JSON)
     */
    public function preview(BulkBusinessCardRequest $request)
    {
        $this->ensureAccess();

        $file = $request->file('excel');

        try {
            $templateIds = $this->importService->parseTemplateFromMeta($file);
            $rows = $this->importService->parseRows($file, $templateIds);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $result = $this->importService->validateRows($rows, $templateIds, Auth::user());

        $token = (string)Str::uuid();
        Cache::put('bulk_card_preview_' . $token, [
            'template_ids' => $templateIds,
            'validRows' => $result['validRows']->all(),
            'failedRows' => $result['failedRows'],
            'operator_id' => Auth::id(),
        ], now()->addMinutes(30));

        return response()->json([
            'success' => true,
            'token' => $token,
            'template_ids' => $templateIds,
            'totals' => [
                'total' => $rows->count(),
                'valid' => $result['validRows']->count(),
                'failed' => count($result['failedRows']),
            ],
            'validPreview' => $result['validRows']->take(20)->all(),
            'failedRows' => $result['failedRows'],
        ]);
    }

    /**
     * Step4 確認執行
     */
    public function execute(Request $request)
    {
        $this->ensureAccess();

        $token = (string)$request->input('token');
        $cached = Cache::pull('bulk_card_preview_' . $token);

        if (!$cached || $cached['operator_id'] !== Auth::id()) {
            Flash::error('預覽資料已過期,請重新上傳');
            return redirect()->route('admin.businessCards.bulkCreate.wizard');
        }

        $validRows = collect($cached['validRows']);
        $templateIds = $cached['template_ids'];

        $report = $this->importService->execute($validRows, $templateIds, Auth::user());

        $reportToken = (string)Str::uuid();
        Cache::put('bulk_card_report_' . $reportToken, [
            'template_ids' => $templateIds,
            'report' => $report,
            'preview_failed' => $cached['failedRows'],
            'operator_id' => Auth::id(),
        ], now()->addMinutes(60));

        Flash::success(
            '批次建立完成。成功:' . count($report['successRows'])
            . ',略過:' . count($report['skippedRows'])
            . ',失敗:' . (count($report['failedRows']) + count($cached['failedRows']))
        );

        return redirect()->route('admin.businessCards.bulkCreate.result', ['token' => $reportToken]);
    }

    public function showResult(Request $request)
    {
        $this->ensureAccess();

        $token = (string)$request->query('token');
        $cached = Cache::get('bulk_card_report_' . $token);

        if (!$cached || $cached['operator_id'] !== Auth::id()) {
            Flash::error('報告已過期,請重新執行');
            return redirect()->route('admin.businessCards.bulkCreate.wizard');
        }

        return view('admin.business_cards.bulk_create.result', [
            'token' => $token,
            'templateIds' => $cached['template_ids'],
            'report' => $cached['report'],
            'previewFailed' => $cached['preview_failed'],
        ]);
    }

    public function downloadFailedReport(Request $request)
    {
        $this->ensureAccess();

        $token = (string)$request->query('token');
        $cached = Cache::get('bulk_card_report_' . $token);

        if (!$cached || $cached['operator_id'] !== Auth::id()) {
            Flash::error('報告已過期');
            return redirect()->route('admin.businessCards.bulkCreate.wizard');
        }

        $allFailed = array_merge(
            $cached['preview_failed'] ?? [],
            $cached['report']['failedRows'] ?? []
        );

        if (empty($allFailed)) {
            Flash::info('沒有失敗列可供下載');
            return back();
        }

        $filename = 'bulk_cards_failed_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(
            new BulkCardFailedRowsExport($cached['template_ids'], $allFailed),
            $filename
        );
    }

    private function ensureAccess(): void
    {
        $user = Auth::user();
        if (!$user || (!$user->isSuperAdmin() && !$user->isMainUser())) {
            abort(403, '無權使用批次建立功能');
        }
    }

    /**
     * 取得操作者可作為目標的 user 名單(僅限 sub_user)
     */
    private function resolveTargetableUsers(User $operator)
    {
        $query = User::where('role', 'sub_user');

        if (!$operator->isSuperAdmin()) {
            $query->where('parent_id', $operator->id);
        }

        return $query->orderBy('id')->get(['id', 'name', 'email', 'role']);
    }
}
