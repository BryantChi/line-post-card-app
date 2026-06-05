# 退款（刷退）功能實作計畫 — 階段 1（藍新 + 銀行轉帳）

> **For agentic workers:** REQUIRED SUB-SKILL: 使用 superpowers:subagent-driven-development（建議）或 superpowers:executing-plans 逐 task 實作。步驟用 checkbox（`- [ ]`）追蹤。
>
> **注意**：依使用者決定，本專案**不寫自動化測試**，每個 task 改用「手動驗證」（tinker / artisan / 後台操作 / 藍新 ccore 實測）。

**Goal:** 讓超管與主帳號能在後台對已付款的續約訂單退款（藍新線上退款、銀行轉帳純記錄），支援全額/部分退款（可多次累計）、可選擇是否扣回到期日。

**Architecture:** 沿用現有多金流 Strategy——擴充 `PaymentGatewayContract` 加 `resolveRefundAction`/`refund`，新增 `RefundService` 協調驗證、呼叫 gateway、寫退款交易、更新訂單狀態與到期日回滾，全程包 DB transaction。綠界（階段 2）此階段以旗標擋下。

**Tech Stack:** Laravel 10、Omnipay（omnipay-taiwan/omnipay-newebpay）、MySQL、AdminLTE/Bootstrap 4。

**Spec:** `docs/superpowers/specs/2026-06-05-refund-design.md`

---

## 檔案結構

| 檔案 | 責任 |
|---|---|
| `database/migrations/2026_06_05_000001_add_refund_status_to_renewal_orders.php` | renewal_orders 加 `refunded_amount`、status enum 擴充 |
| `database/migrations/2026_06_05_000002_add_refund_fields_to_payment_transactions.php` | payment_transactions 加 `type`/`parent_transaction_id`/`refund_action` |
| `app/Models/RenewalOrder.php` | 退款狀態常數、`refunded_amount` cast、`payments()`/`refunds()` 關聯、`canBeRefunded()`/`refundableAmount()` |
| `app/Models/PaymentTransaction.php` | `type` 常數、cast、`parent()`/`refunds()` 關聯、scopes |
| `app/Models/User.php` | `reduceExpiration()` |
| `config/payment.php` | `ecpay_refund_enabled` 階段旗標 |
| `app/Contracts/PaymentGatewayContract.php` | 新增 `resolveRefundAction()`/`refund()` |
| `app/Services/PaymentGateways/BankTransferGateway.php` | manual 退款（不經 API） |
| `app/Services/PaymentGateways/EcpayGateway.php` | 階段 1 退款擋下（旗標） |
| `app/Services/PaymentGateways/NewebpayGateway.php` | 查交易判動作、呼叫 Close/Cancel |
| `app/Services/RefundService.php` | 退款協調核心 |
| `app/Http/Requests/Admin/RefundRequest.php` | 退款表單驗證 |
| `app/Http/Controllers/Admin/RenewalOrderController.php` | `refundForm()`/`refund()` + 權限歸屬 |
| `routes/web.php` | 退款路由 |
| `resources/views/admin/renewal_orders/refund.blade.php` | 退款表單頁 |
| `resources/views/admin/renewal_orders/show.blade.php` | 退款按鈕 + 退款紀錄列表 |

---

## Task 1: Migration — renewal_orders 加退款欄位與狀態

**Files:**
- Create: `database/migrations/2026_06_05_000001_add_refund_status_to_renewal_orders.php`

- [ ] **Step 1: 建立 migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('renewal_orders', function (Blueprint $table) {
            $table->unsignedInteger('refunded_amount')->default(0)->after('amount');
        });

        // status 為 ENUM,需以 raw SQL 擴充新增 refunded / partially_refunded
        DB::statement("ALTER TABLE renewal_orders MODIFY COLUMN status ENUM('pending','paid','cancelled','expired','refunded','partially_refunded') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // 回滾前先把新狀態歸回 paid,避免 enum 縮小造成資料截斷
        DB::statement("UPDATE renewal_orders SET status='paid' WHERE status IN ('refunded','partially_refunded')");
        DB::statement("ALTER TABLE renewal_orders MODIFY COLUMN status ENUM('pending','paid','cancelled','expired') NOT NULL DEFAULT 'pending'");

        Schema::table('renewal_orders', function (Blueprint $table) {
            $table->dropColumn('refunded_amount');
        });
    }
};
```

- [ ] **Step 2: 執行 migration**

Run: `php artisan migrate`
Expected: `Migrating: 2026_06_05_000001_add_refund_status_to_renewal_orders` → `DONE`

- [ ] **Step 3: 手動驗證欄位與 enum**

Run: `php artisan tinker --execute='echo \Illuminate\Support\Facades\Schema::hasColumn("renewal_orders","refunded_amount") ? "ok" : "missing"; print_r(\Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM renewal_orders LIKE \"status\""));'`
Expected: 輸出 `ok`，且 status 的 Type 含 `refunded,partially_refunded`

- [ ] **Step 4: Commit**

```bash
git add database/migrations/2026_06_05_000001_add_refund_status_to_renewal_orders.php
git commit -m "$(printf '[ADD] renewal_orders 加 refunded_amount 與退款狀態\n\nCo-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>')"
```

---

## Task 2: Migration — payment_transactions 加退款欄位

**Files:**
- Create: `database/migrations/2026_06_05_000002_add_refund_fields_to_payment_transactions.php`

- [ ] **Step 1: 建立 migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->enum('type', ['payment', 'refund'])->default('payment')->after('order_id');
            $table->unsignedBigInteger('parent_transaction_id')->nullable()->after('type');
            $table->string('refund_action', 20)->nullable()->after('parent_transaction_id'); // refund / void / manual

            $table->foreign('parent_transaction_id')->references('id')->on('payment_transactions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropForeign(['parent_transaction_id']);
            $table->dropColumn(['type', 'parent_transaction_id', 'refund_action']);
        });
    }
};
```

