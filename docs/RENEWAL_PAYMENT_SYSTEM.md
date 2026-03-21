# 會員續約金流系統 — 完整規劃文件

> 建立日期：2026-03-22
> 狀態：已核准，待實施

---

## 一、為什麼要做這個？

目前子帳號的到期管理完全靠管理員**手動修改** `expires_at` 欄位，沒有任何付款或帳單功能。
本計畫要讓子帳號可以**自己線上續約付款**，管理員也能**代為處理**，並留下完整的訂單紀錄。

---

## 二、需求總覽

| 項目 | 決定 |
|------|------|
| 誰需要續約？ | **子帳號**（主帳號和超級管理員不需要） |
| 付費方案 | **多方案選擇**（月繳 / 季繳 / 年繳，超級管理員可自訂） |
| 線上付款 | **綠界金流 ECPay** 信用卡（使用官方 `ecpay/sdk`） |
| 離線付款 | **匯款**（會員上傳匯款證明）、**現金**（管理員手動確認） |
| 續約入口 | **雙入口** — 會員自助 + 管理員代辦 |

---

## 三、使用者流程圖

### 流程 A：子帳號自助續約

```
子帳號登入後台
    │
    ├── 看到「到期提醒」橫幅（到期前 30 天黃色 / 7 天紅色）
    │
    └── 點選側邊欄「會員續約」
            │
            ├── 選擇方案（月繳 / 季繳 / 年繳）
            │
            └── 選擇付款方式
                    │
                    ├─ [信用卡] ─→ 跳轉綠界付款頁 ─→ 付款完成 ─→ 系統自動延長到期日 ✓
                    │
                    └─ [匯款] ─→ 顯示銀行帳號 ─→ 上傳匯款證明截圖
                                                        │
                                                        └─→ 管理員收到通知 ─→ 確認收款 ─→ 系統延長到期日 ✓
```

### 流程 B：管理員代辦續約

```
超級管理員 / 主帳號 登入後台
    │
    └── 側邊欄「續約訂單」
            │
            ├── 為子帳號建立訂單 ─→ 選方案 + 付款方式 ─→ 建立訂單
            │       │
            │       ├─ [信用卡] ─→ 產生付款連結，可提供給會員
            │       ├─ [匯款]   ─→ 等待會員上傳證明 ─→ 管理員確認
            │       └─ [現金]   ─→ 管理員直接點「確認收款」─→ 系統延長到期日 ✓
            │
            ├── 查看 / 篩選所有訂單（依狀態、日期、會員）
            │
            └── 手動延長到期日（不建立訂單，直接調整）
```

### 流程 C：帳號過期後怎麼辦？

```
子帳號到期 ─→ 登入後被導向續約頁面（不會被強制登出）
                    │
                    ├── 過期不久（續約後新到期日在未來）
                    │       └─→ 正常自助續約 ─→ 從到期日起算加天數 ─→ 帳號恢復 ✓
                    │
                    └── 過期太久（續約後新到期日仍在過去）
                            │
                            ├── 預設模式：顯示「請聯繫管理員」
                            │       └─→ 管理員手動延長 ✓
                            │
                            └── 保護模式（可切換）：自動改從「現在」起算 ✓
```

> **關鍵設計**：過期的子帳號仍然可以登入並存取「續約」頁面，但無法使用其他後台功能。
> 過期策略可透過 `.env` 中的 `RENEWAL_EXPIRED_POLICY` 切換（`admin_only` 或 `from_now`）。

---

## 四、資料庫設計（新增 3 張表）

### 表 1：`subscription_plans`（訂閱方案）

| 欄位 | 類型 | 說明 |
|------|------|------|
| `id` | bigint PK | 自動編號 |
| `name` | string(100) | 方案名稱，例如「月繳方案」 |
| `description` | text, nullable | 方案說明 |
| `price` | unsignedInteger | 價格（新台幣整數，例如 299 = NT$299） |
| `duration_days` | unsignedInteger | 有效天數（30 / 90 / 365） |
| `active` | boolean, default: true | 是否啟用 |
| `sort_order` | unsignedInteger, default: 0 | 排序 |
| `timestamps` | | created_at / updated_at |

預設資料（Seeder）：
- 月繳 NT$299 / 30 天
- 季繳 NT$799 / 90 天
- 年繳 NT$2,999 / 365 天

### 表 2：`renewal_orders`（續約訂單）

| 欄位 | 類型 | 說明 |
|------|------|------|
| `id` | bigint PK | 自動編號 |
| `order_no` | string(20), unique | 訂單編號（格式：`RN` + 年月日時分秒 + 4位亂數） |
| `user_id` | FK → users | 續約的子帳號 |
| `plan_id` | FK → subscription_plans | 選擇的方案 |
| `created_by` | FK → users, nullable | 誰建立的（NULL = 會員自助，有值 = 管理員代辦） |
| `amount` | unsignedInteger | 實際金額（建立時從方案複製，避免方案改價影響已建訂單） |
| `payment_method` | enum | `ecpay_credit` / `bank_transfer` / `cash` |
| `status` | enum, default: pending | `pending` 待付款 / `paid` 已付款 / `cancelled` 已取消 / `expired` 已逾期 |
| `paid_at` | timestamp, nullable | 付款時間 |
| `expires_at` | timestamp, nullable | 訂單逾期時間（pending 超過 72 小時自動過期） |
| `receipt_image` | string, nullable | 匯款證明圖片路徑 |
| `admin_note` | text, nullable | 管理員備註 |
| `timestamps` | | created_at / updated_at |

