# 會員續約金流功能 — 詳細測試驗證指南

> 版本：1.0
> 日期：2026-03-26
> 適用：LINE AI 數位名片後台系統
> 功能：會員信用卡 / 匯款 / 現金續約

---

## 目錄

1. [測試環境準備](#一測試環境準備)
2. [ECPay 測試帳戶與卡號](#二ecpay-測試帳戶與卡號)
3. [關鍵前置：ngrok 隧道設定](#三關鍵前置ngrok-隧道設定)
4. [測試情境 A：子帳號信用卡自助續約](#四測試情境-a子帳號信用卡自助續約)
5. [測試情境 B：子帳號匯款自助續約](#五測試情境-b子帳號匯款自助續約)
6. [測試情境 C：管理員現金代辦續約](#六測試情境-c管理員現金代辦續約)
7. [測試情境 D：帳號到期後續約](#七測試情境-d帳號到期後續約)
8. [資料庫驗證查詢](#八資料庫驗證查詢)
9. [異常情境測試](#九異常情境測試)
10. [自動化驗收清單](#十自動化驗收清單)

---

## 一、測試環境準備

### 1.1 確認 `.env` 設定

```dotenv
# ── ECPay 測試環境 ──────────────────────────────────────────
ECPAY_MERCHANT_ID=3002607
ECPAY_HASH_KEY=pwFHCqoQZGmho4w6
ECPAY_HASH_IV=EkRm7iFT261dpevs
ECPAY_PAYMENT_GATEWAY_URL=https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V5
ECPAY_MODE=test

# ── 續約系統 ───────────────────────────────────────────────
RENEWAL_EXPIRED_POLICY=admin_only
RENEWAL_ORDER_EXPIRE_HOURS=72

# ── APP URL（需在 ngrok 設定後更新，見第三節）────────────────
APP_URL=http://localhost
```

### 1.2 確認 Migration 已執行

```bash
php artisan migrate --status | grep -E "renewal|subscription|payment"
```

預期看到三張表皆為 **Ran**：
- `subscription_plans`
- `renewal_orders`
- `payment_transactions`

如未執行：

```bash
php artisan migrate
```

### 1.3 確認 Seeder 已執行（方案資料）

```bash
php artisan tinker
>>> App\Models\SubscriptionPlan::count()
# 應回傳 3（月繳/季繳/年繳）
```

若為 0：

```bash
php artisan db:seed --class=SubscriptionPlanSeeder
```

### 1.4 準備測試帳號

| 角色 | 帳號 | 用途 |
|------|------|------|
| 超級管理員 | 預設管理員帳號 | 查看所有訂單、管理方案 |
| 主帳號 | 任一主帳號 | 代辦子帳號訂單 |
| 子帳號 | 任一子帳號 | 自助信用卡/匯款續約 |

確認子帳號有 `expires_at`（測試到期提醒需要此欄位有值）：

```bash
php artisan tinker
>>> App\Models\User::where('role', 'sub_user')->first()
```

---

## 二、ECPay 測試帳戶與卡號

> ⚠️ 這些是 ECPay **官方提供**的測試帳戶，只在測試環境（`payment-stage.ecpay.com.tw`）有效。

### 測試商店資訊

| 項目 | 值 |
|------|-----|
| MerchantID | `3002607` |
| HashKey | `pwFHCqoQZGmho4w6` |
| HashIV | `EkRm7iFT261dpevs` |
| 環境 | `payment-stage.ecpay.com.tw`（測試站） |

### 測試信用卡號

| 用途 | 卡號 | 有效月/年 | CVV |
|------|------|-----------|-----|
| **付款成功** | `4311-9522-2222-2222` | 任意未來（如 12/26）| `222` |
| **付款成功（Visa）** | `4111-1111-1111-1111` | 任意未來 | 任意 3 碼 |
| **付款失敗** | `4311-9522-2222-2222` + 到期月年設過去 | 過去日期 | 任意 |

### 3D 驗證碼（若出現）

測試環境 3D 驗證碼：`1234`

---

## 三、關鍵前置：ngrok 隧道設定

> ECPay 伺服器需要能 **從外部呼叫** `/ecpay/notify`。
> `localhost` 無法被外網訪問，因此必須使用 ngrok 建立 HTTPS 隧道。

### 3.1 安裝 ngrok

```bash
# macOS (Homebrew)
brew install ngrok

# Windows (Chocolatey)
choco install ngrok

# 或直接下載：https://ngrok.com/download
```

### 3.2 啟動 Laravel + ngrok

```bash
# 終端機 1：啟動 Laravel
php artisan serve  # 預設 port 8000

# 終端機 2：啟動 ngrok
ngrok http 8000
```

ngrok 輸出範例：

```
Forwarding  https://abcd-1234.ngrok-free.app -> http://localhost:8000
```

### 3.3 更新 `.env`

```dotenv
APP_URL=https://abcd-1234.ngrok-free.app
```

### 3.4 清除快取

```bash
php artisan config:clear
php artisan cache:clear
```

### 3.5 驗證回呼 URL 正確設定

```bash
php artisan tinker
>>> config('ecpay.notify_url')  # 應輸出 https://abcd-1234.ngrok-free.app/ecpay/notify
>>> config('ecpay.return_url')  # 應輸出 https://abcd-1234.ngrok-free.app/ecpay/return
```

> **注意**：每次重啟 ngrok，URL 會改變，需重新更新 `.env` 並清除快取。
>
> 若使用付費 ngrok 方案，可設定固定 subdomain（`ngrok http --subdomain=mysite 8000`）。

---

## 四、測試情境 A：子帳號信用卡自助續約

### 流程概覽

```
子帳號登入 → 側邊欄「會員續約」→ 選方案 → 選「信用卡」
→ 跳轉綠界 → 輸入測試卡號 → 付款成功
→ 瀏覽器回跳結果頁 → 確認訂單狀態與到期日
```

### 步驟詳解

#### Step 1：登入子帳號

以子帳號身分登入後台。

**預期行為：**
- ✅ 若距離到期日 ≤ 30 天：頂部出現黃色提醒橫幅
- ✅ 若距離到期日 ≤ 7 天：頂部出現紅色提醒橫幅
- ✅ 若已過期：頂部出現紅色「已過期」橫幅

#### Step 2：進入續約頁面

側邊欄點「會員續約」→ URL：`/admin/renewal`

**預期行為：**
- ✅ 顯示目前到期日資訊卡片
- ✅ 顯示三種方案卡片（月繳 NT$299 / 季繳 NT$799 / 年繳 NT$2,999）
- ✅ 付款方式下拉選單（信用卡 / 匯款）
- ✅ 若有 pending 訂單：顯示提醒，隱藏新建訂單表單

#### Step 3：選擇方案與付款方式

1. 選擇任一方案（如「月繳方案」NT$299）
2. 付款方式選「**信用卡（綠界金流）**」
3. 點「立即續約」

**預期行為：**
- ✅ POST `/renewal/create-order` 成功
- ✅ 資料庫 `renewal_orders` 新增一筆 `status=pending` 的記錄
- ✅ 跳轉到 `/admin/renewal/ecpay-redirect/{orderId}`

#### Step 4：確認 ECPay 跳轉頁面

**預期行為：**
- ✅ 頁面顯示「正在跳轉至綠界金流付款頁面，請稍候...」
- ✅ 頁面自動在 1 秒內透過 `<form>` 提交到綠界測試站

#### Step 5：在綠界測試環境付款

1. 頁面跳轉到 `payment-stage.ecpay.com.tw`
2. 輸入測試卡號：`4311-9522-2222-2222`
3. 有效月年：`12/26`（任意未來日期）
4. CVV：`222`
5. 點「確認付款」
6. 若出現 3D 驗證，輸入：`1234`

**預期行為：**
- ✅ 綠界處理付款，顯示「付款成功」
- ✅ 在後台 ngrok 日誌中看到 `POST /ecpay/notify` 請求
- ✅ 在後台 ngrok 日誌中看到 `POST /ecpay/return` 請求

#### Step 6：驗證結果頁面

瀏覽器回跳到 `/ecpay/return`，系統顯示結果頁面。

**預期行為：**
- ✅ 顯示綠色「✓ 付款成功」
- ✅ 顯示正確的訂單編號
- ✅ 顯示方案名稱

#### Step 7：資料庫驗證

```sql
-- 確認訂單已付款
SELECT order_no, status, amount, paid_at, payment_method
FROM renewal_orders
ORDER BY id DESC LIMIT 1;
-- 預期：status='paid', paid_at 有值

-- 確認交易記錄建立
SELECT order_id, transaction_no, status, payment_method
FROM payment_transactions
ORDER BY id DESC LIMIT 1;
-- 預期：status='success', transaction_no 有 ECPay 交易編號

-- 確認到期日已延長
SELECT id, name, expires_at
FROM users
WHERE role = 'sub_user'
ORDER BY updated_at DESC LIMIT 1;
-- 預期：expires_at 比續約前延長了 30 天（月繳）
```

---

## 五、測試情境 B：子帳號匯款自助續約

### 流程概覽

```
子帳號選匯款 → 看到銀行帳號資訊 → 上傳收據截圖
→ 管理員確認收款 → 到期日延長
```

### 步驟詳解

#### Step 1：選擇匯款付款方式

在 `/admin/renewal` 頁面：
1. 選擇方案
2. 付款方式選「**匯款**」
3. 點「立即續約」

**預期行為：**
- ✅ 跳轉到 `/admin/renewal/bank-transfer/{orderId}`
- ✅ 顯示銀行帳號資訊（`config/renewal_bank.php` 或 `.env` 中設定的帳號）
- ✅ 顯示匯款收據上傳表單

#### Step 2：上傳匯款證明

1. 準備一張 JPG/PNG/PDF 截圖（測試用，< 2MB）
2. 在上傳表單中選擇檔案
3. 點「上傳收據」

**預期行為：**
- ✅ POST `/renewal/upload-receipt/{orderId}` 成功
- ✅ 資料庫 `renewal_orders.receipt_image` 有值（存儲路徑）
- ✅ 檔案儲存在 `storage/app/receipts/`（非公開路徑）
- ✅ 頁面顯示「收據已上傳，請等待管理員確認」

#### Step 3：管理員確認收款

以管理員帳號登入：
1. 後台「**續約訂單**」→ 找到該筆 pending 訂單
2. 點「查看」→ 可看到收據圖片（透過授權路由）
3. 填入備註（可選）
4. 點「確認收款」

**預期行為：**
- ✅ PATCH `/admin/renewal-orders/{id}/confirm`
- ✅ 訂單 `status` 變為 `paid`
- ✅ `payment_transactions` 建立一筆 `transaction_no = MANUAL-xxx`
- ✅ 子帳號 `expires_at` 延長
- ✅ 子帳號收到付款確認 Email（若 Mail 設定正確）
- ✅ 頁面顯示「已確認付款，用戶到期日已更新」

---

## 六、測試情境 C：管理員現金代辦續約

### 流程概覽

```
管理員為子帳號建立現金訂單 → 直接確認收款 → 到期日延長
```

### 步驟詳解

#### Step 1：管理員建立訂單

1. 管理員後台 → 「**會員資訊**」
2. 找到目標子帳號，點「操作」→「建立續約訂單」
   - URL：`/admin/renewal-orders/create/{userId}`
3. 選擇方案 + 付款方式選「**現金**」
4. 填寫備註（如「已收現金 NT$299」）
5. 點「建立訂單」

**預期行為：**
- ✅ 訂單建立成功，`payment_method='cash'`
- ✅ 跳轉到訂單詳情頁

#### Step 2：直接確認收款

在訂單詳情頁：
1. 點「確認收款」按鈕
2. 填寫備註（可選）

**預期行為：**
- ✅ 訂單 `status` 變為 `paid`
- ✅ 子帳號到期日延長

---

## 七、測試情境 D：帳號到期後續約

### 情境說明

測試帳號過期後，能否正常自助續約。

### 步驟詳解

#### Step 1：手動設定帳號過期

```bash
php artisan tinker
>>> $user = App\Models\User::where('role', 'sub_user')->first();
>>> $user->update(['expires_at' => now()->subDays(5)]);  // 設為 5 天前過期
>>> echo $user->expires_at;
```

#### Step 2：嘗試登入

以該子帳號登入後台。

**預期行為：**
- ✅ 帳號**可以登入**（不被強制登出）
- ✅ 被導向到 `/admin/renewal`（續約頁面）
- ✅ 其他後台功能無法存取（被 `CheckActiveUser` Middleware 攔截）

#### Step 3：續約

依照「測試情境 A」步驟完成信用卡付款。

**預期行為（`RENEWAL_EXPIRED_POLICY=admin_only`，預設）：**

| 舊到期日 | 方案天數 | 新到期日計算 | 結果 |
|---------|---------|------------|------|
| 5 天前 | 30 天 | 5天前 + 30天 = 25天後 | ✅ 成功（在未來） |
| 60 天前 | 30 天 | 60天前 + 30天 = 30天前 | ❌ 失敗（仍在過去），提示聯繫管理員 |
| 60 天前 | 365 天 | 60天前 + 365天 = 305天後 | ✅ 成功（在未來） |

---

## 八、資料庫驗證查詢

### 8.1 快速狀態確認

```sql
-- 最新 5 筆訂單
SELECT id, order_no, user_id, amount, payment_method, status, paid_at, expires_at
FROM renewal_orders
ORDER BY id DESC LIMIT 5;

-- 最新 5 筆交易記錄
SELECT id, order_id, transaction_no, payment_method, amount, status, created_at
FROM payment_transactions
ORDER BY id DESC LIMIT 5;

-- 剛才更新到期日的用戶
SELECT id, name, email, expires_at, updated_at
FROM users
WHERE role = 'sub_user'
ORDER BY updated_at DESC LIMIT 3;
```

### 8.2 各狀態訂單分佈

```sql
SELECT status, COUNT(*) as count, SUM(amount) as total_amount
FROM renewal_orders
GROUP BY status;
```

### 8.3 Artisan Tinker 快速驗證

```bash
php artisan tinker

# 查看最新訂單
>>> App\Models\RenewalOrder::with('plan', 'user')->latest()->first()

# 查看最新交易（含 gateway_response）
>>> App\Models\PaymentTransaction::latest()->first()->gateway_response

# 查看特定用戶到期日
>>> App\Models\User::find(1)->expires_at

# 模擬逾期訂單清理命令
>>> Artisan::call('orders:expire-stale'); Artisan::output()
```

---

## 九、異常情境測試

### 9.1 重複 notify 回呼（冪等性）

**測試方式：** 手動用 Postman 或 curl 重送一次已成功的 ECPay notify 資料。

```bash
# 取得 notify 回呼資料（從 Laravel Log 中複製）
# 重送給 /ecpay/notify
curl -X POST https://your-ngrok-url/ecpay/notify \
  -d "MerchantID=3002607&MerchantTradeNo=RN202603261200001234&RtnCode=1&TradeAmt=299&..."
```

**預期行為：**
- ✅ 回傳 `1|OK`（冪等保護）
- ✅ 訂單 **不會**再次更新
- ✅ 到期日 **不會**再次延長
- ✅ Log 顯示「訂單已付款（重複回呼）」

### 9.2 金額竄改攻擊

**測試方式：** 修改 notify 的 `TradeAmt` 為不同金額後重送。

**預期行為：**
- ✅ 回傳 `0|AmountMismatch`
- ✅ Log 顯示 `CRITICAL` 等級的金額不一致記錄
- ✅ 訂單狀態不變

### 9.3 假造 notify（簽章錯誤）

**測試方式：** 發送一個 `CheckMacValue` 不正確的 POST 請求到 `/ecpay/notify`。

**預期行為：**
- ✅ 回傳 `0|Exception`（簽章驗證失敗）
- ✅ 訂單狀態不變

### 9.4 重複建立訂單（同時有 pending 訂單）

**測試方式：** 已有一筆 pending 訂單時，再次提交「立即續約」。

**預期行為：**
- ✅ 系統拋出錯誤「該用戶已有待付款訂單」
- ✅ 頁面顯示錯誤訊息
- ✅ 不建立新訂單

### 9.5 直接存取他人訂單（IDOR 防護）

**測試方式：** 以子帳號 A 登入，在 URL 中輸入子帳號 B 的訂單 ID。

```
# 嘗試：/admin/renewal/order/{子帳號B的訂單ID}
```

**預期行為：**
- ✅ 回傳 404 或 403（`firstOrFail` 因 `where('user_id', auth()->id())` 找不到）

### 9.6 逾期訂單自動清理

**測試方式：**

```bash
# 1. 手動建立一筆即將過期的訂單
php artisan tinker
>>> App\Models\RenewalOrder::create([
...   'order_no'       => 'RNTEST00000000001234',
...   'user_id'        => 子帳號ID,
...   'plan_id'        => 1,
...   'amount'         => 299,
...   'payment_method' => 'ecpay_credit',
...   'status'         => 'pending',
...   'expires_at'     => now()->subHour(),  // 設為 1 小時前
... ]);

# 2. 執行清理命令
php artisan orders:expire-stale
```

**預期行為：**
- ✅ 輸出清理數量（如「已清理 1 筆逾期訂單」）
- ✅ 該訂單 `status` 變為 `expired`

---

## 十、自動化驗收清單

完成所有情境測試後，逐項確認：

### 核心功能

- [ ] 子帳號可進入 `/admin/renewal` 頁面
- [ ] 方案列表正確顯示（月繳/季繳/年繳，含價格和天數）
- [ ] 選擇信用卡付款 → 成功跳轉綠界測試環境
- [ ] 綠界付款成功 → `renewal_orders.status` 變為 `paid`
- [ ] 綠界付款成功 → `payment_transactions` 建立成功記錄
- [ ] 綠界付款成功 → `users.expires_at` 正確延長天數
- [ ] 瀏覽器回跳結果頁顯示正確狀態（成功 / 失敗）

### 匯款流程

- [ ] 匯款訂單建立後顯示銀行帳號資訊頁
- [ ] 上傳收據成功（JPG / PNG / PDF）
- [ ] 收據儲存在非公開路徑（`storage/app/receipts/`）
- [ ] 管理員可在訂單詳情頁看到收據圖片
- [ ] 管理員確認後訂單狀態更新、到期日延長

### 安全性

- [ ] 重複 notify 回呼不會重複延長到期日（冪等性）
- [ ] 金額竄改攻擊被攔截
- [ ] 簽章錯誤的偽造 notify 被拒絕
- [ ] 子帳號無法存取他人訂單（IDOR 防護）
- [ ] 已有 pending 訂單時，無法建立新訂單

### 邊界情境

- [ ] 帳號過期後可登入並完成續約
- [ ] 逾期訂單清理命令正確執行
- [ ] 取消訂單功能正常
- [ ] 訂單歷史列表正確顯示

---

*文件維護：請在每次修改相關功能後同步更新本文件*