- [ ] **Step 2: 執行 migration**

Run: `php artisan migrate`
Expected: `DONE`

- [ ] **Step 3: 手動驗證**

Run: `php artisan tinker --execute='echo \Illuminate\Support\Facades\Schema::hasColumns("payment_transactions",["type","parent_transaction_id","refund_action"]) ? "ok" : "missing";'`
Expected: `ok`

- [ ] **Step 4: Commit**

```bash
git add database/migrations/2026_06_05_000002_add_refund_fields_to_payment_transactions.php
git commit -m "$(printf '[ADD] payment_transactions 加 type/parent/refund_action 退款欄位\n\nCo-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>')"
```

---

## Task 3: RenewalOrder model — 退款狀態與查詢

**Files:**
- Modify: `app/Models/RenewalOrder.php`

- [ ] **Step 1: 加入 cast、狀態常數、關聯與退款查詢方法**

在 `$fillable` 加入 `'refunded_amount'`（若採白名單 create）；在 `$casts` 加入 `'refunded_amount' => 'integer'`。

更新終態常數並新增方法（放在 `transactions()` 關聯後）：

```php
    // 終態:不可再變更的狀態(refunded 為全額退完的終態)
    const TERMINAL_STATUSES = ['paid', 'cancelled', 'expired', 'refunded'];

    /** 原始付款交易(成功) */
    public function payments()
    {
        return $this->hasMany(PaymentTransaction::class, 'order_id')
            ->where('type', 'payment')->where('status', 'success');
    }

    /** 退款交易(成功) */
    public function refunds()
    {
        return $this->hasMany(PaymentTransaction::class, 'order_id')
            ->where('type', 'refund')->where('status', 'success');
    }

    /** 此訂單是否可退款 */
    public function canBeRefunded(): bool
    {
        return in_array($this->status, ['paid', 'partially_refunded'], true)
            && $this->refundableAmount() > 0;
    }

    /** 尚可退款的餘額 = 原金額 - 已退金額 */
    public function refundableAmount(): int
    {
        return max(0, (int) $this->amount - (int) $this->refunded_amount);
    }
```

- [ ] **Step 2: 手動驗證**

Run: `php artisan tinker --execute='$o=\App\Models\RenewalOrder::where("status","paid")->first(); if($o){echo "refundable=".$o->refundableAmount()." canRefund=".($o->canBeRefunded()?"yes":"no");} else { echo "no paid order"; }'`
Expected: 對已付款訂單輸出 `refundable=<原金額> canRefund=yes`

- [ ] **Step 3: Commit**

```bash
git add app/Models/RenewalOrder.php
git commit -m "$(printf '[ADD] RenewalOrder 退款狀態、payments/refunds 關聯與可退判斷\n\nCo-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>')"
```

---

## Task 4: PaymentTransaction model — type 與關聯

**Files:**
- Modify: `app/Models/PaymentTransaction.php`

- [ ] **Step 1: 加 fillable、常數與關聯**

在 `$fillable` 加入 `'type'`, `'parent_transaction_id'`, `'refund_action'`。新增：

```php
    const TYPE_PAYMENT = 'payment';
    const TYPE_REFUND  = 'refund';

    /** 退款交易指向的原付款交易 */
    public function parent()
    {
        return $this->belongsTo(PaymentTransaction::class, 'parent_transaction_id');
    }

    public function scopePayments($query)
    {
        return $query->where('type', self::TYPE_PAYMENT);
    }

    public function scopeRefunds($query)
    {
        return $query->where('type', self::TYPE_REFUND);
    }
```