> 訂單編號格式範例：`RN202603221430120847`（共 20 字元，符合 ECPay 限制）

### 表 3：`payment_transactions`（付款交易紀錄）

| 欄位 | 類型 | 說明 |
|------|------|------|
| `id` | bigint PK | 自動編號 |
| `order_id` | FK → renewal_orders | 所屬訂單 |
| `transaction_no` | string(50), nullable | ECPay 交易編號 或 手動參考編號 |
| `payment_method` | string(30) | 付款方式 |
| `amount` | unsignedInteger | 交易金額 |
| `status` | enum, default: pending | `pending` / `success` / `failed` |
| `gateway_response` | json, nullable | ECPay 回傳的完整資料（用於爭議查詢） |
| `note` | text, nullable | 備註 |
| `timestamps` | | created_at / updated_at |

> **為什麼要分開「訂單」和「交易」？** 因為一筆訂單可能有多次付款嘗試（例如信用卡失敗後改匯款），分開記錄才能完整追蹤。

### 現有 `users` 表

**不需要修改**。現有的 `expires_at` 欄位已足夠。續約成功時，系統會延長這個值。

---

## 五、後端架構

### 新增的 Models（3 個）

| Model | 檔案路徑 | 主要關聯 |
|-------|----------|----------|
| `SubscriptionPlan` | `app/Models/SubscriptionPlan.php` | → 多筆 RenewalOrder |
| `RenewalOrder` | `app/Models/RenewalOrder.php` | → 屬於 User、SubscriptionPlan；→ 多筆 PaymentTransaction |
| `PaymentTransaction` | `app/Models/PaymentTransaction.php` | → 屬於 RenewalOrder |

### 修改現有 User Model

檔案：`app/Models/User.php`

```
新增關聯：renewalOrders() → hasMany RenewalOrder
新增方法：extendExpiration($days)
    ├── expires_at 為 null  → 從「現在」起加天數
    ├── expires_at 有值     → 從「到期日」起加天數（不論是否已過期）
    └── 加完後仍在過去？    → 看設定決定（見下方）
```

**到期延長的「過期太久」保護機制：**

```
情境：到期日 1/1，今天 7/1（過期 6 個月），買年繳 365 天

預設模式（admin_only）：
  1/1 + 365 = 明年 1/1 ✓（仍在未來，正常延長）

情境：到期日 1/1，今天 7/1（過期 6 個月），買月繳 30 天

預設模式（admin_only）：
  1/1 + 30 = 1/31 ✗（仍在過去！）
  → 自助續約頁面會擋住，提示「過期已久，請聯繫管理員」
  → 管理員可透過「手動延長」功能自由設定日期

可切換模式（from_now）：
  1/1 + 30 = 1/31（仍在過去）
  → 自動改為：7/1 + 30 = 7/31 ✓（從現在起算）
```

> 透過 `config/renewal.php` 中的 `expired_too_long_policy` 設定切換。

### 新增的 Service 類別（2 個）

**`app/Services/EcpayService.php`** — 封裝 ECPay 官方 SDK

```php
// 使用官方 ecpay/sdk 套件
use Ecpay\Sdk\Factories\Factory;

主要方法：
├── buildCheckoutForm($order)  → 產生付款表單 HTML（SDK 自動算 CheckMacValue）
├── verifyCallback($data)      → 驗證 ECPay 回呼簽章
└── processCallback($data)     → 處理回呼、更新訂單狀態
```

**`app/Services/RenewalService.php`** — 續約業務邏輯

```
主要方法：
├── createOrder($user, $plan, $paymentMethod, $createdBy)  → 建立訂單
├── confirmOfflinePayment($order, $adminNote)               → 確認離線付款
├── cancelOrder($order)                                      → 取消訂單
├── processEcpayPayment($order, $callbackData)              → 處理線上付款
├── extendUserExpiration($user, $days)                       → 延長到期日
└── expireStaleOrders()                                      → 清理逾期訂單
```

### 新增的 Controllers（3 個）

| Controller | 檔案路徑 | 誰能用 | 功能 |
|------------|----------|--------|------|
| `SubscriptionPlanController` | `app/Http/Controllers/Admin/` | 超級管理員 | 方案 CRUD |
| `RenewalOrderController` | `app/Http/Controllers/Admin/` | 超級管理員 + 主帳號 | 訂單管理、確認付款、手動延期 |
| `RenewalController` | `app/Http/Controllers/` | 子帳號（自助） | 續約頁面、ECPay 付款、上傳匯款證明 |

---

## 六、頁面與路由規劃

### 超級管理員看到的頁面

| 頁面 | 路由 | 說明 |
|------|------|------|
| 方案列表 | `GET /admin/subscription-plans` | DataTable 列表，可建立/編輯/刪除 |
| 建立方案 | `GET /admin/subscription-plans/create` | 表單：名稱、價格、天數、啟用、排序 |
| 編輯方案 | `GET /admin/subscription-plans/{id}/edit` | 同上 |

