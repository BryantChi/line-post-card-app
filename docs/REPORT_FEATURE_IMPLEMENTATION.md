# AI數位名片報表下載功能 - 完整實作指南

本文件包含所有需要建立和修改的檔案內容,請按照順序執行。

---

## 📋 已完成項目

- ✅ 建立 `business_card_statistics` 資料表 Migration
- ✅ 建立 `BusinessCardStatistic` Model
- ✅ 更新 `BusinessCard` Model 關聯
- ✅ 安裝 Laravel Excel 套件 (maatwebsite/excel)

---

## 🔧 待執行項目

### 步驟 1: 執行 Migration

```bash
php artisan migrate
```

這將建立 `business_card_statistics` 資料表。

---

### 步驟 2: 發布 Laravel Excel 設定檔 (選用)

```bash
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config
```

---

### 步驟 3: 建立報表服務

建立檔案: `app/Services/BusinessCardReportService.php`

```php
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
```

---

### 步驟 4: 建立 Excel Export 類別

建立檔案: `app/Exports/BusinessCardReportExport.php`

```php
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
```

---

### 步驟 5: 在 Controller 新增報表下載方法

編輯: `app/Http/Controllers/Admin/BusinessCardsController.php`

在類別最後新增以下方法:

```php
use App\Services\BusinessCardReportService;

// 在 __construct 中注入服務
protected $reportService;

public function __construct(BusinessCardsRepository $businessCardsRepo, CustomFlexMessageBuilder $flexBuilder, BusinessCardReportService $reportService)
{
    $this->businessCardsRepository = $businessCardsRepo;
    $this->flexBuilder = $flexBuilder;
    $this->reportService = $reportService;
}

/**
 * 下載本週報表
 */
public function downloadWeeklyReport($id)
{
    $businessCard = BusinessCard::findOrFail($id);

    // 檢查權限
    if (!$businessCard->canBeViewedBy(Auth::user())) {
        abort(403, '無權限查看此名片');
    }

    return $this->reportService->generateWeeklyReport($businessCard);
}

/**
 * 下載本月報表
 */
public function downloadMonthlyReport($id)
{
    $businessCard = BusinessCard::findOrFail($id);

    // 檢查權限
    if (!$businessCard->canBeViewedBy(Auth::user())) {
        abort(403, '無權限查看此名片');
    }

    return $this->reportService->generateMonthlyReport($businessCard);
}

/**
 * 下載自訂區間報表
 */
public function downloadCustomReport(Request $request, $id)
{
    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ]);

    $businessCard = BusinessCard::findOrFail($id);

    // 檢查權限
    if (!$businessCard->canBeViewedBy(Auth::user())) {
        abort(403, '無權限查看此名片');
    }

    $startDate = Carbon::parse($request->start_date);
    $endDate = Carbon::parse($request->end_date);

    return $this->reportService->generateCustomReport(
        $businessCard,
        $startDate,
        $endDate,
        '自訂區間'
    );
}
```

---

### 步驟 6: 新增路由

編輯: `routes/web.php`

在 BusinessCards 相關路由區塊新增:

```php
// 報表下載路由
Route::get('businessCards/{id}/report/weekly', [BusinessCardsController::class, 'downloadWeeklyReport'])
    ->name('admin.businessCards.report.weekly');
Route::get('businessCards/{id}/report/monthly', [BusinessCardsController::class, 'downloadMonthlyReport'])
    ->name('admin.businessCards.report.monthly');
Route::post('businessCards/{id}/report/custom', [BusinessCardsController::class, 'downloadCustomReport'])
    ->name('admin.businessCards.report.custom');
```

---

### 步驟 7: 修改名片詳情頁,新增下載按鈕

編輯: `resources/views/admin/business_cards/show.blade.php`

在第 35 行「預覽」按鈕之後新增:

```html
<div class="btn-group my-1">
    <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            data-step="5" data-intro="點擊這裡可以下載本週、本月或自訂區間的點閱/分享數據報表。">
        <i class="fas fa-download"></i> 下載報表
    </button>
    <div class="dropdown-menu">
        <a class="dropdown-item" href="{{ route('admin.businessCards.report.weekly', $businessCard->id) }}">
            <i class="fas fa-calendar-week"></i> 本週報表
        </a>
        <a class="dropdown-item" href="{{ route('admin.businessCards.report.monthly', $businessCard->id) }}">
            <i class="fas fa-calendar-alt"></i> 本月報表
        </a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#customReportModal">
            <i class="fas fa-calendar-plus"></i> 自訂區間報表
        </a>
    </div>
</div>
```