- [ ] **Step 2: 手動驗證**

Run: `php artisan tinker --execute='echo \App\Models\PaymentTransaction::TYPE_REFUND; echo " "; echo \App\Models\PaymentTransaction::refunds()->count();'`
Expected: 輸出 `refund 0`（目前尚無退款交易）

- [ ] **Step 3: Commit**

```bash
git add app/Models/PaymentTransaction.php
git commit -m "$(printf '[ADD] PaymentTransaction type 常數、parent 關聯與 scopes\n\nCo-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>')"
```

---

## Task 5: User::reduceExpiration()

**Files:**
- Modify: `app/Models/User.php`（接在 `extendExpiration()` 後）

- [ ] **Step 1: 新增方法**

```php
    /**
     * 縮短到期日(退款扣回服務期用)。
     * 從現有到期日往前扣 $days 天;無到期日則不動。
     */
    public function reduceExpiration(int $days): bool
    {
        if ($this->expires_at === null || $days <= 0) {
            return false;
        }

        $this->update(['expires_at' => $this->expires_at->copy()->subDays($days)]);
        return true;
    }
```

- [ ] **Step 2: 手動驗證**

Run: `php artisan tinker --execute='$u=\App\Models\User::whereNotNull("expires_at")->first(); $b=$u->expires_at->format("Y-m-d"); $u->reduceExpiration(3); $u->refresh(); echo "before=$b after=".$u->expires_at->format("Y-m-d"); $u->extendExpiration(3);'`
Expected: after 比 before 早 3 天（最後再 +3 還原，避免污染資料）

- [ ] **Step 3: Commit**

```bash
git add app/Models/User.php
git commit -m "$(printf '[ADD] User::reduceExpiration 退款扣回到期日\n\nCo-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>')"
```

---

## Task 6: config 旗標 + 契約擴充

**Files:**
- Modify: `config/payment.php`
- Modify: `app/Contracts/PaymentGatewayContract.php`

- [ ] **Step 1: config 加綠界退款旗標**

在 `config/payment.php` return 陣列內加入（`ecpay` 區塊旁）：

```php
    // 綠界退款階段旗標:階段 1 關閉,正式環境驗證端點後再開
    'ecpay_refund_enabled' => env('ECPAY_REFUND_ENABLED', false),
```

- [ ] **Step 2: 契約新增兩個方法**

在 `PaymentGatewayContract` 介面加入：

```php
    /**
     * 查交易狀態,回傳建議退款動作:
     *  'refund' (信用卡已請款/關帳,走退款) | 'void' (未請款/關帳,走取消授權/作廢) | 'manual' (銀行轉帳)
     */
    public function resolveRefundAction(\App\Models\PaymentTransaction $original): string;

    /**
     * 執行退款。
     * @return array{success:bool, txn_no:?string, action:string, message:string, raw:array}
     */
    public function refund(\App\Models\PaymentTransaction $original, int $amount, string $action): array;
```

- [ ] **Step 3: 手動驗證(語法 + config)**

Run: `php -l app/Contracts/PaymentGatewayContract.php && php artisan config:clear && php artisan tinker --execute='echo config("payment.ecpay_refund_enabled") ? "on" : "off";'`
Expected: `No syntax errors` 且輸出 `off`

> 注意:此步驟後三個 gateway 會因未實作介面方法而無法解析,Task 7–9 補齊前先不要執行依賴 gateway 的指令。

- [ ] **Step 4: Commit**

```bash
git add config/payment.php app/Contracts/PaymentGatewayContract.php
git commit -m "$(printf '[ADD] 金流契約加 resolveRefundAction/refund 與綠界退款旗標\n\nCo-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>')"
```

---

## Task 7: BankTransferGateway — manual 退款

**Files:**
- Modify: `app/Services/PaymentGateways/BankTransferGateway.php`

- [ ] **Step 1: 實作介面方法**

```php
    public function resolveRefundAction(\App\Models\PaymentTransaction $original): string
    {
        return 'manual';
    }

    public function refund(\App\Models\PaymentTransaction $original, int $amount, string $action): array
    {
        // 銀行轉帳無線上退款 API,線上退款一律擋下,改由 RefundService 走純人工記錄路徑
        throw new \LogicException('Bank transfer refund must be recorded manually, not via gateway.');
    }
```

- [ ] **Step 2: 手動驗證**

Run: `php -l app/Services/PaymentGateways/BankTransferGateway.php`
Expected: `No syntax errors`

- [ ] **Step 3: Commit**

```bash
git add app/Services/PaymentGateways/BankTransferGateway.php
git commit -m "$(printf '[ADD] BankTransferGateway 退款方法(manual,擋線上退款)\n\nCo-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>')"
```