### 管理員（超級管理員 + 主帳號）看到的頁面

| 頁面 | 路由 | 說明 |
|------|------|------|
| 訂單列表 | `GET /admin/renewal-orders` | 可篩選狀態/日期/會員 |
| 訂單詳情 | `GET /admin/renewal-orders/{id}` | 包含交易紀錄、收據圖片 |
| 為會員建立訂單 | `GET /admin/renewal-orders/create/{userId}` | 選方案 + 付款方式 |
| 確認收款 | `PATCH /admin/renewal-orders/{id}/confirm` | 離線付款確認 |
| 取消訂單 | `PATCH /admin/renewal-orders/{id}/cancel` | 取消待付款訂單 |
| 手動延長 | `GET/POST /admin/sub-users/{userId}/manual-extend` | 不建立訂單，直接調整到期日 |

### 子帳號（會員）看到的頁面

| 頁面 | 路由 | 說明 |
|------|------|------|
| 續約主頁 | `GET /admin/renewal` | 到期資訊 + 方案卡片 + 選擇付款方式 |
| ECPay 付款跳轉 | `GET /admin/renewal/ecpay/{orderId}` | 自動跳轉到綠界付款 |
| 匯款資訊 | `GET /admin/renewal/bank-transfer/{orderId}` | 顯示銀行帳號 + 上傳收據 |
| 訂單歷史 | `GET /admin/renewal/history` | 自己的所有訂單 |
| 訂單詳情 | `GET /admin/renewal/order/{orderId}` | 單筆訂單詳情 |

### ECPay 回呼路由（公開，無需登入）

| 路由 | 說明 |
|------|------|
| `POST /ecpay/notify` | ECPay 伺服器回呼（**實際更新訂單的地方**），回傳 `1\|OK` |
| `POST /ecpay/return` | 用戶瀏覽器回跳（**僅顯示結果**，不做更新） |

---

## 七、後台側邊欄選單配置

```
📋 現有選單                          📋 新增選單
───────────                          ───────────
👑 瀏覽網站 (超級管理員)
👑 超級管理員
👑 管理員                           👑 訂閱方案        ← 新增（超級管理員）
👥 會員                             👥 續約訂單        ← 新增（超級管理員+主帳號）
👑 登入紀錄
🃏 名片模板
🃏 AI數位名片
👤 個人資料管理                      💳 會員續約        ← 新增（子帳號，到期時會高亮）
── 分隔線 ──
👑 前台設定
```

---

## 八、綠界金流（ECPay）串接細節

### 安裝

```bash
composer require ecpay/sdk
```

### 環境設定（`.env`）

```
# 綠界金流
ECPAY_MERCHANT_ID=3002607          # 測試用商店編號（正式環境請換為正式編號）
ECPAY_HASH_KEY=pwFHCqoQZGmho4w6   # 測試用 HashKey
ECPAY_HASH_IV=EkRm7iFT261dpevs    # 測試用 HashIV
ECPAY_PAYMENT_GATEWAY_URL=https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V5
ECPAY_MODE=test

# 續約設定
RENEWAL_EXPIRED_POLICY=admin_only  # 過期太久的處理方式（admin_only 或 from_now）
RENEWAL_ORDER_EXPIRE_HOURS=72      # 待付款訂單逾期時間（小時）
```

### 付款流程時序

```
子帳號         我們的系統              ECPay 伺服器
  │               │                      │
  ├─ 選方案+付款 ─→│                      │
  │               ├─ 建立訂單              │
  │               ├─ 產生付款表單           │
  │←─ 跳轉至 ECPay ─┘                     │
  ├───────── 在 ECPay 頁面輸入卡號 ────────→│
  │                                        ├─ 扣款處理
  │                                        ├─ 回呼 notify ──→│ 驗證簽章
  │                                        │                 ├─ 更新訂單 → paid
  │                                        │                 ├─ 延長到期日
  │                                        │                 └─ 回傳 "1|OK"
  │←───────── 瀏覽器回跳 return ───────────│
  │               ├─ 顯示付款結果           │
  │←── 續約成功！ ─┘                       │
```

### 必要的安全設定修改

| 檔案 | 修改內容 |
|------|----------|
| `SecurityHeaders.php` | `form-action` 加入 ECPay 網域，允許表單提交到綠界 |
| `SecurityHeaders.php` | `Permissions-Policy` 將 `payment=()` 改為 `payment=(self)`，允許瀏覽器付款 API |
| `VerifyCsrfToken.php` | `$except` 加入 `'ecpay/*'`，ECPay 回呼不驗 CSRF |
| `CheckActiveUser.php` | 允許過期用戶存取 `/admin/renewal*` 路由（不被強制登出） |

---

## 九、離線付款流程細節

### 匯款流程

