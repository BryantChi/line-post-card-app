# 藍新金流（NewebPay）測試指南

本文件說明如何在本地測試藍新（NewebPay）信用卡續約金流，包含兩種方式：
**真實刷卡（透過 zrok 隧道）** 與 **指令模擬回呼（不用刷卡，可反覆測）**。

---

## 一、測試資訊哪裡來？

| 項目 | 來源 |
|---|---|
| ✅ 測試信用卡號 | 公開：`4000-2211-1111-1111`（任意未來有效期 + 任意 3 碼安全碼即可通過授權） |
| ✅ 串接技術文件 / API 規格 | 公開：藍新官網「技術支援 → 下載專區」的 MPG 串接技術文件 |
| ✅ 測試環境網址 | 公開：付款閘道 `https://ccore.newebpay.com`、測試後台 `https://cwww.newebpay.com` |
| ❌ 測試商店三組金鑰<br>（MerchantID / HashKey / HashIV） | **需自行申請**：到 `https://cwww.newebpay.com` 註冊測試會員、申請測試商店後取得。<br>藍新**沒有**像綠界 `3002607` 那種公開共用測試帳號，網路上找到的別人金鑰請勿使用。 |

---

## 二、共同前置：後台系統設定

進後台側邊欄「系統設定」，藍新金流區塊：

1. **藍新金流模式** → 選「測試模式」（會打 `ccore.newebpay.com` 沙箱，不實際扣款）
2. 填入測試商店的 **MerchantID / HashKey / HashIV**（留空＝不修改）
3. **啟用金流** → 勾選「藍新」
4.（可選）**預設金流** → 設為「藍新」，前台付款方式會預選藍新
5. **測試模式指定帳號** → 把要測試的帳號加進名單 ⬅️ **必做**

> 📌 **為什麼第 5 步必做**：存取控制邏輯為「只要任一**啟用中的線上金流**處於 test 模式，就只開放名單內帳號使用續約功能」。
> 測試帳號沒加進名單，打開續約頁只會看到「功能暫停」。
> （邏輯位置：`SystemSetting::canUserAccessRenewal()` / `isAnyActiveOnlineGatewayInTestMode()`）

---

## 三、方式 A：真實刷卡（透過 zrok）

> 適合驗證「跳轉藍新 → 刷卡 → 藍新回呼」的完整真實流程。

### 概念
藍新刷卡成功後，會從**外網主動 POST** 到 `你的網域/newebpay/notify` 通知付款結果。
本地 `localhost` 外網連不到，需用 zrok 開一個公開 HTTPS 網址。

### 步驟

```bash
# 終端機 1：啟動網站
php artisan serve                       # localhost:8000

# 終端機 2：用 zrok 開公開網址指向它
zrok share public --headless localhost:8000
# → 取得類似 https://xxxxxxxx.share.zrok.io 的公開網址
```

> 💡 建議用「固定網址」，免得每次重開 zrok 都要重設：
> ```bash
> zrok reserve public localhost:8000    # 取得固定 token，例如 myapp
> zrok share reserved myapp             # 之後都用這行，網址固定為 https://myapp.share.zrok.io
> ```

把 `.env` 的 `APP_URL` 改成 zrok 網址，並清快取（回呼網址是由 `APP_URL` 組出來的）：

```bash
# .env
APP_URL=https://xxxxxxxx.share.zrok.io

php artisan config:clear

# 驗證
php artisan tinker
>>> config('payment.newebpay.notify_url')   # 應為 https://xxxxxxxx.share.zrok.io/newebpay/notify
```

### 刷卡
1. 用**名單內測試帳號**登入 → 側邊欄「會員續約」
2. 選方案 → 付款方式「信用卡（藍新金流）」→ 立即續約
3. 跳轉藍新測試頁 → 輸入 `4000-2211-1111-1111`、任意未來有效期、任意 3 碼安全碼
4. 觀察 zrok 請求記錄應出現 `POST /newebpay/notify`，瀏覽器回跳結果頁

---

## 四、方式 B：指令模擬回呼（不用刷卡，可反覆測）

> 適合反覆驗證 `processNotify` 的後端邏輯（驗章、金額、冪等、終態保護、延長到期），
> **不需 zrok、不需真的刷卡**。指令會用測試金鑰產生通得過驗章的真實 payload。

### 前提
- 環境**不是** production
- 藍新為**測試模式**、且測試金鑰已於後台填妥

### 指令

```bash
# 模擬付款成功
php artisan newebpay:simulate-notify {訂單編號}

# 模擬付款失敗
php artisan newebpay:simulate-notify {訂單編號} --status=FAIL
```

訂單編號 = `renewal_orders.order_no`（格式如 `RN20260604xxxxxxxx`）。
先在續約頁建立一筆訂單（會是 `pending`），複製其 `order_no` 後即可模擬。

### 範例輸出
```
回呼前 → 訂單 RN20260604... | 狀態 pending | 金額 299
模擬送出 → Status=SUCCESS
processNotify 回應 → 1|OK
回呼後 → 訂單 RN20260604... | 狀態 paid
```

---

## 五、驗證重點（含近期修正）

| 要驗證的行為 | 怎麼測 | 預期結果 |
|---|---|---|
| **#1 存取控制**（依當前金流模式） | 用**不在名單**的帳號開 `/admin/renewal` | 看到「功能暫停」頁；名單內帳號才正常 |
| **#2 終態訂單不被改寫** | 先取消一筆訂單（變 `cancelled`），再 `newebpay:simulate-notify {該訂單} --status=SUCCESS` | 訂單**維持 cancelled**；`storage/logs/laravel.log` 出現 `終態訂單收到成功付款,需人工處理` 的 critical log |
| **#3 並發建單防護** | 同帳號已有 pending 時再次建單 | 被擋，提示「該用戶已有待付款訂單」 |
| 冪等（重複回呼） | 對同一筆已 paid 的訂單再跑一次 simulate | 回 `1|OK`，到期日**不會**再次延長 |
| 一般成功流程 | 對 pending 訂單 simulate SUCCESS | 變 `paid`、建立 success 交易、`User.expires_at` 延長 |

資料驗證：
```bash
php artisan tinker
>>> \App\Models\RenewalOrder::where('order_no','RN...')->first()->status
>>> \App\Models\PaymentTransaction::latest()->first()
>>> \App\Models\User::find(帳號ID)->expires_at
```

---

## 六、常見問題

| 現象 | 原因 / 解法 |
|---|---|
| 續約頁顯示「功能暫停」 | 測試帳號沒加進「測試模式指定帳號」名單（二-5） |
| 刷卡成功但訂單一直 pending | 藍新回呼沒進來：`APP_URL` 沒改成 zrok 網址、改了沒 `config:clear`、或 zrok 沒開 |
| zrok 網址第一次連線跳確認頁 | 先用瀏覽器開一次該網址點過確認頁，或改用 reserved 固定網址 |
| simulate 指令報「只允許 test 模式」 | 後台藍新模式不是測試模式，或在 production 環境（指令有安全防護） |
| 跳轉藍新後報錯 | 測試憑證（MerchantID/HashKey/HashIV）沒填或填錯 |

> 正式上線前另需於藍新後台設定「我方 server IP 白名單」，並確保回呼走 HTTPS（測試環境通常免白名單）。