---

## Task 8: EcpayGateway — 階段 1 退款擋下

**Files:**
- Modify: `app/Services/PaymentGateways/EcpayGateway.php`

- [ ] **Step 1: 實作介面方法(以旗標擋下)**

```php
    public function resolveRefundAction(\App\Models\PaymentTransaction $original): string
    {
        // 階段 2 才查交易狀態;階段 1 預設 refund(實際會被 refund() 旗標擋下)
        return 'refund';
    }

    public function refund(\App\Models\PaymentTransaction $original, int $amount, string $action): array
    {
        if (!config('payment.ecpay_refund_enabled', false)) {
            return [
                'success' => false,
                'txn_no'  => null,
                'action'  => $action,
                'message' => '綠界退款尚未啟用(待正式環境驗證端點後開放)',
                'raw'     => [],
            ];
        }

        // 階段 2 實作:呼叫 DoAction(Action=R 退刷 / N 作廢),此處先保留
        throw new \LogicException('ECPay refund not implemented yet (phase 2).');
    }
```

- [ ] **Step 2: 手動驗證**

Run: `php -l app/Services/PaymentGateways/EcpayGateway.php`
Expected: `No syntax errors`

- [ ] **Step 3: Commit**

```bash
git add app/Services/PaymentGateways/EcpayGateway.php
git commit -m "$(printf '[ADD] EcpayGateway 退款方法(階段1旗標擋下)\n\nCo-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>')"
```

---

## Task 9: NewebpayGateway — 藍新退款 / 取消授權

**Files:**
- Modify: `app/Services/PaymentGateways/NewebpayGateway.php`

- [ ] **Step 1: 實作 resolveRefundAction 與 refund**

於 `makeGateway()` 上方加入：

```php
    public function resolveRefundAction(\App\Models\PaymentTransaction $original): string
    {
        try {
            $gateway = $this->makeGateway();
            $response = $gateway->fetchTransaction([
                'transactionId' => $original->order->order_no,
            ])->send();

            $data = $response->getData();
            // 藍新 CloseStatus: 0=未請款, 1=等待批次關帳, 2=請款失敗, 3=已關帳請款
            $closeStatus = $data['Result']['CloseStatus'] ?? ($data['CloseStatus'] ?? null);

            if ($closeStatus !== null && (string) $closeStatus === '0') {
                return 'void'; // 尚未請款 → 取消授權
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('藍新查交易狀態失敗,退款動作預設 refund', [
                'order_no' => $original->order->order_no ?? null,
                'error'    => $e->getMessage(),
            ]);
        }

        return 'refund'; // 已請款或查不到 → 預設退款(管理員可手動覆寫)
    }

    public function refund(\App\Models\PaymentTransaction $original, int $amount, string $action): array
    {
        $gateway = $this->makeGateway();
        $orderNo = $original->order->order_no;
        $tradeNo = $original->transaction_no; // 原付款的藍新 TradeNo

        $options = [
            'transactionId'        => $orderNo,
            'transactionReference' => $tradeNo,
            'amount'               => (string) $amount,
        ];

        try {
            // action=void → CreditCard/Cancel(取消授權); 否則 → CreditCard/Close CloseType=2(退款)
            $request  = $action === 'void' ? $gateway->void($options) : $gateway->refund($options);
            $response = $request->send();
            $data     = $response->getData();

            $success = method_exists($response, 'isSuccessful')
                ? $response->isSuccessful()
                : (($data['Status'] ?? '') === 'SUCCESS');

            return [
                'success' => (bool) $success,
                'txn_no'  => $data['Result']['TradeNo'] ?? ($data['TradeNo'] ?? $tradeNo),
                'action'  => $action,
                'message' => $data['Message'] ?? ($success ? 'OK' : 'FAILED'),
                'raw'     => is_array($data) ? $data : [],
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'txn_no'  => null,
                'action'  => $action,
                'message' => $e->getMessage(),
                'raw'     => [],
            ];
        }
    }
```

- [ ] **Step 2: 手動驗證(語法)**

Run: `php -l app/Services/PaymentGateways/NewebpayGateway.php`
Expected: `No syntax errors`

> 真正的藍新退款行為會在 Task 15 用 ccore 測試環境的實際訂單驗證(此處先不發 API)。

- [ ] **Step 3: Commit**

```bash
git add app/Services/PaymentGateways/NewebpayGateway.php
git commit -m "$(printf '[ADD] NewebpayGateway 退款/取消授權與交易狀態判斷\n\nCo-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>')"
```

---

## Task 10: RefundService — 退款協調核心

**Files:**
- Create: `app/Services/RefundService.php`

- [ ] **Step 1: 建立 service**