```
子帳號選擇「匯款」─→ 系統建立 pending 訂單
                         │
                         ├── 頁面顯示銀行帳號資訊
                         ├── 子帳號匯款後上傳轉帳截圖
                         │       │
                         │       ├── 限 jpg/jpeg/png/pdf，最大 2MB
                         │       └── 存至 storage/app/receipts/（非公開路徑）
                         │
                         └── 系統寄 Email 通知管理員
                                   │
                                   └── 管理員到「續約訂單」頁面
                                          ├── 查看收據截圖
                                          └── 點「確認收款」→ 系統延長到期日 ✓
```

### 現金流程

```
管理員收到現金 ─→ 到後台「續約訂單」
                    │
                    ├── 為子帳號建立訂單（付款方式選「現金」）
                    └── 直接點「確認收款」─→ 系統延長到期日 ✓
```

---

## 十、需要新增的所有檔案

### Composer 套件
- `ecpay/sdk` — 綠界官方 PHP SDK

### 資料庫 Migration（3 個）
- `create_subscription_plans_table`
- `create_renewal_orders_table`
- `create_payment_transactions_table`

### Models（3 個）
- `app/Models/SubscriptionPlan.php`
- `app/Models/RenewalOrder.php`
- `app/Models/PaymentTransaction.php`

### Service 類別（2 個）
- `app/Services/EcpayService.php` — 封裝 ECPay SDK
- `app/Services/RenewalService.php` — 續約業務邏輯

### Controllers（3 個）
- `app/Http/Controllers/Admin/SubscriptionPlanController.php` — 方案管理
- `app/Http/Controllers/Admin/RenewalOrderController.php` — 訂單管理
- `app/Http/Controllers/RenewalController.php` — 會員自助續約 + ECPay 回呼

### 前端 Views（約 17 個）

```
resources/views/
├── admin/subscription_plans/    (5 個)
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── fields.blade.php
│   └── table.blade.php
├── admin/renewal_orders/        (6 個)
│   ├── index.blade.php
│   ├── show.blade.php
│   ├── create_for_user.blade.php
│   ├── fields.blade.php
│   ├── table.blade.php
│   └── manual_extend.blade.php
└── renewal/                     (6 個)
    ├── index.blade.php
    ├── bank_transfer.blade.php
    ├── ecpay_redirect.blade.php
    ├── ecpay_result.blade.php
    ├── history.blade.php
    └── order_detail.blade.php
```

### 設定檔（2 個）
- `config/ecpay.php` — 綠界金流設定
- `config/renewal.php` — 續約系統設定（過期策略、訂單逾時等）

### Email 模板（2 個）
- `resources/views/emails/renewal_order_created.blade.php` — 通知管理員有新離線訂單
- `resources/views/emails/renewal_payment_confirmed.blade.php` — 通知會員付款已確認

### 排程命令（1 個）
- `app/Console/Commands/ExpireStaleOrders.php` — 自動清理逾時未付訂單

### Seeder（1 個）
- `database/seeders/SubscriptionPlanSeeder.php` — 預設 3 個方案

---

## 十一、需要修改的現有檔案

| 檔案 | 改什麼 |
|------|--------|
| `app/Models/User.php` | 加 `renewalOrders()` 關聯 + `extendExpiration()` 方法 |
| `app/Http/Middleware/CheckActiveUser.php` | 讓過期用戶仍可存取續約頁面 |
| `app/Http/Middleware/SecurityHeaders.php` | CSP 允許綠界網域 + 新增 HSTS header |
| `app/Http/Middleware/VerifyCsrfToken.php` | ECPay 回呼路由免 CSRF |
| `routes/web.php` | 加入所有新路由 |
| `resources/views/layouts/menu.blade.php` | 加入 3 個新選單項目 |
| `resources/views/layouts/app.blade.php` | 加入到期提醒橫幅 |
| `.env.example` | 加入 ECPay + 續約系統環境變數 |
| `app/Console/Kernel.php` | 註冊逾期訂單清理排程 |

---

## 十二、安全防護與弱點掃描合規

> 本章節依據 **OWASP Top 10 (2021)** 及常見弱點掃描工具（OWASP ZAP、Burp Suite、SonarQube、Fortify）的檢查項逐一對照設計。

### 12.1 OWASP Top 10 對照表

