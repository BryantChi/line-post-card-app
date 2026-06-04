<?php

namespace App\Console\Commands;

use App\Models\RenewalOrder;
use App\Models\SystemSetting;
use App\Services\PaymentGateways\NewebpayGateway;
use Illuminate\Console\Command;
use Omnipay\NewebPay\Encryptor;

/**
 * 【僅供本地 / 測試環境使用】
 *
 * 模擬藍新 (NewebPay) 的伺服器端 notify 回呼,不需真的去藍新刷卡,
 * 就能反覆測試 processNotify 的完整流程 (含 TradeSha 驗章、金額一致性、
 * 冪等、終態保護、延長到期日)。
 *
 * 原理:用後台系統設定的「藍新測試金鑰」把回應資料加密成 TradeInfo + TradeSha,
 * 與正式回呼用同一組金鑰,因此能通過 NewebpayGateway::parseVerifiedData 的驗章。
 *
 * 用法:
 *   php artisan newebpay:simulate-notify {order_no}                  # 模擬付款成功
 *   php artisan newebpay:simulate-notify {order_no} --status=FAIL    # 模擬付款失敗
 */
class SimulateNewebpayNotify extends Command
{
    protected $signature = 'newebpay:simulate-notify
                            {order_no : 要模擬回呼的訂單編號 (RenewalOrder.order_no)}
                            {--status=SUCCESS : 回呼狀態,SUCCESS=付款成功,其他值=付款失敗}';

    protected $description = '【僅供測試】模擬藍新 notify 伺服器回呼,產生通得過驗章的加密 payload 並送入 processNotify';

    public function handle(NewebpayGateway $gateway): int
    {
        // 安全防護 1:絕不可在 production 執行 (會把訂單改成已付款)
        if (app()->isProduction()) {
            $this->error('禁止在 production 環境執行此模擬指令。');
            return self::FAILURE;
        }

        // 安全防護 2:只允許在藍新「測試模式」下執行
        $mode = SystemSetting::getNewebpayMode();
        if ($mode !== 'test') {
            $this->error("藍新目前為「{$mode}」模式,為安全起見只允許在 test 模式下模擬。請先到後台系統設定切換為測試模式。");
            return self::FAILURE;
        }

        $orderNo = $this->argument('order_no');
        $order = RenewalOrder::where('order_no', $orderNo)->first();
        if (!$order) {
            $this->error("找不到訂單:{$orderNo}");
            return self::FAILURE;
        }

        $creds = SystemSetting::getNewebpayCredentials('test');
        if (empty($creds['merchant_id']) || empty($creds['hash_key']) || empty($creds['hash_iv'])) {
            $this->error('藍新測試憑證不完整 (merchant_id / hash_key / hash_iv),請先於後台系統設定填入。');
            return self::FAILURE;
        }

        $status = (string) $this->option('status');

        $this->newLine();
        $this->info("回呼前 → 訂單 {$orderNo}|狀態 {$order->status}|金額 {$order->amount}");
        $this->line("模擬送出 → Status={$status}");

        // 用與正式回呼相同的金鑰加密,確保能通過 parseVerifiedData 的 TradeSha 驗章
        $encryptor = new Encryptor($creds['hash_key'], $creds['hash_iv']);

        $result = [
            'Status'          => $status,
            'MerchantID'      => $creds['merchant_id'],
            'MerchantOrderNo' => $order->order_no,
            'Amt'             => (string) $order->amount,
            'TradeNo'         => 'SIM' . now()->format('YmdHis'),
            'PaymentType'     => 'CREDIT',
            'RespondType'     => 'JSON',
            'PayTime'         => now()->format('Y-m-d H:i:s'),
            'Message'         => $status === 'SUCCESS' ? '模擬授權成功' : '模擬授權失敗',
        ];

        $tradeInfo = $encryptor->encrypt($result);
        $tradeSha  = $encryptor->tradeSha($tradeInfo);

        $postData = [
            'Status'     => $status,
            'MerchantID' => $creds['merchant_id'],
            'TradeInfo'  => $tradeInfo,
            'TradeSha'   => $tradeSha,
        ];

        $response = $gateway->processNotify($postData);
        $this->line("processNotify 回應 → <comment>{$response}</comment>");

        $order->refresh();
        $this->info("回呼後 → 訂單 {$orderNo}|狀態 {$order->status}");
        $this->newLine();

        return self::SUCCESS;
    }
}