```php
<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use App\Models\RenewalOrder;
use App\Services\PaymentGateways\PaymentGatewayManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefundService
{
    public function __construct(protected PaymentGatewayManager $gateways) {}

    /**
     * 對訂單退款。
     *
     * @param  RenewalOrder  $order
     * @param  int     $amount             退款金額(>0)
     * @param  string  $reason             退款原因(必填,稽核)
     * @param  bool    $rollbackExpiration 是否扣回到期日(該方案天數)
     * @param  ?string $action             指定動作(refund/void/manual);null=由 gateway 判斷
     * @return array{success:bool, message:string, transaction:?PaymentTransaction}
     */
    public function refund(
        RenewalOrder $order,
        int $amount,
        string $reason,
        bool $rollbackExpiration,
        ?string $action = null
    ): array {
        $driver = $this->gateways->driverForPaymentMethod($order->payment_method);

        return DB::transaction(function () use ($order, $amount, $reason, $rollbackExpiration, $action, $driver) {
            /** @var RenewalOrder $fresh */
            $fresh = RenewalOrder::lockForUpdate()->find($order->id);

            // 1. 狀態與金額驗證
            if (!in_array($fresh->status, ['paid', 'partially_refunded'], true)) {
                return ['success' => false, 'message' => '此訂單狀態不可退款', 'transaction' => null];
            }
            $refundable = max(0, (int) $fresh->amount - (int) $fresh->refunded_amount);
            if ($amount <= 0 || $amount > $refundable) {
                return ['success' => false, 'message' => "退款金額不合法(可退餘額 {$refundable})", 'transaction' => null];
            }

            // 2. 找原付款交易
            $payment = PaymentTransaction::where('order_id', $fresh->id)
                ->where('type', PaymentTransaction::TYPE_PAYMENT)
                ->where('status', 'success')
                ->latest()->first();
            if (!$payment && $fresh->payment_method !== 'bank_transfer') {
                return ['success' => false, 'message' => '找不到原始付款交易', 'transaction' => null];
            }

            // 3. 決定動作 + 執行
            if ($fresh->payment_method === 'bank_transfer') {
                $result = ['success' => true, 'txn_no' => 'MANUAL-REFUND-' . now()->format('YmdHis') . '-' . $fresh->id,
                           'action' => 'manual', 'message' => '銀行轉帳人工退款記錄', 'raw' => []];
            } else {
                $finalAction = $action ?: $driver->resolveRefundAction($payment);
                $result = $driver->refund($payment, $amount, $finalAction);
            }

            // 4. 寫退款交易紀錄(成功或失敗都記,稽核留痕)
            $refundTxn = PaymentTransaction::create([
                'order_id'              => $fresh->id,
                'type'                  => PaymentTransaction::TYPE_REFUND,
                'parent_transaction_id' => $payment->id ?? null,
                'refund_action'         => $result['action'],
                'transaction_no'        => $result['txn_no'],
                'payment_method'        => $fresh->payment_method,
                'amount'                => $amount,
                'status'                => $result['success'] ? 'success' : 'failed',
                'gateway_response'      => $result['raw'],
                'note'                  => $reason,
            ]);

            // 5. 失敗 → 不動訂單與到期日
            if (!$result['success']) {
                Log::warning('退款失敗', ['order_no' => $fresh->order_no, 'message' => $result['message']]);
                return ['success' => false, 'message' => $result['message'], 'transaction' => $refundTxn];
            }

            // 6. 成功 → 累加已退、更新狀態、(選擇性)扣回到期日
            $fresh->refunded_amount = (int) $fresh->refunded_amount + $amount;
            $fresh->status = $fresh->refunded_amount >= (int) $fresh->amount ? 'refunded' : 'partially_refunded';
            $fresh->save();

            if ($rollbackExpiration) {
                $fresh->loadMissing(['user', 'plan']);
                $fresh->user->reduceExpiration((int) $fresh->plan->duration_days);
            }

            Log::info('退款成功', [
                'order_no' => $fresh->order_no, 'amount' => $amount,
                'action'   => $result['action'], 'status' => $fresh->status,
            ]);

            return ['success' => true, 'message' => '退款成功', 'transaction' => $refundTxn];
        });
    }
}
```

- [ ] **Step 2: 手動驗證(語法 + 可解析)**

Run: `php -l app/Services/RefundService.php && composer dump-autoload -q && php artisan tinker --execute='echo get_class(app(\App\Services\RefundService::class));'`
Expected: `No syntax errors` 且輸出 `App\Services\RefundService`

- [ ] **Step 3: 手動驗證(銀行轉帳退款流程,不碰金流 API)**