```
┌─────┬──────────────────────────────────┬─────────────────────────────────────┐
│ 編號 │ OWASP 風險類別                    │ 本系統的防護措施                       │
├─────┼──────────────────────────────────┼─────────────────────────────────────┤
│ A01 │ 存取控制失效                      │ 12.3 授權與存取控制（IDOR 防護等）       │
│     │ Broken Access Control            │                                     │
├─────┼──────────────────────────────────┼─────────────────────────────────────┤
│ A02 │ 加密機制失效                      │ 12.4 敏感資料保護（金鑰管理等）          │
│     │ Cryptographic Failures           │                                     │
├─────┼──────────────────────────────────┼─────────────────────────────────────┤
│ A03 │ 注入攻擊                         │ 12.5 注入防護（SQL/XSS/命令注入）        │
│     │ Injection                        │                                     │
├─────┼──────────────────────────────────┼─────────────────────────────────────┤
│ A04 │ 不安全的設計                      │ 12.6 安全設計原則                       │
│     │ Insecure Design                  │                                     │
├─────┼──────────────────────────────────┼─────────────────────────────────────┤
│ A05 │ 安全設定缺陷                      │ 12.7 安全設定（Headers/CSP/CORS）       │
│     │ Security Misconfiguration        │                                     │
├─────┼──────────────────────────────────┼─────────────────────────────────────┤
│ A06 │ 易受攻擊和過時的元件               │ 12.8 套件與依賴安全                     │
│     │ Vulnerable Components            │                                     │
├─────┼──────────────────────────────────┼─────────────────────────────────────┤
│ A07 │ 身分驗證失效                      │ 12.9 身分驗證與 Session 管理            │
│     │ Auth Failures                    │                                     │
├─────┼──────────────────────────────────┼─────────────────────────────────────┤
│ A08 │ 軟體和資料完整性失效               │ 12.2 金流安全（簽章驗證/反竄改）         │
│     │ Integrity Failures               │                                     │
├─────┼──────────────────────────────────┼─────────────────────────────────────┤
│ A09 │ 安全記錄與監控失效                 │ 12.10 安全日誌與監控                    │
│     │ Logging & Monitoring             │                                     │
├─────┼──────────────────────────────────┼─────────────────────────────────────┤
│ A10 │ 伺服器端請求偽造                   │ 12.11 SSRF 防護                       │
│     │ SSRF                             │                                     │
└─────┴──────────────────────────────────┴─────────────────────────────────────┘
```

---

### 12.2 金流安全（A08 軟體和資料完整性）

| 掃描項目 | 風險 | 防護措施 |
|---------|------|----------|
| 偽造回呼 | 攻擊者模擬 ECPay 回呼篡改訂單 | SDK 的 CheckMacValue 驗證簽章。**只在 `notify`（伺服器對伺服器）中更新訂單**，`return`（瀏覽器端）僅顯示 |
| 重複回呼 | ECPay 重發導致重複扣款/延期 | **冪等處理**：處理前先用 `lockForUpdate()` 鎖定訂單，`paid` 狀態直接回 `1\|OK` |
| 金額竄改 | 中間人修改交易金額 | 回呼 `TradeAmt` 必須 === 訂單 `amount`，不一致 → 記 log + 不更新 + 通知管理員 |
| 重放攻擊 | 截取合法回呼重複使用 | 檢查 `transaction_no` 唯一性，已處理的交易編號不重複處理 |
| 訂單號碰撞 | 產生重複訂單號 | `order_no` 格式 `RN` + 時間戳 + 4 位隨機數 + DB `unique` 約束 + 碰撞重試 |
| 供應鏈攻擊 | SDK 被植入惡意程式 | 使用官方 `ecpay/sdk`（MIT License），鎖定版本號，定期 `composer audit` 檢查 |

**訂單狀態轉換規則（防止非法狀態變更）：**

```
pending ──→ paid       （僅限：ECPay notify 成功 或 管理員確認離線付款）
pending ──→ cancelled  （僅限：管理員手動取消）
pending ──→ expired    （僅限：排程任務，超過 72 小時未付款）
paid       ──→ ✗ 終態，程式碼強制阻擋任何變更
cancelled  ──→ ✗ 終態
expired    ──→ ✗ 終態
```

---

### 12.3 授權與存取控制（A01 存取控制失效）

> 弱點掃描重點：IDOR（不安全的直接物件參照）、水平/垂直越權

| 掃描項目 | 風險 | 防護措施 |
|---------|------|----------|
| IDOR — 訂單查看 | 子帳號改 URL 中的 orderId 查看他人訂單 | Controller 中**強制過濾** `where('user_id', auth()->id())`，不只檢查訂單存在 |
| IDOR — 訂單操作 | 攻擊者操作非自己的訂單（上傳收據、付款） | 每個操作前驗證 `$order->user_id === auth()->id()`，失敗回 403 |
| IDOR — 收據查看 | 直接猜測收據路徑下載他人收據 | 收據存 `storage/app/receipts/`（非 public），需透過 Controller 路由 + 權限檢查才能存取 |
| 垂直越權 — 方案管理 | 主帳號嘗試存取方案 CRUD | 路由層 `check.super.admin` Middleware 阻擋 |
| 垂直越權 — 確認付款 | 子帳號嘗試確認自己的訂單 | 路由層 `check.main` Middleware 阻擋 |
| 水平越權 — 訂單管理 | 主帳號 A 管理主帳號 B 的子帳號訂單 | Controller 中過濾 `whereHas('user', fn($q) => $q->where('parent_id', auth()->id()))` |
| 過期用戶越權 | 過期用戶透過續約路由存取其他功能 | `CheckActiveUser` 只放行 `$request->is('admin/renewal*')`，**精確比對**，不用模糊匹配 |
| Mass Assignment | 透過表單注入未預期欄位 | Model 使用 `$fillable` 白名單，Controller 使用 `$request->only([...])` 明確取值 |

---

### 12.4 敏感資料保護（A02 加密機制失效）

