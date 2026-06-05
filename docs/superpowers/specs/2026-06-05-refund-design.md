# 信用卡 / 訂單退款功能設計

- 日期：2026-06-05
- 分支：`feature/newebpay-multi-gateway`
- 範圍：續約訂單的退款（刷退）功能，涵蓋藍新、綠界、銀行轉帳

---

## 1. 目標與情境

為續約訂單提供退款能力，涵蓋三種使用情境：

1. **誤刷／重複扣款更正**
2. **用戶取消訂閱退費**
3. **客訴／管理員裁量補償**

因此需支援 **全額與部分退款**，且部分退款可 **多次累計**（累計不超過原額）。

## 2. 設計決策摘要

| 主題 | 決策 |
|---|---|
| 退款金額 | 支援全額與部分退款，部分退款可多次累計，累計不超原額 |
| 到期日回滾 | 退款 UI 提供「扣回服務期」勾選框，預設勾選、可取消；扣回固定為該訂單 `plan.duration_days`（不按比例） |
| 關帳時序 | 系統查交易狀態自動判斷動作（已請款/關帳→退款；未請款/關帳→作廢/取消授權），UI 顯示建議動作、管理員可手動覆寫 |
| 銀行轉帳 | 納入功能，但純人工記錄（不串 API），管理員線下匯款後於系統標記 |
| 權限 | 超管 + 主帳號；主帳號只能退自己旗下子帳號的訂單 |
| 架構 | Approach A：擴充 `PaymentGatewayContract` + 新增 `RefundService`，與現有多金流 Strategy 架構一致 |
| 交付 | Approach C：分階段。階段 1 藍新 + 銀行轉帳先上；階段 2 綠界正式環境驗證後再開 |

## 3. 資料模型與狀態機

### 3.1 `payment_transactions`（複用，新增 3 欄）

- `type` ENUM('payment','refund')，預設 `payment`（現有資料即付款）
- `parent_transaction_id` BIGINT UNSIGNED NULL（退款指向原付款交易）
- `refund_action` VARCHAR NULL（實際動作：`refund` / `void` / `manual`）

一筆退款 = 一列 `type='refund'`：`amount`=退款金額（正數）、`status`=`success`/`failed`、`gateway_response`=金流回應、`note`=退款原因。

### 3.2 `renewal_orders`（新增 1 欄 + 擴充狀態）

- `refunded_amount` INT 預設 0（累計已退金額，用於控管「不超原額」與多次累計）
- `status` 新增 `refunded`（全額退）、`partially_refunded`（部分退）
  - 實作時先確認 `status` 欄位型別；若為 ENUM 需 migration 擴充，若為 VARCHAR 則僅需更新驗證與常數

### 3.3 狀態機

```
paid ──部分退──> partially_refunded ──退到滿額──> refunded(終態)
paid ──全額退──────────────────────────────────> refunded(終態)
partially_refunded ──再退──> partially_refunded / refunded
```

- 僅 `paid` / `partially_refunded` 可退款；`cancelled` / `expired` 不可（未付款）
- `refunded` 列入終態（`TERMINAL_STATUSES`），不可再退

### 3.4 到期日回滾

- 退款 UI 「扣回服務期（該方案 N 天）」勾選框，預設勾選、可取消
- 勾選 → `User::reduceExpiration($plan->duration_days)`（新增方法，到期日減固定天數）

## 4. 契約擴充與 Gateway 行為

### 4.1 `PaymentGatewayContract` 新增方法

```php
// 查交易狀態,回傳建議退款動作:'refund' | 'void'(信用卡) 或 'manual'(銀行轉帳)
public function resolveRefundAction(PaymentTransaction $original): string;

// 執行退款,回傳 ['success'=>bool,'txn_no'=>?string,'action'=>string,'message'=>string,'raw'=>array]
public function refund(PaymentTransaction $original, int $amount, string $action): array;
```

`resolveRefundAction` 供「系統自動判斷」：進退款頁時先查狀態決定預設動作，UI 顯示後管理員可覆寫，送出時把最終 `action` 傳給 `refund()`。

### 4.2 各 Gateway 實作

| Gateway | resolveRefundAction | refund 實際呼叫 |
|---|---|---|
| 藍新 `NewebpayGateway` | 查交易：已請款→`refund`、未請款→`void` | `refund`→`API/CreditCard/Close`(CloseType=2)；`void`→取消授權 |
| 綠界 `EcpayGateway` | 查交易：已關帳→`refund`、未關帳→`void` | `DoAction` Action=`R`(退刷) / `N`(作廢) |
| 銀行轉帳 `BankTransferGateway` | 一律回 `manual` | 不經 gateway；線上 refund 拋例外，由 `RefundService` 走純記錄路徑 |