Run:
```bash
php artisan tinker --execute='
$o=\App\Models\RenewalOrder::where("payment_method","bank_transfer")->where("status","paid")->first();
if(!$o){echo "no bank_transfer paid order, skip"; return;}
$r=app(\App\Services\RefundService::class)->refund($o, 100, "測試部分退款", false);
$o->refresh();
echo "result=".($r["success"]?"ok":"fail")." status=".$o->status." refunded=".$o->refunded_amount;
'
```
Expected: 若有銀行轉帳已付款訂單 → `result=ok status=partially_refunded refunded=100`；驗證後可手動把該訂單 `refunded_amount=0,status=paid` 還原

- [ ] **Step 4: Commit**

```bash
git add app/Services/RefundService.php
git commit -m "$(printf '[ADD] RefundService 退款協調(驗證/防超退/狀態/到期日回滾)\n\nCo-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>')"
```

---

## Task 11: RefundRequest — 表單驗證

**Files:**
- Create: `app/Http/Requests/Admin/RefundRequest.php`

- [ ] **Step 1: 建立 FormRequest**

```php
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 權限與歸屬在 Controller 驗證
    }

    public function rules(): array
    {
        return [
            'amount'              => 'required|integer|min:1',
            'reason'              => 'required|string|max:255',
            'action'              => 'nullable|in:refund,void,manual',
            'rollback_expiration' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => '請輸入退款金額',
            'amount.min'      => '退款金額需大於 0',
            'reason.required' => '請填寫退款原因',
        ];
    }
}
```

- [ ] **Step 2: 手動驗證**

Run: `php -l app/Http/Requests/Admin/RefundRequest.php`
Expected: `No syntax errors`

- [ ] **Step 3: Commit**

```bash
git add app/Http/Requests/Admin/RefundRequest.php
git commit -m "$(printf '[ADD] RefundRequest 退款表單驗證\n\nCo-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>')"
```

---

## Task 12: Controller — refundForm / refund + 權限歸屬

**Files:**
- Modify: `app/Http/Controllers/Admin/RenewalOrderController.php`

- [ ] **Step 1: 加入 use 與兩個方法**

於檔案頂部 use 區加入(若尚未存在)：

```php
use App\Http\Requests\Admin\RefundRequest;
use App\Services\RefundService;
use App\Services\PaymentGateways\PaymentGatewayManager;
use Laracasts\Flash\Flash;
use Illuminate\Support\Facades\Auth;
```

新增方法：

```php
    /**
     * 退款表單頁。
     */
    public function refundForm($id, PaymentGatewayManager $gateways)
    {
        $order = RenewalOrder::with(['plan', 'user', 'transactions'])->findOrFail($id);
        $this->authorizeRefund($order);

        if (!$order->canBeRefunded()) {
            Flash::error('此訂單目前不可退款');
            return redirect()->route('admin.renewalOrders.show', $order->id);
        }

        // 系統建議動作(銀行轉帳=manual,信用卡查交易狀態)
        $suggestedAction = 'manual';
        if ($order->payment_method !== 'bank_transfer') {
            $payment = $order->transactions->where('type', 'payment')->where('status', 'success')->last();
            if ($payment) {
                $suggestedAction = $gateways->driverForPaymentMethod($order->payment_method)
                    ->resolveRefundAction($payment);
            }
        }

        $refundableAmount = $order->refundableAmount();
        $refunds = $order->transactions->where('type', 'refund');

        return view('admin.renewal_orders.refund', compact('order', 'suggestedAction', 'refundableAmount', 'refunds'));
    }

    /**
     * 執行退款。
     */
    public function refund($id, RefundRequest $request, RefundService $refundService)
    {
        $order = RenewalOrder::findOrFail($id);
        $this->authorizeRefund($order);

        if ($request->integer('amount') > $order->refundableAmount()) {
            Flash::error('退款金額超過可退餘額');
            return redirect()->route('admin.renewalOrders.refundForm', $order->id);
        }

        $result = $refundService->refund(
            $order,
            $request->integer('amount'),
            $request->input('reason'),
            $request->boolean('rollback_expiration'),
            $request->input('action') ?: null
        );

        $result['success']
            ? Flash::success($result['message'])
            : Flash::error('退款失敗:' . $result['message']);

        return redirect()->route('admin.renewalOrders.show', $order->id);
    }

    /**
     * 退款權限歸屬:超管不限;主帳號只能退自己旗下子帳號的訂單。
     */
    private function authorizeRefund(RenewalOrder $order): void
    {
        $actor = Auth::user();
        if ($actor->isSuperAdmin()) {
            return;
        }
        // 主帳號:訂單的用戶必須是自己旗下子帳號(parent_id = 自己)
        if ($actor->isMainUser() && $order->user && (int) $order->user->parent_id === (int) $actor->id) {
            return;
        }
        abort(403, '無權對此訂單退款');
    }
```

