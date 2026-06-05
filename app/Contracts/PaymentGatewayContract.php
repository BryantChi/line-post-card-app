<?php

namespace App\Contracts;

use App\Models\RenewalOrder;

interface PaymentGatewayContract
{
    /** Gateway 識別碼 (對應 config/payment.php gateways key) */
    public function code(): string;

    /** RenewalOrder.payment_method 欄位儲存的值 */
    public function paymentMethodValue(): string;

    /** 前台/後台顯示用的人類可讀名稱 */
    public function label(): string;

    /** 是否需要跨域 redirect (信用卡=true,銀行轉帳=false) */
    public function isRedirect(): bool;

    /** 是否在後台 active gateways 中啟用 */
    public function isActive(): bool;

    /**
     * 產生自動送出的付款表單 HTML
     * 僅在 isRedirect()=true 時實作,否則拋出例外
     */
    public function buildCheckoutForm(RenewalOrder $order): string;

    /**
     * 處理伺服器端 notify 回呼
     * @return string 回傳給金流商的回應內容
     */
    public function processNotify(array $postData): string;

    /**
     * 從 notify post data 抽出本系統的 order_no (供 returnResult PRG redirect 用)
     */
    public function extractOrderNo(array $postData): ?string;

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
}