### 4.3 綠界端點處理（呼應分階段）

- 階段 1 不啟用綠界退款；`RefundService` 在「綠界尚未啟用退款」時擋下並提示。
- 階段 2 正式環境小額實測，確認套件舊端點 `payment.ecpay.com.tw/CreditDetail/DoAction` 是否仍有效；若失效則覆寫為官方新端點 `https://ecpayment.ecpay.com.tw/1.0.0/Credit/DoAction`。

## 5. RefundService 協調流程與錯誤處理

### 5.1 輸入

原訂單、退款金額、`action`（可選，預設 `resolveRefundAction`）、是否扣回到期日、退款原因（必填）、操作者。

### 5.2 流程（全程包 DB transaction）

1. `lockForUpdate` 鎖定訂單（序列化同訂單併發退款）
2. 驗證：
   - 訂單狀態 ∈ {`paid`, `partially_refunded`}
   - 找到原付款交易（`type=payment`, `status=success`）
   - `amount > 0` 且 `refunded_amount + amount ≤ order.amount`（防超退）
3. 決定 `action`（未指定→`resolveRefundAction`）
4. 呼叫 `gateway->refund()`（銀行轉帳→跳過 API，走 manual 記錄）
5. 依結果：
   - **成功**：建 `type=refund` 交易；`refunded_amount += amount`；更新狀態（滿額→`refunded`、否則→`partially_refunded`）；勾選扣回→`user->reduceExpiration($plan->duration_days)`
   - **失敗**：建 `type=refund, status=failed`（稽核留痕），**不動訂單與到期日**，回錯誤訊息
6. commit

### 5.3 錯誤處理

| 情況 | 處理 |
|---|---|
| 金流回「未關帳/未請款不能退」 | 提示「綠界請於授權隔日 6:00 後再退」或改作廢 |
| 綠界帳戶餘額不足 | 明確提示需先儲值 |
| 端點／網路錯誤 | 記 log、交易標 `failed`、提示重試（**不改訂單**，可安全重試） |
| 重複送出 | `lockForUpdate` + `refunded_amount` 累計檢查擋超退 |
| 銀行轉帳 manual | 不呼叫 API，需管理員確認已線下匯款後才記錄 |

**冪等關鍵**：退款失敗時訂單完全不動；只有「成功才寫 `refunded_amount`」，避免重複退。

## 6. 後台介面、Controller、權限

### 6.1 UI 與 Controller（`Admin/RenewalOrderController`）

- 訂單詳情頁（show）：`paid`/`partially_refunded` 時顯示「退款」按鈕 + 退款紀錄列表（`type=refund` 交易）
- `refundForm($orderId)`：顯示原付款資訊、已退金額、**可退餘額**、系統建議動作（可改）、金額輸入、「扣回服務期」勾選（預設勾）、退款原因（必填）
- `refund($orderId)` POST：呼叫 `RefundService->refund()`，flash 結果
- 路由放現有 renewal-orders 群組

### 6.2 權限：超管 + 主帳號（限自己旗下）

- 沿用現有 `check.main` 群組（主帳號 + 超管）
- Controller 內加歸屬檢查：主帳號只能退自己旗下子帳號的訂單，超管不限（比照現有 `canBeViewedBy` 模式）

## 7. 測試策略

- `RefundService` 單元測試：防超退、狀態流轉、到期日回滾、**失敗不改訂單**、併發鎖
- 藍新：ccore 測試環境**可實測退款**（整合測試）
- 綠界：正式環境小額實測（無測試環境）→ 階段 2
- 銀行轉帳 manual：純邏輯測試

## 8. 分階段交付範圍

- **階段 1**：資料模型 + 契約擴充 + 藍新退款 + 銀行轉帳純記錄 + 後台 UI + `RefundService` + 測試 → 可完整驗收
- **階段 2**：綠界退款（正式環境驗證端點與關帳時序）後開啟

## 9. 風險與注意事項

- **綠界無退款測試環境**：官方明載授權無法於測試環境提供，退款只能正式環境小額實測。
- **綠界套件端點疑似過時**：Omnipay 走 `payment.ecpay.com.tw/CreditDetail/DoAction`，官方現行為 `ecpayment.ecpay.com.tw/1.0.0/Credit/DoAction`；需正式環境實測確認。
- **綠界關帳時序**：授權後隔日 6:00 自動關帳；當天退款屬未關帳，需作廢而非退刷。
- **綠界帳戶餘額**：退刷需帳戶餘額足夠。
- **藍新退款期限**：交易後 90 天內可退。

## 10. 不在本次範圍（YAGNI）

- 用戶自助退款（一律由管理員操作）
- 退款金額按服務期比例自動計算（採固定天數扣回）
- 自動對帳 / 退款報表（後續視需求另議）