> 註:`User` 子帳號歸屬欄位已確認為 `parent_id`(`subUsers()` / `parentUser()` 皆用 `parent_id`);`isSuperAdmin()` / `isMainUser()` 方法皆存在。

- [ ] **Step 2: 手動驗證(語法)**

Run: `php -l app/Http/Controllers/Admin/RenewalOrderController.php`
Expected: `No syntax errors`

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/Admin/RenewalOrderController.php
git commit -m "$(printf '[ADD] 退款 Controller(refundForm/refund)與權限歸屬\n\nCo-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>')"
```

---

## Task 13: 路由

**Files:**
- Modify: `routes/web.php`（現有 renewal-orders 路由群組內,`check.main` 群組）

- [ ] **Step 1: 加入退款路由**

在 `admin.renewalOrders.show` 路由附近、同一 `check.main` 群組內加入：

```php
        Route::get('/renewal-orders/{id}/refund', [App\Http\Controllers\Admin\RenewalOrderController::class, 'refundForm'])
            ->name('admin.renewalOrders.refundForm');
        Route::post('/renewal-orders/{id}/refund', [App\Http\Controllers\Admin\RenewalOrderController::class, 'refund'])
            ->name('admin.renewalOrders.refund')
            ->middleware('throttle:10,1');
```

- [ ] **Step 2: 手動驗證**

Run: `php artisan route:clear && php artisan route:list --name=renewalOrders.refund`
Expected: 列出 `admin.renewalOrders.refundForm`(GET) 與 `admin.renewalOrders.refund`(POST)

- [ ] **Step 3: Commit**

```bash
git add routes/web.php
git commit -m "$(printf '[ADD] 退款路由(refundForm/refund)\n\nCo-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>')"
```

---

## Task 14: 後台畫面 — 退款表單 + 訂單詳情退款區

**Files:**
- Create: `resources/views/admin/renewal_orders/refund.blade.php`
- Modify: `resources/views/admin/renewal_orders/show.blade.php`

- [ ] **Step 1: 建立退款表單頁**

```blade
@extends('layouts.app')
@section('content')
<div class="content px-3 py-3">
    <h1 class="mb-3">訂單退款 — {{ $order->order_no }}</h1>

    <div class="card mb-3">
        <div class="card-body">
            <p>用戶:{{ $order->user->name ?? $order->user->email }}</p>
            <p>方案:{{ $order->plan->name }}（{{ $order->plan->duration_days }} 天）</p>
            <p>原始金額:NT$ {{ number_format($order->amount) }}</p>
            <p>已退金額:NT$ {{ number_format($order->refunded_amount) }}</p>
            <p><strong>可退餘額:NT$ {{ number_format($refundableAmount) }}</strong></p>
            <p>付款方式:{{ $order->getPaymentMethodLabel() }}</p>
        </div>
    </div>

    {!! Form::open(['route' => ['admin.renewalOrders.refund', $order->id], 'method' => 'POST']) !!}
    <div class="card">
        <div class="card-body">
            <div class="form-group">
                <label>退款金額（最多 NT$ {{ number_format($refundableAmount) }}）</label>
                <input type="number" name="amount" class="form-control" style="max-width:240px"
                       min="1" max="{{ $refundableAmount }}" value="{{ $refundableAmount }}" required>
            </div>

            @if($order->payment_method !== 'bank_transfer')
            <div class="form-group">
                <label>退款動作（系統建議:<strong>{{ $suggestedAction }}</strong>，可手動調整）</label>
                <select name="action" class="form-control" style="max-width:280px">
                    <option value="refund" {{ $suggestedAction === 'refund' ? 'selected' : '' }}>退款（已請款/關帳）</option>
                    <option value="void" {{ $suggestedAction === 'void' ? 'selected' : '' }}>取消授權/作廢（未請款/關帳）</option>
                </select>
            </div>
            @else
            <input type="hidden" name="action" value="manual">
            <div class="alert alert-info">銀行轉帳為人工退款,送出後僅記錄退款,請另行線下匯款。</div>
            @endif

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" name="rollback_expiration" value="1" class="form-check-input" id="rollback" checked>
                    <label class="form-check-label" for="rollback">扣回服務期（該方案 {{ $order->plan->duration_days }} 天）</label>
                </div>
            </div>

            <div class="form-group">
                <label>退款原因（必填）</label>
                <input type="text" name="reason" class="form-control" maxlength="255" required>
            </div>

            <button type="submit" class="btn btn-danger" onclick="return confirm('確定要退款嗎?此動作會實際向金流商發出退款。')">
                <i class="fas fa-undo"></i> 確認退款
            </button>
            <a href="{{ route('admin.renewalOrders.show', $order->id) }}" class="btn btn-secondary">取消</a>
        </div>
    </div>
    {!! Form::close() !!}