| 掃描項目 | 風險 | 防護措施 |
|---------|------|----------|
| 金鑰明文儲存 | ECPay HashKey/HashIV 寫死在程式碼 | **僅存 `.env`**，`.env` 已在 `.gitignore` 中排除。程式碼中只用 `config('ecpay.hash_key')` |
| 傳輸層加密 | 付款資料明文傳輸 | ECPay 正式環境 **強制 HTTPS**。所有 callback URL 必須是 `https://` |
| 回應中洩漏敏感資訊 | 錯誤訊息暴露系統內部資訊 | 生產環境 `APP_DEBUG=false`。自訂錯誤頁面不顯示 stack trace |
| 資料庫敏感欄位 | `gateway_response` 含完整回呼資料 | JSON 格式儲存，**不包含信用卡號**（ECPay 回呼本身不回傳完整卡號，僅回傳後四碼） |
| Log 中洩漏資料 | Log 記錄包含金鑰或個資 | Log 記錄只寫 `order_no`、`status`、`amount`，**禁止記錄** HashKey/HashIV/完整回呼 |
| 收據圖片 | 匯款證明可能含個人帳號資訊 | 存儲在非公開路徑，存取需經授權，不建立 symbolic link 到 public |

---

### 12.5 注入防護（A03 注入攻擊）

| 掃描項目 | 風險 | 防護措施 |
|---------|------|----------|
| SQL Injection | 惡意 SQL 注入 | **全程使用 Eloquent ORM** 參數綁定，禁止 `DB::raw()` 或字串拼接 SQL |
| XSS（反射型） | 使用者輸入反射回頁面 | Blade `{{ }}` 自動跳脫。唯一例外：`{!! $formHtml !!}` 用於 SDK 產生的表單（來源可信） |
| XSS（儲存型） | admin_note、方案 description 含惡意腳本 | 顯示時使用 `{{ }}`。若需 HTML 顯示則通過 `mews/purifier`（專案已安裝）清理 |
| 命令注入 | 使用者輸入傳入系統指令 | **不使用 `exec()`、`shell_exec()`、`system()` 等函式** |
| LDAP/XML 注入 | 特殊格式注入 | 系統不使用 LDAP 或 XML 解析，風險不適用 |
| Host Header 注入 | 偽造 Host 產生惡意連結 | `config('app.url')` 固定在 `.env`，`route()` 函式基於設定生成 URL，不依賴 `$_SERVER['HTTP_HOST']` |

**每個 Controller 的輸入驗證規則（弱點掃描重點檢查項）：**

```
SubscriptionPlanController::store/update
├── name:          required|string|max:100
├── description:   nullable|string|max:1000
├── price:         required|integer|min:1|max:999999
├── duration_days: required|integer|min:1|max:3650
├── active:        required|boolean
└── sort_order:    required|integer|min:0|max:999

RenewalController::createOrder
├── plan_id:        required|exists:subscription_plans,id (且 active=true)
└── payment_method: required|in:ecpay_credit,bank_transfer,cash

RenewalController::uploadReceipt
└── receipt_image: required|file|mimes:jpg,jpeg,png,pdf|max:2048

RenewalOrderController::storeForUser
├── user_id:        required|exists:users,id (且 role=sub_user)
├── plan_id:        required|exists:subscription_plans,id
└── payment_method: required|in:ecpay_credit,bank_transfer,cash

RenewalOrderController::confirmPayment
└── admin_note: nullable|string|max:500

RenewalOrderController::manualExtend
├── days:   required|integer|min:1|max:3650
└── reason: required|string|max:500
```

---

### 12.6 安全設計原則（A04 不安全的設計）

| 設計原則 | 實施方式 |
|---------|----------|
| 最小權限原則 | 每個角色只能存取自己需要的路由和資料。子帳號無法看到方案管理或訂單管理 |
| 防禦深度 | 路由層 Middleware + Controller 層權限檢查 + Model 層 scope 過濾，三層防護 |
| 失敗安全 | ECPay 回呼驗證失敗時，**預設不更新訂單**（fail-closed）。不會因為驗證異常而放行 |
| 業務邏輯防護 | 同一用戶同時只能有 **1 筆 pending 訂單**（防止重複建單）。建立前檢查是否有未完成訂單 |
| 價格防竄改 | 前端顯示的價格僅供參考，**後端從資料庫取方案價格**，不信任前端傳來的 amount |
| 時間防竄改 | 訂單的 `expires_at`（逾期時間）由後端計算，不接受前端傳入 |

---

### 12.7 安全設定（A05 安全設定缺陷）

> 弱點掃描工具（ZAP/Burp）最常標記的項目

| 掃描項目 | 目前狀態 | 本次修改 |
|---------|---------|---------|
| `Content-Security-Policy` | ✅ 已設定（SecurityHeaders.php） | `form-action` 加入 ECPay 網域 |
| `X-Frame-Options` | ✅ `DENY` | 不變 |
| `X-Content-Type-Options` | ✅ `nosniff` | 不變 |
| `Referrer-Policy` | ✅ `strict-origin-when-cross-origin` | 不變 |
| `Permissions-Policy` | ✅ 已設定 | 移除 `payment=()`（改為 `payment=(self)`）讓 ECPay 可用 |
| `X-XSS-Protection` | ✅ `1; mode=block` | 不變 |
| `Strict-Transport-Security` | ⚠️ 未設定 | **新增** `max-age=31536000; includeSubDomains`（正式環境） |
| CSRF Token | ✅ 所有表單 | ECPay callback 路由加入 `$except` |
| CORS | ✅ 預設 same-origin | 不變。ECPay 不需要 CORS（是 form POST 不是 AJAX） |
| Cookie 安全 | ✅ `session.secure` 可設 | 確認 `.env` 中 `SESSION_SECURE_COOKIE=true`（正式環境） |
| 錯誤頁面 | ✅ 自訂錯誤頁面 | ECPay 回呼錯誤回傳純文字 `0|ErrorMessage`，不暴露 HTML |

