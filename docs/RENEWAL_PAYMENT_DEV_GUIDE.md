# 會員續約金流系統 — 開發完整技術文件

> 版本：1.1
> 日期：2026-03-27
> 技術棧：Laravel 10 / PHP 8.2 / ECPay SDK 1.3.2506240
> 功能範疇：訂閱方案管理、續約訂單、ECPay 信用卡、匯款/現金離線付款
>
> **v1.1 變更摘要：** 修復 ECPay 跨域 POST 導致 session 被覆蓋問題；採用 PRG 模式重構付款結果頁；新增 `ecpay` 無 session middleware 群組；修補付款結果頁 IDOR 漏洞。

---

## 目錄

1. [系統架構概覽](#一系統架構概覽)
2. [資料庫設計與 Migration](#二資料庫設計與-migration)
3. [Model 層：三個核心模型](#三model-層三個核心模型)
4. [Config 層：設定檔](#四config-層設定檔)
5. [Service 層：業務邏輯核心](#五service-層業務邏輯核心)
6. [Controller 層：請求處理](#六controller-層請求處理)
7. [路由設計](#七路由設計)
8. [前端視圖](#八前端視圖)
9. [安全機制詳解](#九安全機制詳解)
10. [ECPay 付款時序圖](#十ecpay-付款時序圖)
11. [排程命令](#十一排程命令)
12. [ECPay SDK 版本資訊](#十二ecpay-sdk-版本資訊)
13. [已知問題與修復記錄](#十三已知問題與修復記錄)

---

## 一、系統架構概覽

### 1.1 功能角色分工

| 角色 | 可執行的操作 |
|------|-------------|
| 超級管理員 | 管理訂閱方案（CRUD）、查看所有訂單、確認付款、手動延長任意用戶到期日 |
| 主帳號 | 查看/管理自己子帳號的訂單、代辦建立訂單、確認付款 |
| 子帳號 | 自助選擇方案付款（信用卡/匯款）、查看自己的訂單歷史 |

### 1.2 付款方式

| 方式 | 流程 | 誰確認 |
|------|------|--------|
| `ecpay_credit` | 跳轉綠界 → 刷卡 → ECPay notify 自動確認 | 系統自動 |
| `bank_transfer` | 顯示帳號 → 會員匯款 → 上傳收據 → 管理員確認 | 管理員手動 |
| `cash` | 管理員直接建立 → 確認收款 | 管理員手動 |

### 1.3 資料流

```
[會員選方案] → RenewalService::createOrder() → renewal_orders (pending)
                                                        │
                    ┌───────────────────────────────────┤
                    │ ecpay_credit                      │ bank_transfer/cash
                    ▼                                   ▼
        EcpayService::buildCheckoutForm()     管理員確認 → RenewalService::confirmOfflinePayment()
                    │
                    ▼ (ECPay 伺服器 notify)
        EcpayService::processCallback()
                    │
                    ▼
        renewal_orders (paid) + payment_transactions (success) + users.expires_at 延長
```

### 1.4 新增的檔案清單

```
app/
├── Services/
│   ├── EcpayService.php              # ECPay SDK 封裝
│   └── RenewalService.php            # 續約業務邏輯
├── Http/
│   ├── Controllers/
│   │   ├── EcpayCallbackController.php   # ECPay 回呼
│   │   ├── RenewalController.php         # 會員自助續約
│   │   └── Admin/
│   │       ├── SubscriptionPlanController.php  # 方案 CRUD
│   │       └── RenewalOrderController.php      # 訂單管理
├── Models/
│   ├── SubscriptionPlan.php
│   ├── RenewalOrder.php
│   └── PaymentTransaction.php
├── Console/Commands/
│   └── ExpireStaleOrders.php         # 逾期訂單清理排程
└── Mail/
    ├── RenewalOrderCreated.php       # 通知管理員（匯款訂單）
    └── RenewalPaymentConfirmed.php   # 通知會員（付款確認）

config/
├── ecpay.php                         # ECPay 設定
└── renewal.php                       # 續約系統設定

database/migrations/
├── 2026_03_24_000001_create_subscription_plans_table.php
├── 2026_03_24_000002_create_renewal_orders_table.php
├── 2026_03_24_000003_create_payment_transactions_table.php
└── 2026_03_24_100000_add_unique_to_transaction_no_in_payment_transactions.php

routes/
├── web.php                           # 一般 web 路由（含 session / CSRF）
└── ecpay.php                         # ECPay 回呼路由（無 session，獨立群組）

resources/views/
├── renewal/
│   ├── index.blade.php               # 會員續約主頁
│   ├── ecpay_redirect.blade.php      # 跳轉綠界頁
│   ├── bank_transfer.blade.php       # 匯款資訊頁
│   ├── history.blade.php             # 訂單歷史
│   ├── order_detail.blade.php        # 訂單詳情
│   └── payment_result.blade.php      # 信用卡付款結果頁（後台 layout，需登入）
├── ecpay/
│   └── result.blade.php              # 備用獨立結果頁（無需登入，目前未使用）
└── admin/
    ├── subscription_plans/
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   ├── edit.blade.php
    │   ├── fields.blade.php
    │   └── table.blade.php
    └── renewal_orders/
        ├── index.blade.php
        ├── show.blade.php
        ├── create_for_user.blade.php
        ├── manual_extend.blade.php
        └── table.blade.php
```

---

## 二、資料庫設計與 Migration

### 2.1 `subscription_plans`（訂閱方案表）

**檔案：** `database/migrations/2026_03_24_000001_create_subscription_plans_table.php`

```php
Schema::create('subscription_plans', function (Blueprint $table) {
    $table->id();
    $table->string('name', 100);         // 方案名稱，如「月繳方案」
    $table->text('description')->nullable(); // 方案說明
    $table->unsignedInteger('price');    // 價格（新台幣整數）
    $table->unsignedInteger('duration_days'); // 有效天數（30/90/365）
    $table->boolean('active')->default(true); // 是否啟用
    $table->unsignedInteger('sort_order')->default(0); // 排序權重
    $table->timestamps();
});
```

**設計重點：**
- `price` 使用 `unsignedInteger`（不是 `decimal`）：台灣訂閱方案通常為整數金額，無小數位需求，避免浮點數精度問題
- `active` 欄位：方案下架時不刪除，保留歷史訂單的 `plan_id` 關聯完整性
- `sort_order`：前端方案卡片的顯示順序，預設按 sort_order ASC 排列

### 2.2 `renewal_orders`（續約訂單表）

**檔案：** `database/migrations/2026_03_24_000002_create_renewal_orders_table.php`

```php
Schema::create('renewal_orders', function (Blueprint $table) {
    $table->id();

    // ── 訂單識別 ────────────────────────────────────────────────
    $table->string('order_no', 20)->unique();
    // 格式：RN + YmdHis（14碼）+ 4位補零亂數 = 20碼
    // 符合 ECPay MerchantTradeNo 最多 20 碼的限制

    // ── 關聯 ─────────────────────────────────────────────────────
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    // 子帳號被刪除時，其訂單一併刪除

    $table->foreignId('plan_id')->constrained('subscription_plans')->onDelete('restrict');
    // 有訂單的方案不可刪除（RESTRICT）

    $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
    // NULL = 會員自助建立；有值 = 管理員代辦建立
    // 管理員被刪除時設為 NULL（不影響訂單）

    // ── 金流資訊 ─────────────────────────────────────────────────
    $table->unsignedInteger('amount');
    // 建立訂單時從方案複製價格，防止方案改價影響已建訂單

    $table->enum('payment_method', ['ecpay_credit', 'bank_transfer', 'cash']);
    // 三種付款方式

    $table->enum('status', ['pending', 'paid', 'cancelled', 'expired'])->default('pending');
    // pending：待付款
    // paid：已付款（終態）
    // cancelled：已取消（終態）
    // expired：逾期未付（終態）

    // ── 時間戳 ───────────────────────────────────────────────────
    $table->timestamp('paid_at')->nullable();   // 付款完成時間
    $table->timestamp('expires_at')->nullable(); // 訂單逾期時間（≠ 用戶到期日）
    // pending 訂單超過此時間視為逾期，由排程自動標記 expired

    // ── 附加資訊 ─────────────────────────────────────────────────
    $table->string('receipt_image')->nullable(); // 匯款證明圖片路徑
    $table->text('admin_note')->nullable();      // 管理員備註
    $table->timestamps();
});
```

**設計重點：**
- **`amount` 複製自方案**：建立訂單時就固定金額，方案後來漲價或降價不影響已建訂單
- **`expires_at` vs `users.expires_at`**：`renewal_orders.expires_at` 是「訂單有效期」（pending 訂單最多等 72 小時），`users.expires_at` 是「帳號到期日」，兩者語義完全不同
- **`created_by` nullable**：區分自助（NULL）與代辦（有值），用於審計追蹤
- **onDelete 策略**：
  - `user_id` → CASCADE（用戶刪除，訂單一起消失）
  - `plan_id` → RESTRICT（防止刪除有訂單的方案）
  - `created_by` → SET NULL（管理員刪除，訂單仍保留，只是失去「誰建立」的資訊）

### 2.3 `payment_transactions`（付款交易記錄表）

**檔案：** `database/migrations/2026_03_24_000003_create_payment_transactions_table.php`

```php
Schema::create('payment_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained('renewal_orders')->onDelete('cascade');
    // 訂單刪除時，交易記錄一併刪除

    $table->string('transaction_no', 50)->nullable();
    // ECPay 交易編號（TradeNo）或手動參考號（MANUAL-xxx）
    // nullable：失敗的交易可能沒有 transaction_no

    $table->string('payment_method', 30);   // ecpay_credit / bank_transfer / cash
    $table->unsignedInteger('amount');      // 交易金額
    $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
    $table->json('gateway_response')->nullable();
    // ECPay 回傳的已過濾資料（白名單欄位）
    // 手動付款此欄位為 NULL

    $table->text('note')->nullable();       // 備註（錯誤訊息 / 管理員說明）
    $table->timestamps();
});
```

**為什麼要拆「訂單」和「交易」？**

一筆訂單可能有多次付款嘗試：
1. 信用卡付款失敗（建立 `status=failed` 的 transaction）
2. 重試後成功（建立 `status=success` 的 transaction）
3. ECPay 重發 notify（冪等保護，不建立重複 transaction）

訂單記錄「意圖」，交易記錄「行為」。

### 2.4 `transaction_no` 唯一索引

**檔案：** `database/migrations/2026_03_24_100000_add_unique_to_transaction_no_in_payment_transactions.php`

```php
Schema::table('payment_transactions', function (Blueprint $table) {
    $table->unique('transaction_no', 'uniq_payment_transaction_no');
    // MySQL 允許 nullable unique index：多個 NULL 不衝突
    // 此設計防止同一個 ECPay TradeNo 被重複記錄（重放攻擊防護）
});
```

---

## 三、Model 層：三個核心模型

### 3.1 `SubscriptionPlan.php`

```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    // ── $fillable 白名單 ────────────────────────────────────────
    // 防止 Mass Assignment 攻擊（只允許這些欄位被批量賦值）
    protected $fillable = [
        'name', 'description', 'price', 'duration_days', 'active', 'sort_order',
    ];

    // ── $casts 型別轉換 ─────────────────────────────────────────
    // 讀取時自動轉換型別，避免 "1"/"0" 字串比較問題
    protected $casts = [
        'active'        => 'boolean',
        'price'         => 'integer',
        'duration_days' => 'integer',
        'sort_order'    => 'integer',
    ];

    // ── 關聯 ──────────────────────────────────────────────────
    public function renewalOrders()
    {
        return $this->hasMany(RenewalOrder::class, 'plan_id');
        // 用於 SubscriptionPlanController::destroy() 檢查是否有活躍訂單
    }

    // ── 查詢 Scope ─────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('active', true);
        // 使用：SubscriptionPlan::active()->get()
        // 只取啟用中的方案，停用方案不顯示給會員選擇
    }
}
```

### 3.2 `RenewalOrder.php`

```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RenewalOrder extends Model
{
    protected $fillable = [
        'order_no', 'user_id', 'plan_id', 'created_by', 'amount',
        'payment_method', 'status', 'paid_at', 'expires_at',
        'receipt_image', 'admin_note',
    ];

    protected $casts = [
        'paid_at'    => 'datetime', // 自動轉為 Carbon 物件，支援 ->format()
        'expires_at' => 'datetime',
        'amount'     => 'integer',
    ];

    // ── 終態常數 ────────────────────────────────────────────────
    // 這些狀態一旦設定，不應再變更
    // 程式碼中統一使用此常數，避免硬編碼字串
    const TERMINAL_STATUSES = ['paid', 'cancelled', 'expired'];

    // ── 關聯 ──────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
        // $order->user → 取得續約的子帳號
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
        // $order->plan → 取得方案資訊（名稱、天數、價格）
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
        // $order->createdBy → 取得建立者（管理員）
        // 管理員被刪除時此關聯回傳 null（因 created_by 設為 SET NULL）
    }

    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class, 'order_id');
        // $order->transactions → 取得所有付款嘗試記錄
    }

    // ── 業務方法 ───────────────────────────────────────────────
    public function isTerminal(): bool
    {
        // 判斷訂單是否在終態（已付款/已取消/已逾期）
        // 終態訂單不應被再次操作
        return in_array($this->status, self::TERMINAL_STATUSES);
    }

    // ── 訂單號生成 ─────────────────────────────────────────────
    public static function generateOrderNo(): string
    {
        do {
            // RN + YmdHis（14碼）+ 4位補零亂數 = 20碼
            // 格式範例：RN20260326143022 + 0847 = RN202603261430220847
            $orderNo = 'RN' . now()->format('YmdHis')
                     . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('order_no', $orderNo)->exists());
        // do-while：處理極低機率的碰撞（同秒內 >9999 筆訂單）
        // DB 有 unique 約束作為最終保護

        return $orderNo;
    }
}
```

### 3.3 `PaymentTransaction.php`

```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'order_id', 'transaction_no', 'payment_method',
        'amount', 'status', 'gateway_response', 'note',
    ];

    protected $casts = [
        'amount'           => 'integer',
        'gateway_response' => 'array',
        // JSON 欄位自動在讀取時 decode、寫入時 encode
        // $transaction->gateway_response 直接取得 PHP array
    ];

    public function order()
    {
        return $this->belongsTo(RenewalOrder::class, 'order_id');
    }
}
```

---

## 四、Config 層：設定檔

### 4.1 `config/ecpay.php`

```php
<?php
return [
    // ── 商店認證 ───────────────────────────────────────────────
    'merchant_id' => env('ECPAY_MERCHANT_ID', '3002607'),
    // 測試預設值為綠界官方示範商店 3002607

    'hash_key' => env('ECPAY_HASH_KEY'),
    // 用於 CheckMacValue 計算的簽章金鑰
    // 不設預設值，強制必須在 .env 設定

    'hash_iv' => env('ECPAY_HASH_IV'),
    // 同上，初始向量

    // ── 付款閘道 ───────────────────────────────────────────────
    'gateway_url' => env(
        'ECPAY_PAYMENT_GATEWAY_URL',
        'https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V5'
    ),
    // 預設測試環境，正式環境需修改為：
    // https://payment.ecpay.com.tw/Cashier/AioCheckOut/V5

    'mode' => env('ECPAY_MODE', 'test'),
    // test | production（目前只用於識別，未來可用於功能開關）

    // ── 回呼路由 ───────────────────────────────────────────────
    'notify_url' => env('APP_URL') . '/ecpay/notify',
    // 伺服器端回呼：ECPay 伺服器 → 我們的 /ecpay/notify
    // 此路由才真正更新訂單狀態

    'return_url' => env('APP_URL') . '/ecpay/return',
    // 瀏覽器端回跳：付款後瀏覽器跳回此頁面（僅顯示結果）
    // 注意：此路由不可用於更新訂單，因瀏覽器可偽造
];
```

### 4.2 `config/renewal.php`

```php
<?php
return [
    // ── 過期太久的處理策略 ─────────────────────────────────────
    'expired_too_long_policy' => env('RENEWAL_EXPIRED_POLICY', 'admin_only'),
    /*
     * admin_only（預設）：
     *   若「舊到期日 + 方案天數」仍在過去，自助續約失敗
     *   提示會員聯繫管理員，管理員可手動延長
     *
     * from_now：
     *   若計算結果仍在過去，自動改為從「現在」起算
     *   例：到期日在 60 天前，買月繳 30 天
     *   admin_only：拒絕（60天前 + 30天 = 30天前，仍在過去）
     *   from_now：接受（現在 + 30天 = 30天後）
     */

    // ── 訂單逾期設定 ───────────────────────────────────────────
    'order_expire_hours' => env('RENEWAL_ORDER_EXPIRE_HOURS', 72),
    // pending 訂單超過 72 小時未付款，排程任務自動標記為 expired

    // ── 到期提醒天數 ───────────────────────────────────────────
    'warn_days_before_expiry' => 30,
    // 到期前 30 天：顯示黃色提醒橫幅

    'alert_days_before_expiry' => 7,
    // 到期前 7 天：顯示紅色警示橫幅
];
```

---

## 五、Service 層：業務邏輯核心

### 5.1 `EcpayService.php` — 完整逐行解析

```php
<?php
namespace App\Services;

use Ecpay\Sdk\Factories\Factory;          // ECPay SDK 工廠類別，用於建立服務實例
use Ecpay\Sdk\Response\VerifiedArrayResponse; // 用於驗證回呼簽章的回應類別
use Ecpay\Sdk\Services\UrlService;        // 用於 URL 編碼（ECPay 要求的特殊格式）
use App\Models\RenewalOrder;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EcpayService
{
    // ── 成員變數 ───────────────────────────────────────────────
    private string $merchantId;
    private ?string $hashKey;  // nullable：未設定 .env 時為 null（測試環境仍有預設值）
    private ?string $hashIv;
    private string $gatewayUrl;

    public function __construct()
    {
        // 從 config/ecpay.php 讀取設定
        // 不直接讀 env()，保持對 config 的單一依賴
        $this->merchantId = config('ecpay.merchant_id');
        $this->hashKey    = config('ecpay.hash_key');
        $this->hashIv     = config('ecpay.hash_iv');
        $this->gatewayUrl = config('ecpay.gateway_url');
    }

    // ────────────────────────────────────────────────────────────
    // 1. 產生付款表單 HTML
    // ────────────────────────────────────────────────────────────
    public function buildCheckoutForm(RenewalOrder $order): string
    {
        // 建立 ECPay SDK Factory，傳入 HashKey/HashIV
        // Factory 是 ECPay SDK 的入口，所有功能都透過它建立
        $factory = new Factory([
            'hashKey' => $this->hashKey,
            'hashIv'  => $this->hashIv,
        ]);

        // 建立自動提交表單服務
        // 'AutoSubmitFormWithCmvService' → SDK 會自動計算 CheckMacValue 並嵌入表單
        $autoSubmitFormService = $factory->create('AutoSubmitFormWithCmvService');

        $input = [
            'MerchantID'      => $this->merchantId,

            'MerchantTradeNo' => $order->order_no,
            // 唯一訂單號，最多 20 碼（我們的訂單號恰好 20 碼）
            // ECPay 用此欄位識別我們的訂單，notify 回呼時會回傳此值

            'MerchantTradeDate' => now()->format('Y/m/d H:i:s'),
            // ECPay 要求的格式：YYYY/MM/DD HH:II:SS

            'PaymentType' => 'aio',
            // All In One，ECPay 的統一付款方式代碼

            'TotalAmount' => $order->amount,
            // 金額必須是整數（新台幣無小數）

            'TradeDesc' => UrlService::ecpayUrlEncode('LINE AI 數位名片會員續約'),
            // ECPay 要求交易描述使用特定的 URL 編碼方式（% 編碼後再替換特殊字元）

            'ItemName' => '訂閱方案 ' . $order->plan->name . ' NT$' . $order->amount,
            // 商品名稱（顯示在綠界付款頁面）

            'ReturnURL' => config('ecpay.notify_url'),
            // 伺服器端回呼 URL（實際更新訂單的地方）
            // ECPay 伺服器 → 我們的 POST /ecpay/notify

            'OrderResultURL' => config('ecpay.return_url'),
            // 瀏覽器端回跳 URL（用戶付款後瀏覽器跳回的頁面）
            // 僅用於顯示結果，不做訂單更新

            'ChoosePayment' => 'Credit',
            // 指定信用卡付款，不顯示其他付款選項

            'EncryptType' => 1,
            // 1 = SHA256 雜湊（ECPay 目前標準）

            'CustomField1' => (string) $order->id,
            // 自訂欄位：夾帶內部訂單 ID
            // ECPay 會在 notify/return 回傳此值，方便我們二次查詢
        ];

        // 呼叫 SDK 產生含 CheckMacValue 的 HTML 自動提交表單
        // 表單的 action 指向 $this->gatewayUrl（綠界付款頁面）
        // 瀏覽器載入此頁面時，表單會在 JavaScript 中自動 submit
        return $autoSubmitFormService->generate($input, $this->gatewayUrl);
    }

    // ────────────────────────────────────────────────────────────
    // 2. 驗證 ECPay 回呼簽章
    // ────────────────────────────────────────────────────────────
    public function verifyCallback(array $postData): array
    {
        $factory = new Factory([
            'hashKey' => $this->hashKey,
            'hashIv'  => $this->hashIv,
        ]);

        // VerifiedArrayResponse：ECPay SDK 提供的回呼驗證類別
        // 建立實例後呼叫 ->get($postData)：
        //   - 從 $postData 中取出除 CheckMacValue 之外的所有欄位
        //   - 按字母排序後計算 SHA256 簽章
        //   - 與 $postData['CheckMacValue'] 比對
        //   - 一致 → 回傳過濾後的資料陣列
        //   - 不一致 → 拋出例外（InvalidArgumentException 或類似）
        $checkoutResponse = $factory->create(VerifiedArrayResponse::class);
        return $checkoutResponse->get($postData);
        // 失敗會拋出例外，呼叫端 catch 後回傳 "0|Exception"
    }

    // ────────────────────────────────────────────────────────────
    // 3. 處理 ECPay notify 回呼（核心邏輯）
    // ────────────────────────────────────────────────────────────
    public function processCallback(array $postData): string
    {
        $orderNo = $postData['MerchantTradeNo'] ?? null;
        $tradeNo = $postData['TradeNo'] ?? null;

        try {
            // ── 步驟 1：驗證簽章 ──────────────────────────────
            // 失敗時拋出例外，跳到 catch → 回傳 "0|Exception"
            $verified = $this->verifyCallback($postData);

            // ── 步驟 2：查找訂單 ──────────────────────────────
            $order = RenewalOrder::where('order_no', $orderNo)->first();
            if (!$order) {
                Log::warning('ECPay 回呼：找不到訂單', ['order_no' => $orderNo]);
                return '0|OrderNotFound';
            }

            // ── 步驟 3：冪等性保護（第一層）─────────────────────
            // ECPay 可能因網路問題重複發送 notify
            // 若訂單已付款，直接回傳 "1|OK"，不做任何更新
            if ($order->status === 'paid') {
                Log::info('ECPay 回呼：訂單已付款（重複回呼）', ['order_no' => $orderNo]);
                return '1|OK';
            }

            // ── 步驟 4：金額驗證（防止竄改）─────────────────────
            // 攻擊者可能偽造 TradeAmt，我們必須與資料庫中的 amount 比對
            $returnedAmt = (int) ($verified['TradeAmt'] ?? 0);
            if ($returnedAmt !== $order->amount) {
                Log::critical('ECPay 回呼：金額不一致', [
                    'order_no' => $orderNo,
                    'expected' => $order->amount,
                    'received' => $returnedAmt,
                ]);
                // 記錄 CRITICAL 等級 Log，通知管理員調查
                return '0|AmountMismatch';
            }

            // ── 步驟 5：付款狀態判斷 ─────────────────────────
            $rtnCode = (int) ($verified['RtnCode'] ?? 0);
            if ($rtnCode !== 1) {
                // RtnCode !== 1 表示付款失敗（如：卡號錯誤、餘額不足）
                Log::warning('ECPay 回呼：付款失敗', [
                    'order_no' => $orderNo,
                    'rtn_code' => $rtnCode,
                    'rtn_msg'  => $verified['RtnMsg'] ?? '',
                ]);

                // 建立失敗交易記錄（用於審計追蹤）
                PaymentTransaction::create([
                    'order_id'         => $order->id,
                    'transaction_no'   => $tradeNo,
                    'payment_method'   => 'ecpay_credit',
                    'amount'           => $returnedAmt,
                    'status'           => 'failed',
                    'gateway_response' => $this->sanitizeGatewayResponse($verified),
                    'note'             => 'RtnCode: ' . $rtnCode . ' / ' . ($verified['RtnMsg'] ?? ''),
                ]);
                return '0|PaymentFailed';
            }

            // ── 步驟 6：DB Transaction 更新訂單（核心）──────────
            DB::transaction(function () use ($order, $verified, $tradeNo) {

                // lockForUpdate()：資料庫行鎖（悲觀鎖定）
                // 防止兩個 notify 同時進入 DB Transaction 後都通過冪等檢查
                $fresh = RenewalOrder::lockForUpdate()->find($order->id);

                // 冪等性保護（第二層）：鎖定後再次檢查
                // 若另一個並發請求已先更新為 paid，跳過
                if ($fresh->status === 'paid') {
                    return;
                }

                // 更新訂單狀態為已付款
                $fresh->update([
                    'status'  => 'paid',
                    'paid_at' => now(),
                ]);

                // 建立成功交易記錄
                PaymentTransaction::create([
                    'order_id'         => $fresh->id,
                    'transaction_no'   => $tradeNo,        // ECPay 的交易流水號
                    'payment_method'   => 'ecpay_credit',
                    'amount'           => $fresh->amount,
                    'status'           => 'success',
                    'gateway_response' => $this->sanitizeGatewayResponse($verified),
                    'note'             => 'ECPay 信用卡付款成功',
                ]);

                // 延長用戶到期日
                // $fresh->user 透過 eager loading 或 lazy loading 取得 User 實例
                // $fresh->plan->duration_days 取得方案天數（如 30/90/365）
                $fresh->user->extendExpiration($fresh->plan->duration_days);

                Log::info('ECPay 付款成功', [
                    'order_no'       => $fresh->order_no,
                    'amount'         => $fresh->amount,
                    'transaction_no' => $tradeNo,
                ]);
            });
            // DB::transaction 會自動 COMMIT（成功）或 ROLLBACK（例外）

            return '1|OK';
            // ECPay 要求回傳純文字 "1|OK" 確認已收到 notify
            // 若不回傳此字串，ECPay 會重試最多 N 次

        } catch (\Exception $e) {
            Log::error('ECPay 回呼處理例外', [
                'order_no' => $orderNo,
                'message'  => $e->getMessage(),
            ]);
            return '0|Exception';
        }
    }

    // ────────────────────────────────────────────────────────────
    // 4. 敏感資料過濾（白名單）
    // ────────────────────────────────────────────────────────────
    private function sanitizeGatewayResponse(array $data): array
    {
        // 白名單方式：只保留稽核所需欄位
        // 移除的欄位包括：
        //   CheckMacValue（簽章金鑰衍生值）
        //   授權碼（銀行內部）
        //   完整卡號相關欄位
        $allowedKeys = [
            'MerchantID',          // 商店編號
            'MerchantTradeNo',     // 我們的訂單號
            'RtnCode',             // 付款結果代碼
            'RtnMsg',              // 付款結果訊息
            'TradeNo',             // ECPay 交易號
            'TradeAmt',            // 交易金額
            'PaymentDate',         // 付款時間
            'PaymentType',         // 付款方式
            'PaymentTypeChargeFee',// 手續費
            'TradeDate',           // 交易時間
            'SimulatePaid',        // 是否為模擬付款（測試環境）
        ];

        return array_filter(
            $data,
            fn($key) => in_array($key, $allowedKeys),
            ARRAY_FILTER_USE_KEY  // 使用 key 作為過濾條件（而非 value）
        );
    }
}
```

### 5.2 `RenewalService.php` — 核心業務方法

```php
<?php
namespace App\Services;

// ── 關鍵設計：Email 在 DB transaction commit 後才發送 ────────────
// 避免 DB rollback 後 Email 已寄出的問題

class RenewalService
{
    // ────────────────────────────────────────────────────────────
    // 建立續約訂單
    // ────────────────────────────────────────────────────────────
    public function createOrder(
        User $user,
        SubscriptionPlan $plan,
        string $paymentMethod,
        ?User $createdBy = null,  // null = 會員自助，有值 = 管理員代辦
        ?string $adminNote = null
    ): RenewalOrder {

        $newOrder = DB::transaction(function () use ($user, $plan, $paymentMethod, $createdBy, $adminNote) {

            // ── 防重複：TOCTOU（Time-of-Check to Time-of-Use）保護 ──
            // 在 transaction 內用 lockForUpdate 鎖定檢查
            // 防止「兩個 tab 同時提交」建立兩筆 pending 訂單
            if (RenewalOrder::where('user_id', $user->id)
                            ->where('status', 'pending')
                            ->lockForUpdate()
                            ->exists()) {
                throw new \Exception('該用戶已有待付款訂單');
            }

            $expireHours = config('renewal.order_expire_hours', 72);

            return RenewalOrder::create([
                'order_no'       => RenewalOrder::generateOrderNo(),
                'user_id'        => $user->id,
                'plan_id'        => $plan->id,
                'created_by'     => $createdBy?->id,  // PHP 8 nullsafe operator
                'amount'         => $plan->price,
                // 從方案複製金額，不接受前端傳入 amount
                // 防止前端竄改金額
                'payment_method' => $paymentMethod,
                'status'         => 'pending',
                'expires_at'     => now()->addHours($expireHours),
                'admin_note'     => $adminNote,
            ]);
        });

        // ── 匯款訂單通知管理員（在 transaction 外發送）──────────
        if ($newOrder->payment_method === 'bank_transfer') {
            $adminEmail = config('mail.admin_email', config('mail.from.address'));
            try {
                Mail::to($adminEmail)->send(new RenewalOrderCreated($newOrder));
            } catch (\Exception $e) {
                // Email 失敗不影響主流程（訂單已建立）
                Log::warning('發送管理員通知 Email 失敗', [
                    'order_no' => $newOrder->order_no,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        return $newOrder;
    }

    // ────────────────────────────────────────────────────────────
    // 確認離線付款（匯款/現金）
    // ────────────────────────────────────────────────────────────
    public function confirmOfflinePayment(RenewalOrder $order, ?string $adminNote = null): bool
    {
        // ── 終態前置檢查（快速失敗）─────────────────────────────
        if ($order->isTerminal()) {
            throw new \Exception('該訂單已在終態，無法再次操作');
        }

        $confirmedOrder = null;  // 用於在 transaction 外取得已確認的訂單資料

        DB::transaction(function () use ($order, $adminNote, &$confirmedOrder) {
            // ── 二次鎖定（防並發）────────────────────────────────
            $lockedOrder = RenewalOrder::lockForUpdate()->find($order->id);

            if ($lockedOrder->isTerminal()) {
                throw new \Exception('該訂單已在終態，無法再次操作');
            }

            // 更新訂單為已付款
            $lockedOrder->update([
                'status'     => 'paid',
                'paid_at'    => now(),
                'admin_note' => $adminNote,
            ]);

            // 建立手動付款交易記錄
            PaymentTransaction::create([
                'order_id'       => $lockedOrder->id,
                'transaction_no' => 'MANUAL-' . now()->format('YmdHis') . '-' . $lockedOrder->id,
                // 格式範例：MANUAL-20260326143022-123
                // 前綴 MANUAL 用於區分線上/離線付款
                'payment_method' => $lockedOrder->payment_method,
                'amount'         => $lockedOrder->amount,
                'status'         => 'success',
                'note'           => $adminNote,
                // gateway_response 不設定（無 ECPay 資料）
            ]);

            // 延長到期日
            $lockedOrder->load(['user', 'plan']);  // 確保關聯已載入
            $lockedOrder->user->extendExpiration($lockedOrder->plan->duration_days);

            Log::info('離線付款確認', [
                'order_no' => $lockedOrder->order_no,
                'user_id'  => $lockedOrder->user_id,
                'amount'   => $lockedOrder->amount,
            ]);

            // &$confirmedOrder 引用傳遞：使外部變數能取得 transaction 內的值
            $confirmedOrder = $lockedOrder;
        });
        // ── DB transaction COMMIT 後才發送 Email ─────────────────
        // 若 transaction rollback，$confirmedOrder 仍為 null，不發 Email

        if ($confirmedOrder) {
            try {
                Mail::to($confirmedOrder->user->email)
                    ->send(new RenewalPaymentConfirmed($confirmedOrder));
            } catch (\Exception $e) {
                Log::warning('發送付款確認 Email 失敗', [
                    'order_no' => $confirmedOrder->order_no,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        return true;
    }

    // ────────────────────────────────────────────────────────────
    // 取消訂單
    // ────────────────────────────────────────────────────────────
    public function cancelOrder(RenewalOrder $order): bool
    {
        if ($order->isTerminal()) {
            throw new \Exception('訂單已在終態，無法取消');
        }

        return DB::transaction(function () use ($order) {
            $fresh = RenewalOrder::lockForUpdate()->find($order->id);

            if ($fresh->isTerminal()) {
                // 並發保護：鎖定後再次確認
                throw new \Exception('訂單狀態已變更，無法取消');
            }

            $fresh->update(['status' => 'cancelled']);
            Log::info('訂單已取消', [
                'order_no' => $fresh->order_no,
                'order_id' => $fresh->id,
            ]);
            return true;
        });
    }

    // ────────────────────────────────────────────────────────────
    // 清理逾期未付訂單
    // ────────────────────────────────────────────────────────────
    public function expireStaleOrders(): int
    {
        // 查詢條件：status=pending 且 expires_at 已過
        // 使用 expires_at 欄位（精確）而非建立時間 + offset（模糊）
        $count = RenewalOrder::where('status', 'pending')
                              ->where('expires_at', '<', now())
                              ->update(['status' => 'expired']);
        // 批次更新，單次 SQL 比逐筆 loop 效率高很多
        return $count;
    }
}
```

---

## 六、Controller 層：請求處理

### 6.1 `EcpayCallbackController.php`

```php
<?php
namespace App\Http\Controllers;

class EcpayCallbackController extends Controller
{
    // notify()：ECPay 伺服器 → 我們的後端
    // 這裡才是真正更新訂單的地方
    public function notify(Request $request)
    {
        $result = $this->ecpayService->processCallback($request->post());
        // 必須回傳純文字，不可有 HTML、JSON 包裝、重定向
        // ECPay 要求接收到 "1|OK" 才停止重試
        return response($result, 200)->header('Content-Type', 'text/plain');
    }

    // returnResult()：付款後瀏覽器回跳
    //
    // ⚠️ 重要設計：PRG（Post-Redirect-Get）模式
    //
    // 問題根源：ECPay 以「跨域 Form POST」將瀏覽器導回 /ecpay/return。
    // 瀏覽器的 SameSite=Lax cookie 策略：跨域 POST 不帶 session cookie。
    // Laravel 收到無 cookie 的 POST → 建立全新空白 session → response 帶 Set-Cookie。
    // 瀏覽器收到新 session cookie → 覆蓋原本的登入 session → 用戶被登出。
    //
    // 解法：/ecpay/return 路由使用 'ecpay' middleware 群組（不含 StartSession）。
    // response 不帶 Set-Cookie，原登入 session cookie 完整保留。
    // 本方法只做一件事：redirect 到有 auth 的 GET 路由，讓 session 在那裡恢復。
    public function returnResult(Request $request)
    {
        $orderNo = $request->input('MerchantTradeNo');
        // 僅傳遞 order_no，不信任其他 POST 參數（如 RtnCode）
        return redirect()->route('renewal.payment-result', ['order_no' => $orderNo]);
    }
}
```

### 6.2 `RenewalController.php` — `paymentResult()` 付款結果頁

```php
// 信用卡付款結果頁（GET，有 auth middleware）
// 由 POST /ecpay/return PRG redirect 後到達
// 此時 session 已正常恢復，可使用 layouts.app 後台 layout
public function paymentResult(Request $request)
{
    $orderNo = $request->input('order_no');
    $order   = null;

    if ($orderNo) {
        $order = RenewalOrder::where('order_no', $orderNo)
            ->where('user_id', Auth::id())   // IDOR 防護：只能查自己的訂單
            ->with('plan', 'user')
            ->first();
    }

    // 以資料庫訂單狀態為準，不信任任何 URL 參數
    $success = $order && $order->status === 'paid';

    return view('renewal.payment_result', compact('success', 'order'));
}
```

**設計重點：**
- `where('user_id', Auth::id())`：防止 IDOR — 任何已登入用戶只要知道他人 `order_no` 就可能查看，加此過濾確保只能查自己的訂單
- 不從 URL 讀取 `RtnCode` 做判斷，完全以 DB 的 `order.status` 為準
- 使用 `$order->plan?->name` nullsafe operator 防止 plan 關聯為 null 時拋出例外

---

### 6.3 `RenewalOrderController.php` — IDOR 防護

```php
// 核心安全設計：getOrderForCurrentUser()
private function getOrderForCurrentUser($id): RenewalOrder
{
    $currentUser = Auth::user();
    $order = RenewalOrder::findOrFail($id);

    if ($currentUser->isSuperAdmin()) {
        return $order;
    }

    // 主帳號只能存取自己子帳號的訂單
    // 防止：主帳號 A 存取主帳號 B 的子帳號訂單
    $subUserIds = $currentUser->subUsers()->pluck('id')->toArray();
    if (!in_array($order->user_id, $subUserIds)) {
        abort(403, '您沒有權限存取此訂單');
    }

    return $order;
}

// user_id 篩選的 IDOR 防護
if ($request->filled('user_id')) {
    if (!$currentUser->isSuperAdmin() && !in_array((int)$request->user_id, $subUserIds)) {
        abort(403, '無權限篩選此用戶的訂單');
    }
    $query->where('user_id', $request->user_id);
}
```

---

## 七、路由設計

### 7.1 `routes/web.php` — 會員自助續約路由（有 session / auth）

```php
// ── 會員自助續約路由 ──────────────────────────────────────────
Route::middleware(['auth', 'check.active'])->prefix('admin')->group(function () {
    Route::get('/renewal', [RenewalController::class, 'index'])
         ->name('renewal.index');

    Route::post('/renewal/create-order', [RenewalController::class, 'createOrder'])
         ->name('renewal.create-order')
         ->middleware('throttle:5,1');   // 最多 5 次/分鐘，防大量建單

    Route::get('/renewal/ecpay-redirect/{orderId}', [RenewalController::class, 'ecpayRedirect'])
         ->name('renewal.ecpay-redirect');

    Route::get('/renewal/bank-transfer/{orderId}', [RenewalController::class, 'bankTransfer'])
         ->name('renewal.bank-transfer');

    Route::post('/renewal/upload-receipt/{orderId}', [RenewalController::class, 'uploadReceipt'])
         ->name('renewal.upload-receipt')
         ->middleware('throttle:10,1');  // 最多 10 次/分鐘，防大量上傳

    Route::get('/renewal/history', [RenewalController::class, 'history'])
         ->name('renewal.history');

    Route::get('/renewal/order/{orderId}', [RenewalController::class, 'orderDetail'])
         ->name('renewal.order-detail');

    Route::post('/renewal/cancel-order/{orderId}', [RenewalController::class, 'cancelOrder'])
         ->name('renewal.cancel-order')
         ->middleware('throttle:10,1');

    // 信用卡付款結果頁（PRG 模式的 GET 端點）
    // 由 POST /ecpay/return redirect 而來，此時 session 已恢復，用戶仍登入
    Route::get('/renewal/payment-result', [RenewalController::class, 'paymentResult'])
         ->name('renewal.payment-result');
});
```

### 7.2 `routes/ecpay.php` — ECPay 回呼路由（無 session）

```php
// 此路由檔透過 RouteServiceProvider 以 'ecpay' middleware 群組載入，
// 不走 'web' 群組，因此不含 StartSession / ShareErrorsFromSession / VerifyCsrfToken。
//
// 原因：ECPay 以跨域 Form POST 呼叫這些路由。
// 瀏覽器的 SameSite=Lax 策略導致 session cookie 不被帶入。
// 若使用 'web' 群組，Laravel 會建立新空白 session 並寫入 Set-Cookie，
// 覆蓋用戶原有的登入 session → 付款後被登出。

Route::post('/ecpay/notify', [EcpayCallbackController::class, 'notify'])
     ->name('ecpay.notify')
     ->middleware('throttle:60,1');  // 最多 60 次/分鐘

Route::post('/ecpay/return', [EcpayCallbackController::class, 'returnResult'])
     ->name('ecpay.return')
     ->middleware('throttle:30,1');  // 最多 30 次/分鐘
```

### 7.3 `app/Http/Kernel.php` — `ecpay` middleware 群組

```php
protected $middlewareGroups = [
    'web' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,       // ← 建立/還原 session
        \Illuminate\View\Middleware\ShareErrorsFromSession::class, // ← 依賴 session
        \App\Http\Middleware\VerifyCsrfToken::class,              // ← 依賴 session（寫 CSRF cookie）
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],

    // ECPay 回呼專屬：不含任何 session 相關 middleware
    // Cookie 加解密仍保留（保護其他 cookie 不被竄改）
    'ecpay' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];
```

### 7.4 `app/Providers/RouteServiceProvider.php`

```php
$this->routes(function () {
    Route::middleware('api')
        ->prefix('api')
        ->group(base_path('routes/api.php'));

    Route::middleware('web')
        ->group(base_path('routes/web.php'));

    // ECPay 回呼使用無 session 的 ecpay 群組
    Route::middleware('ecpay')
        ->group(base_path('routes/ecpay.php'));
});
```

---

## 八、前端視圖

### 8.1 `renewal/ecpay_redirect.blade.php`

```blade
@extends('layouts.app')
@section('content')
<div class="content px-3 text-center py-5">
    <p><i class="fas fa-spinner fa-spin fa-3x"></i></p>
    <p>正在跳轉至綠界金流付款頁面，請稍候...</p>

    {!! $formHtml !!}
    {{--
        $formHtml 是由 EcpayService::buildCheckoutForm() 產生的 HTML。
        內含一個 <form> 標籤，action 指向綠界付款頁面，
        並包含所有必要參數（含 CheckMacValue 簽章）。

        ECPay SDK 的 AutoSubmitFormWithCmvService 會在表單後自動附加：
        <script>document.getElementById('_form_aiochk').submit();</script>
        使頁面載入後立即提交表單。

        使用 {!! ... !!}（未跳脫）而非 {{ }}（跳脫）：
        因為 $formHtml 是可信的系統產生的 HTML，需要保留 HTML 標籤。
        這是 XSS 安全的，因為 $formHtml 完全由後端 SDK 產生，
        不含任何用戶輸入。
    --}}
</div>
@endsection
```

### 8.2 `renewal/payment_result.blade.php` — 付款結果頁（後台 layout）

```blade
@extends('layouts.app')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <h1>信用卡付款結果</h1>
        {{-- breadcrumb：首頁 > 帳號續約 > 付款結果 --}}
    </div>
</section>

<div class="content px-3">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center py-5">
                    @if($success)
                        <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                        <h3 class="text-success">付款成功</h3>
                        @if($order)
                            <p class="text-muted">訂單編號：{{ $order->order_no }}</p>
                            <p>方案：{{ $order->plan?->name ?? '' }}</p>
                            {{-- plan?->name：nullsafe operator，plan 為 null 時不拋出例外 --}}
                        @endif
                        <p class="mt-3">您的帳號已成功續約，感謝您的支持！</p>
                    @else
                        {{-- 失敗 OR notify 尚未處理完成 --}}
                        <i class="fas fa-times-circle fa-5x text-danger mb-3"></i>
                        <h3 class="text-danger">付款失敗或處理中</h3>
                        <p>付款未完成或尚在處理中，請稍後查看訂單狀態，或聯繫管理員。</p>
                    @endif
                    <div class="mt-4">
                        @if($order)
                            <a href="{{ route('renewal.order-detail', $order->id) }}" class="btn btn-outline-secondary mr-2">
                                查看訂單詳情
                            </a>
                        @endif
                        <a href="{{ route('renewal.index') }}" class="btn btn-primary">
                            返回續約頁面
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

**設計重點：**
- 繼承 `layouts.app`：付款完成後回到完整的後台介面（導覽列、側邊欄一應俱全）
- `$order->plan?->name`：使用 PHP 8 nullsafe operator，防止 plan 關聯為 null 時崩潰
- 失敗提示說明「尚在處理中」：notify 可能因網路時序比 return 慢，用戶不必驚慌

---

## 九、安全機制詳解

### 9.1 訂單狀態機（防止非法狀態轉換）

```
pending ──→ paid       僅限：ECPay notify 成功 OR 管理員確認離線付款
pending ──→ cancelled  僅限：管理員手動取消
pending ──→ expired    僅限：排程任務（逾時未付）
paid       ──→ ✗ 終態，程式碼 isTerminal() 強制阻擋任何變更
cancelled  ──→ ✗ 終態
expired    ──→ ✗ 終態
```

### 9.2 SameSite Cookie 與 ECPay 跨域 POST 問題

**問題背景：**

```
config/session.php → 'same_site' => 'lax'

SameSite=Lax 規則：
  ✅ 同源請求：帶 cookie
  ✅ 跨域 GET（頂層導覽）：帶 cookie
  ❌ 跨域 POST：不帶 cookie  ← ECPay return URL 就是跨域 POST
```

**若使用 `web` middleware 群組處理跨域 POST 的後果：**

```
[ECPay] POST /ecpay/return（無 session cookie）
  → StartSession 找不到 session → 建立新空白 session
  → response 帶 Set-Cookie: laravel_session=【空白值】
  → 瀏覽器的舊 session cookie 被覆蓋
  → redirect 後用戶已登出 ❌
```

**解法：`ecpay` middleware 群組（不含 StartSession）**

```
[ECPay] POST /ecpay/return（使用 ecpay 群組）
  → 不啟動 session → response 無 Set-Cookie
  → 瀏覽器原有 session cookie 完整保留
  → redirect 到 GET /admin/renewal/payment-result
  → 瀏覽器帶原 session cookie（SameSite=Lax 允許跨域 GET）
  → session 恢復，用戶仍登入 ✅
```

### 9.3 安全修改清單

| 檔案 | 修改內容 | 原因 |
|------|---------|------|
| `VerifyCsrfToken.php` | `$except` 加入 `'ecpay/*'` | ECPay 回呼無法提供 CSRF Token |
| `SecurityHeaders.php` | `form-action` 加入 ECPay 網域 | CSP 允許表單提交到綠界 |
| `CheckActiveUser.php` | 允許過期用戶存取 `admin/renewal*` | 過期帳號仍需能自助續約 |
| `Kernel.php` | 新增 `ecpay` middleware 群組 | ECPay 回呼不含 session，防 session 被覆蓋 |
| `RouteServiceProvider.php` | ECPay 路由改用 `ecpay` 群組 | 使 `routes/ecpay.php` 不走 `web` 群組 |
| `EcpayCallbackController.php` | `returnResult()` 改為 PRG redirect | 解決跨域 POST session 問題 |
| `RenewalController.php` | 新增 `paymentResult()` 加 user_id 過濾 | 修補 IDOR 漏洞 |

### 9.4 防攻擊矩陣

| 攻擊類型 | 防護措施 |
|---------|---------|
| 偽造 ECPay 回呼 | CheckMacValue 簽章驗證 |
| 重複回呼（重放） | 冪等保護 + `transaction_no` unique 約束 |
| 金額竄改 | 後端比對 `order.amount` vs `TradeAmt` |
| 並發付款 | `lockForUpdate()` 悲觀鎖 |
| IDOR — 付款結果頁 | `paymentResult()` 加 `where('user_id', Auth::id())` |
| IDOR — 訂單管理頁 | `getOrderForCurrentUser()` 角色層級過濾 |
| Mass Assignment | Model `$fillable` 白名單 |
| 大量建單 DoS | `throttle:5,1` 速率限制 |
| 大量上傳 DoS | `throttle:10,1` 速率限制 |
| 惡意檔案上傳 | `mimes:jpg,jpeg,png,pdf\|max:2048` 驗證 |
| Session 劫持（跨域 POST） | `ecpay` 群組不啟動 session，PRG 模式恢復 |

---

## 十、ECPay 付款時序圖

```
子帳號瀏覽器              我們的 Laravel 應用              ECPay 伺服器
      │                            │                             │
      │ GET /admin/renewal         │                             │
      ├──────────────────────────→ │                             │
      │ 選方案/付款方式             │                             │
      │                            │                             │
      │ POST /renewal/create-order │                             │
      ├──────────────────────────→ │                             │
      │                            │ RenewalService::createOrder()
      │                            │ renewal_orders (pending)    │
      │                            │                             │
      │ GET /renewal/ecpay-redirect/{id}                         │
      ├──────────────────────────→ │                             │
      │                            │ EcpayService::buildCheckoutForm()
      │ ←── 含自動 submit 表單 ────┤                             │
      │                            │                             │
      │ 表單自動 POST（含 CheckMacValue）────────────────────→   │
      │                            │                             │ 驗證簽章
      │                            │                             │ 處理扣款
      │                            │                             │
      │                            │ ←── POST /ecpay/notify ────┤
      │                            │    （ecpay 群組，無 session）│
      │                            │ 驗證 CheckMacValue 簽章     │
      │                            │ 比對金額                    │
      │                            │ renewal_orders → paid       │
      │                            │ payment_transactions 新增   │
      │                            │ users.expires_at 延長       │
      │                            │ ──→ "1|OK" ───────────────→ │
      │                            │                             │
      │ ←── POST /ecpay/return ────────────────────────────────┤
      │     （ecpay 群組，無 session，不覆蓋原 cookie）          │
      │     [302] redirect ──────→ │                             │
      │                            │ returnResult() 只做 redirect
      │                            │ 不讀 DB，不信任 POST 參數   │
      │                            │                             │
      │ GET /admin/renewal/payment-result?order_no=RN...         │
      ├──────────────────────────→ │                             │
      │     （帶原 session cookie，SameSite=Lax 允許跨域 GET）   │
      │                            │ auth middleware 通過        │
      │                            │ paymentResult() 查 DB 訂單狀態
      │                            │ where('user_id', Auth::id()) 防 IDOR
      │ ←── 後台付款結果頁 ────────┤                             │
      │     （layouts.app，仍登入）│                             │
```

**時序說明：**
- `notify` 與 `return` 幾乎同時觸發，但 `notify` 是伺服器端通訊（較快且可靠），`return` 是瀏覽器端（依賴網路速度）
- 若 `return` 比 `notify` 早到達，`payment_result` 頁面可能顯示「付款失敗或處理中」，屬正常情況，用戶可稍後至訂單詳情查看
- `notify` 失敗時 ECPay 會自動重試，最終確保訂單狀態正確更新

---

## 十一、排程命令

**檔案：** `app/Console/Commands/ExpireStaleOrders.php`

```php
protected $signature   = 'orders:expire-stale';
protected $description = '清理逾期未付款的 pending 續約訂單';

public function handle(RenewalService $renewalService): int
{
    $count = $renewalService->expireStaleOrders();
    $this->info("已清理 {$count} 筆逾期訂單");
    return Command::SUCCESS;
}
```

**Kernel 排程（每小時執行）：**

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('orders:expire-stale')->hourly();
}
```

**手動執行：**

```bash
php artisan orders:expire-stale
```

---

## 十二、ECPay SDK 版本資訊

| 項目 | 值 |
|------|-----|
| `composer.json` 要求 | `"ecpay/sdk": "^1.3"` |
| 目前安裝版本 | `1.3.2506240` |
| 發布日期 | 2025 年 6 月 24 日 |
| GitHub | https://github.com/ECPay/SDK_PHP |
| 狀態 | ✅ 最新版本，無需更新 |

**關鍵 SDK 類別對照：**

| 我們使用的類別 | 用途 |
|------|------|
| `Factory` | SDK 入口，透過它建立所有服務實例 |
| `AutoSubmitFormWithCmvService` | 產生含 CheckMacValue 的自動提交 HTML 表單 |
| `VerifiedArrayResponse` | 驗證 ECPay 回呼的 CheckMacValue 簽章 |
| `UrlService::ecpayUrlEncode()` | ECPay 要求的特殊 URL 編碼格式 |

---

---

## 十三、已知問題與修復記錄

### v1.1（2026-03-27）— ECPay return 空白頁 + 付款後登出

**症狀：**
1. 信用卡付款成功後，瀏覽器被 ECPay 導回 `/ecpay/return` 顯示空白頁面
2. 若有顯示內容，用戶到後台時已被登出

**根本原因（三個疊加問題）：**

| # | 問題 | 說明 |
|---|------|------|
| 1 | `$order->plan->name ?? ''` | PHP 的 `??` 無法攔截「對 null 存取屬性」的 `ErrorException`，plan 為 null 時 500 |
| 2 | `ecpay.result` 繼承 `layouts.app` | `layouts.app` 有 `Auth::user()->name`；跨域 POST 無 session → `null->name` 崩潰 |
| 3 | `web` middleware 群組的 `StartSession` | 跨域 POST 無 session cookie → 建立新空白 session → 覆蓋原登入 cookie → 登出 |

**修復措施：**

1. **`$order->plan?->name ?? ''`** — 改用 PHP 8 nullsafe operator
2. **PRG 模式重構** — `returnResult()` 只做 redirect，結果頁改為有 auth 的 GET 路由
3. **`ecpay` middleware 群組** — ECPay 路由移出 `web` 群組，使用無 session 的專屬群組
4. **IDOR 修補** — `paymentResult()` 加 `where('user_id', Auth::id())`

**修改的檔案：**

```
app/Http/Kernel.php                          新增 ecpay middleware 群組
app/Providers/RouteServiceProvider.php       載入 routes/ecpay.php
app/Http/Controllers/EcpayCallbackController.php  returnResult() 改為 PRG redirect
app/Http/Controllers/RenewalController.php   新增 paymentResult()，加 user_id 過濾
routes/ecpay.php                             新建，ECPay 路由專屬檔
routes/web.php                               移除舊 ECPay 路由，新增 payment-result GET 路由
resources/views/renewal/payment_result.blade.php  新建付款結果頁（後台 layout）
resources/views/ecpay/result.blade.php       改為獨立 HTML（備用，主流程已不使用）
```

---

*文件維護：請在每次修改相關功能後同步更新本文件。*