</div>
@endsection
```

- [ ] **Step 2: show.blade.php 加退款按鈕與退款紀錄**

在訂單詳情適當位置（操作按鈕區）加入：

```blade
    @if($order->canBeRefunded())
        <a href="{{ route('admin.renewalOrders.refundForm', $order->id) }}" class="btn btn-warning">
            <i class="fas fa-undo"></i> 退款
        </a>
    @endif
```

在交易紀錄區下方加入退款紀錄列表：

```blade
    @php($refundList = $order->transactions->where('type', 'refund'))
    @if($refundList->count())
    <div class="card mt-3">
        <div class="card-header"><h3 class="card-title">退款紀錄</h3></div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>時間</th><th>金額</th><th>動作</th><th>狀態</th><th>原因</th></tr></thead>
                <tbody>
                @foreach($refundList as $r)
                    <tr>
                        <td>{{ $r->created_at->format('Y-m-d H:i') }}</td>
                        <td>NT$ {{ number_format($r->amount) }}</td>
                        <td>{{ $r->refund_action }}</td>
                        <td>{{ $r->status === 'success' ? '成功' : '失敗' }}</td>
                        <td>{{ $r->note }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
```

- [ ] **Step 3: 手動驗證(畫面)**

Run: `php artisan view:clear`
然後以超管登入後台,開啟一筆已付款訂單詳情頁,確認:出現「退款」按鈕 → 點入退款表單顯示可退餘額、動作下拉、扣回勾選、原因欄位。

- [ ] **Step 4: Commit**

```bash
git add resources/views/admin/renewal_orders/refund.blade.php resources/views/admin/renewal_orders/show.blade.php
git commit -m "$(printf '[ADD] 退款後台畫面(退款表單+訂單詳情退款區)\n\nCo-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>')"
```

---

## Task 15: 整合手動驗證（藍新 ccore 實測 + 權限）

> 本 task 無程式碼,為端到端手動驗收。需藍新 ccore 測試環境憑證、已設 zrok/APP_URL(見 `docs/NEWEBPAY_TESTING_GUIDE.md`)。

- [ ] **Step 1: 準備一筆藍新已付款訂單**

用測試帳號完成一次藍新續約付款(或用 `php artisan newebpay:simulate-notify` 將既有 pending 訂單轉 paid),記下 `order_no`。

- [ ] **Step 2: 後台部分退款(藍新)**

以超管登入 → 訂單詳情 → 退款 → 輸入部分金額(如原額一半)、原因、保留「扣回服務期」勾選 → 確認退款。

驗證(tinker)：
```bash
php artisan tinker --execute='$o=\App\Models\RenewalOrder::where("order_no","<ORDER_NO>")->with("transactions","user","plan")->first(); echo "status=".$o->status." refunded=".$o->refunded_amount." expires=".$o->user->expires_at; print_r($o->transactions->where("type","refund")->map->only(["amount","status","refund_action","note"])->values()->all());'
```
Expected: `status=partially_refunded`、`refunded_amount=<退款額>`、出現一筆 `type=refund status=success`、到期日已往前扣該方案天數。

> 注意:藍新 ccore 實際退款是否成功取決於該測試交易是否已請款。若回「未請款不可退」,改用退款表單的「取消授權/作廢」動作重試。

- [ ] **Step 3: 退到滿額 → refunded 終態**

對同一訂單再退剩餘金額。驗證 `status=refunded`、退款按鈕消失(canBeRefunded=false)。

- [ ] **Step 4: 防超退驗證**

嘗試對已全退訂單再退款 → 應被擋(畫面無退款入口;若直接 POST,RefundService 回「退款金額不合法」)。

- [ ] **Step 5: 權限驗證**

以「非該訂單旗下」的主帳號嘗試開啟該訂單退款頁(直接打 URL `/admin/renewal-orders/<id>/refund`)→ 應 403。

- [ ] **Step 6: 銀行轉帳人工退款**

對一筆銀行轉帳已付款訂單退款 → 應成功記錄一筆 `refund_action=manual` 的退款交易,狀態轉 partially_refunded/refunded。

- [ ] **Step 7: 推送**

全部驗證通過後：
```bash
git push
```

---

## 階段 2（另開計畫）

綠界退款待正式環境完成下列驗證後另寫計畫實作 `EcpayGateway::refund`：
1. 確認套件端點 `payment.ecpay.com.tw/CreditDetail/DoAction` 是否仍有效,否則改打 `ecpayment.ecpay.com.tw/1.0.0/Credit/DoAction`
2. 正式環境小額實測退刷(Action=R)與作廢(Action=N)、關帳時序、帳戶餘額
3. 開啟 `ECPAY_REFUND_ENABLED=true`