---

### 步驟 8: 新增自訂區間報表 Modal

在 `show.blade.php` 檔案最後 (在 `@endsection` 之前) 新增:

```html
<!-- 自訂區間報表 Modal -->
<div class="modal fade" id="customReportModal" tabindex="-1" role="dialog" aria-labelledby="customReportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.businessCards.report.custom', $businessCard->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="customReportModalLabel">下載自訂區間報表</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="span">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="start_date">起始日期</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required
                               max="{{ date('Y-m-d') }}"
                               value="{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label for="end_date">結束日期</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" required
                               max="{{ date('Y-m-d') }}"
                               value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        請選擇要下載報表的日期區間。報表將包含該區間內每日的點閱數和分享數統計。
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-download"></i> 下載報表
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

---

### 步驟 9: 修改點閱/分享記錄邏輯

找到記錄點閱數的 Controller 方法 (通常在處理分享頁面的 Controller 中),並修改如下:

**範例** (需根據實際專案調整):

```php
use App\Models\BusinessCardStatistic;

// 原本的點閱記錄
$businessCard->increment('views');

// 新增:同時記錄到統計表
BusinessCardStatistic::recordView($businessCard->id);
```

**分享記錄**:

```php
// 原本的分享記錄
$businessCard->increment('shares');

// 新增:同時記錄到統計表
BusinessCardStatistic::recordShare($businessCard->id);
```

---

## 🎯 測試步驟

### 1. 執行 Migration

```bash
php artisan migrate
```

### 2. 清除快取

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### 3. 測試報表下載

1. 進入任何一張名片的詳情頁
2. 點擊「下載報表」按鈕
3. 選擇「本週報表」或「本月報表」
4. 確認下載的 Excel 檔案包含兩個工作表:
   - 工作表1: 名片資訊
   - 工作表2: 每日統計數據

### 4. 測試自訂區間報表

1. 點擊「自訂區間報表」
2. 選擇起始和結束日期
3. 點擊「下載報表」
4. 確認報表正確產生

---

## 🐛 可能遇到的問題

### 問題 1: Class 'Maatwebsite\Excel\Facades\Excel' not found

**解決**:
```bash
composer dump-autoload
php artisan config:clear
```

### 問題 2: 報表下載後沒有數據

**原因**: 統計表中還沒有數據

**解決**:
1. 先訪問名片分享頁面產生點閱記錄
2. 或手動在資料庫插入測試數據:

```sql
INSERT INTO business_card_statistics (business_card_id, date, views, shares, created_at, updated_at)
VALUES (1, CURDATE(), 10, 5, NOW(), NOW());
```

### 問題 3: 路由找不到

**解決**:
```bash
php artisan route:list | grep report
php artisan route:clear
```

---

## 📊 報表範例

下載的 Excel 檔案將包含以下內容:

**工作表 1 - 名片資訊**
| 名片標題 | 副標題 | 所屬用戶 | 狀態 | 總點閱數 | 總分享數 | 統計期間 | 起始日期 | 結束日期 | 建立時間 |
|---------|--------|---------|------|---------|---------|---------|---------|---------|---------|
| 示範名片 | 副標題 | 使用者A | 啟用 | 150 | 30 | 本週 | 2025-10-01 | 2025-10-07 | 2025-09-01 10:00:00 |

**工作表 2 - 每日統計數據**
| # | 日期 | 星期 | 點閱數 | 分享數 |
|---|------|------|-------|--------|
| 1 | 2025-10-01 | 星期二 | 20 | 5 |
| 2 | 2025-10-02 | 星期三 | 35 | 8 |
| ... | ... | ... | ... | ... |

---

## 🔄 後續優化建議

1. **新增圖表**: 在 Excel 中自動產生趨勢圖表
2. **排程任務**: 定期產生並寄送週報/月報給用戶
3. **更多統計**: 新增訪客來源、裝置類型等統計
4. **匯出格式**: 支援 PDF、CSV 等其他格式

---

**完成以上步驟後,報表下載功能即可正常使用! 📊✅**