**新增 HSTS Header（SecurityHeaders.php）：**

```php
if (config('app.env') === 'production') {
    $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
}
```

---

### 12.8 套件與依賴安全（A06 易受攻擊的元件）

| 項目 | 處理方式 |
|------|----------|
| PHP 版本 | 專案要求 `^8.1`，目前 PHP 8.2.30（在支援週期內） |
| Laravel 版本 | `^10.10`（Laravel 10 LTS 支援至 2025-02，需評估升級至 11） |
| ECPay SDK | `ecpay/sdk` 官方維護，2025 年 6 月最新更新 |
| 已知漏洞檢查 | 部署前執行 `composer audit` 確認無已知 CVE |
| npm 套件 | 部署前執行 `npm audit` 確認前端依賴無漏洞 |
| 版本鎖定 | `composer.lock` 和 `package-lock.json` 必須提交版控，確保環境一致 |

---

### 12.9 身分驗證與 Session（A07 身分驗證失效）

| 掃描項目 | 風險 | 防護措施 |
|---------|------|----------|
| Session 固定攻擊 | 登入後未更換 Session ID | Laravel 預設在登入時呼叫 `$request->session()->regenerate()` ✅ |
| Session 劫持 | Cookie 被竊取 | `SESSION_SECURE_COOKIE=true`（HTTPS only）+ `HttpOnly` flag（Laravel 預設） |
| 閒置超時 | 用戶離開後 Session 未過期 | 現有機制：30 分鐘閒置自動登出（app.blade.php 中的 JS） ✅ |
| 暴力破解 | 大量嘗試登入 | Laravel 預設 `ThrottleRequests`：5 次/分鐘 ✅ |
| 過期用戶繞過 | 過期用戶透過直接 URL 存取 | `CheckActiveUser` 中間件在**所有 admin 路由**生效，僅 renewal 路由例外 |
| ECPay callback 偽造身分 | 攻擊者模擬 ECPay 身分 | CheckMacValue 簽章驗證，等同 ECPay 的「身分認證」 |

---

### 12.10 安全日誌與監控（A09 安全記錄失效）

> 弱點掃描常標記「缺乏安全事件記錄」

| 事件類型 | 記錄方式 | 記錄內容 |
|---------|---------|---------|
| ECPay 回呼成功 | `Log::info()` + `payment_transactions` 表 | order_no, amount, transaction_no, 時間 |
| ECPay 回呼失敗 | `Log::warning()` | order_no, 失敗原因, 完整回呼資料（脫敏後） |
| 簽章驗證失敗 | `Log::error()` | 來源 IP, order_no, 原始參數（不含金鑰） |
| 金額不一致 | `Log::critical()` + 通知管理員 | order_no, 預期金額, 實際金額 |
| 越權存取嘗試 | `Log::warning()` | 用戶 ID, 嘗試存取的 order_id, 來源 IP |
| 管理員操作 | `renewal_orders.admin_note` + `created_by` | 誰做了什麼操作、何時 |
| 檔案上傳 | `Log::info()` | 用戶 ID, 檔案大小, MIME type, order_id |
| 訂單狀態變更 | `payment_transactions` 表 | 每次狀態變更都建立一筆交易記錄 |

**Log 脫敏規則：**

```
✅ 可以記錄：order_no, amount, user_id, IP, transaction_no, status
❌ 禁止記錄：HashKey, HashIV, 信用卡號, 完整 gateway_response（僅記錄關鍵欄位）
```

---

### 12.11 SSRF 防護（A10 伺服器端請求偽造）

| 項目 | 處理方式 |
|------|----------|
| ECPay 整合方式 | 使用 **form POST 重導**（瀏覽器端），不是伺服器端 HTTP 請求。不存在 SSRF 風險 |
| 收據 URL | 收據是檔案上傳（非 URL），不會觸發伺服器端請求 |
| 總體評估 | 本功能**不涉及伺服器端發出 HTTP 請求**，SSRF 風險不適用 |

---

### 12.12 檔案上傳安全

| 掃描項目 | 風險 | 防護措施 |
|---------|------|----------|
| 惡意檔案內容 | 上傳含 PHP/shell 的偽裝圖片 | `mimes:jpg,jpeg,png,pdf` 檢查 MIME + 副檔名。存儲路徑**不在 web root** |
| 路徑穿越 | `../../etc/passwd` 類攻擊 | Laravel `store()` 自動生成 hash 檔名（`Str::random(40)`），完全忽略原始檔名 |
| 檔案大小 | 大檔案造成 DoS | `max:2048`（2MB）+ PHP `upload_max_filesize` 限制 |
| 存取控制 | 直接 URL 存取收據 | 存儲在 `storage/app/receipts/`，**無** public symbolic link。必須經 Controller 路由 |
| Content-Type 嗅探 | 瀏覽器猜測檔案類型執行 | 已設定 `X-Content-Type-Options: nosniff` ✅ |
| 圖片中嵌入腳本 | SVG 中嵌入 JS | **不接受 SVG**，僅限 jpg/jpeg/png/pdf |

---

### 12.13 速率限制與 DoS 防護

| 路由 | 限制 | 原因 |
|------|------|------|
| `POST /admin/renewal/create-order` | `throttle:5,1` | 防止大量建單 |
| `POST /admin/renewal/upload-receipt/*` | `throttle:10,1` | 防止大量上傳 |
| `POST /ecpay/notify` | `throttle:60,1` | ECPay 可能重試，但需限制 |
| `POST /ecpay/return` | `throttle:30,1` | 瀏覽器回跳 |
| `PATCH /admin/renewal-orders/*/confirm` | `throttle:10,1` | 管理員操作 |

---

### 12.14 部署安全清單（cPanel 共享主機）

| 檢查項 | 說明 | 狀態 |
|--------|------|------|
| HTTPS 強制 | ECPay 正式環境要求 callback 必須 HTTPS | `.env` 設 `APP_URL=https://...` |
| HSTS | 防止 HTTP 降級攻擊 | SecurityHeaders.php 新增（僅正式環境） |
| .env 保護 | 直接 URL 不可存取 | `.htaccess` 阻擋（Laravel 預設）✅ |
| storage 保護 | `storage/app/` 不可從外部存取 | 無 public symbolic link 到 receipts ✅ |
| debug 模式 | 正式環境禁止 debug | `.env` 設 `APP_DEBUG=false` |
| 錯誤顯示 | 不顯示 stack trace | `APP_ENV=production` + 自訂錯誤頁面 |
| Cron Job | 逾期訂單自動清理 | cPanel 設定 `php artisan schedule:run` |
| 檔案權限 | receipts 目錄 | `755`（目錄）、`644`（檔案） |
| composer audit | 檢查已知 CVE | 部署前執行 |
| npm audit | 檢查前端漏洞 | 部署前執行 |

---

## 十三、實施順序

```
┌──────────────────────────────────────────────────────────────────────┐
│ Phase 1  資料庫 + 模型 + 環境設定                                      │
│ ・安裝 ecpay/sdk・建 3 張表・建 3 個 Model・改 User Model・設定 config    │
└────────────────────┬─────────────────────────────────────────────────┘
                     ▼
┌──────────────────────────────────────────────────────────────────────┐
│ Phase 2  方案管理 CRUD（超級管理員）                                     │
│ ・SubscriptionPlanController・Views・選單・Seeder                      │
└────────────────────┬─────────────────────────────────────────────────┘
                     ▼
┌──────────────────────────────────────────────────────────────────────┐
│ Phase 3  訂單管理（管理員端）                                           │
│ ・RenewalService・RenewalOrderController・Views・確認付款・手動延期       │
└────────────────────┬─────────────────────────────────────────────────┘
                     ▼
┌──────────────────────────────────────────────────────────────────────┐
│ Phase 4  綠界金流串接                                                  │
│ ・EcpayService・安全設定修改・ECPay 回呼路由・付款跳轉頁面               │
└────────────────────┬─────────────────────────────────────────────────┘
                     ▼
┌──────────────────────────────────────────────────────────────────────┐
│ Phase 5  子帳號自助續約                                                │
│ ・Middleware 修改・RenewalController・續約頁面・匯款證明上傳・到期提醒      │
└────────────────────┬─────────────────────────────────────────────────┘
                     ▼
┌──────────────────────────────────────────────────────────────────────┐
│ Phase 6  通知 + 排程                                                  │
│ ・Email 模板・逾期訂單自動清理命令・Kernel 註冊                           │
└──────────────────────────────────────────────────────────────────────┘
```

> 每個 Phase 完成後進行 code review，確認無誤再進入下一階段。

---

## 十四、如何驗證

| # | 測試項目 | 步驟 |
|---|----------|------|
| 1 | 方案管理 | 超級管理員登入 → 建立/編輯/刪除方案 → 確認 CRUD 正常 |
| 2 | 管理員建單 | 管理員為子帳號建立訂單 → 選方案+付款方式 → 確認訂單建立成功 |
| 3 | ECPay 付款 | 使用 ECPay 測試環境 → 建立訂單 → 跳轉付款 → 付款成功 → 到期日自動延長 |
| 4 | 匯款續約 | 子帳號選匯款 → 上傳收據 → 管理員收到通知 → 確認收款 → 到期日延長 |
| 5 | 現金續約 | 管理員建立現金訂單 → 確認收款 → 到期日延長 |
| 6 | 過期用戶續約 | 把測試帳號 `expires_at` 設為昨天 → 登入 → 確認能看到續約頁面 → 付款 → 帳號恢復 |
| 7 | 逾期訂單清理 | 執行 `php artisan orders:expire-stale` → 確認 72 小時未付訂單被標記 `expired` |
