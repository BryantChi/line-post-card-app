<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\PaymentGateways\PaymentGatewayManager;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;

class SystemSettingsController extends Controller
{
    public function index(PaymentGatewayManager $paymentManager)
    {
        $renewalEnabled = SystemSetting::isRenewalEnabled();
        $ecpayMode      = SystemSetting::getEcpayMode();
        $newebpayMode   = SystemSetting::getNewebpayMode();
        $testUserIds    = SystemSetting::getRenewalTestUserIds();

        // 取得所有子帳號供測試帳號選擇
        $subUsers = User::where('role', 'sub_user')
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        // ECPay 憑證
        $testCreds = SystemSetting::getEcpayCredentials('test');
        $prodCreds = SystemSetting::getEcpayCredentials('production');
        $testCredsInDb = !empty(SystemSetting::get('ecpay_test_merchant_id'));
        $prodCredsInDb = !empty(SystemSetting::get('ecpay_prod_merchant_id'));

        // NewebPay 憑證
        $newebpayTestCreds = SystemSetting::getNewebpayCredentials('test');
        $newebpayProdCreds = SystemSetting::getNewebpayCredentials('production');
        $newebpayTestCredsInDb = !empty(SystemSetting::get('newebpay_test_merchant_id'));
        $newebpayProdCredsInDb = !empty(SystemSetting::get('newebpay_prod_merchant_id'));

        // 金流啟用設定
        $activeGateways  = SystemSetting::getActiveGateways();
        $defaultGateway  = SystemSetting::getDefaultGateway();
        $allGatewayCodes = $paymentManager->allCodes();
        $gatewayLabels   = [];
        foreach ($allGatewayCodes as $code) {
            $gatewayLabels[$code] = $paymentManager->driver($code)->label();
        }

        // 訂閱費用相關設定
        $firstTimeDesignFee   = SystemSetting::getFirstTimeDesignFee();
        $reactivationSetupFee = SystemSetting::getReactivationSetupFee();
        $cardRetentionDays    = SystemSetting::getCardRetentionDays();

        return view('admin.system_settings.index', compact(
            'renewalEnabled', 'ecpayMode', 'newebpayMode', 'testUserIds', 'subUsers',
            'testCreds', 'prodCreds', 'testCredsInDb', 'prodCredsInDb',
            'newebpayTestCreds', 'newebpayProdCreds', 'newebpayTestCredsInDb', 'newebpayProdCredsInDb',
            'activeGateways', 'defaultGateway', 'allGatewayCodes', 'gatewayLabels',
            'firstTimeDesignFee', 'reactivationSetupFee', 'cardRetentionDays'
        ));
    }

    public function update(Request $request, PaymentGatewayManager $paymentManager)
    {
        $allGatewayCodes = $paymentManager->allCodes();
        $allCodesRule    = 'in:' . implode(',', $allGatewayCodes);

        $request->validate([
            'renewal_enabled'           => 'required|in:true,false',
            'ecpay_mode'                => 'required|in:test,production',
            'newebpay_mode'             => 'required|in:test,production',
            'renewal_test_user_ids'     => 'nullable|array',
            'renewal_test_user_ids.*'   => 'integer|exists:users,id',

            // 金流啟用設定
            'active_payment_gateways'   => 'required|array|min:1',
            'active_payment_gateways.*' => $allCodesRule,
            'default_payment_gateway'   => 'required|' . $allCodesRule,

            // ECPay 憑證
            'ecpay_test_merchant_id'    => 'nullable|string|max:20',
            'ecpay_test_hash_key'       => 'nullable|string|max:50',
            'ecpay_test_hash_iv'        => 'nullable|string|max:50',
            'ecpay_test_gateway_url'    => 'nullable|url|max:255',
            'ecpay_prod_merchant_id'    => 'nullable|string|max:20',
            'ecpay_prod_hash_key'       => 'nullable|string|max:50',
            'ecpay_prod_hash_iv'        => 'nullable|string|max:50',
            'ecpay_prod_gateway_url'    => 'nullable|url|max:255',

            // NewebPay 憑證
            'newebpay_test_merchant_id' => 'nullable|string|max:30',
            'newebpay_test_hash_key'    => 'nullable|string|max:50',
            'newebpay_test_hash_iv'     => 'nullable|string|max:50',
            'newebpay_prod_merchant_id' => 'nullable|string|max:30',
            'newebpay_prod_hash_key'    => 'nullable|string|max:50',
            'newebpay_prod_hash_iv'     => 'nullable|string|max:50',

            // 訂閱方案費用設定
            'first_time_design_fee'     => 'required|integer|min:0|max:999999',
            'reactivation_setup_fee'    => 'required|integer|min:0|max:999999',
            'card_retention_days'       => 'required|integer|min:0|max:3650',
        ]);

        // default gateway 必須在 active 清單中
        if (!in_array($request->default_payment_gateway, $request->active_payment_gateways, true)) {
            Flash::error('預設金流必須是啟用中的金流之一');
            return redirect()->route('admin.systemSettings.index');
        }

        // 儲存憑證(只在有填值時更新,空值不覆蓋)
        $credentialKeys = [
            'ecpay_test_merchant_id', 'ecpay_test_hash_key', 'ecpay_test_hash_iv', 'ecpay_test_gateway_url',
            'ecpay_prod_merchant_id', 'ecpay_prod_hash_key', 'ecpay_prod_hash_iv', 'ecpay_prod_gateway_url',
            'newebpay_test_merchant_id', 'newebpay_test_hash_key', 'newebpay_test_hash_iv',
            'newebpay_prod_merchant_id', 'newebpay_prod_hash_key', 'newebpay_prod_hash_iv',
        ];
        foreach ($credentialKeys as $key) {
            $value = $request->input($key);
            if ($value !== null && $value !== '') {
                SystemSetting::set($key, $value);
            }
        }

        // 切換到正式模式時,檢查正式環境憑證是否完整
        if ($request->ecpay_mode === 'production'
            && in_array('ecpay', $request->active_payment_gateways, true)
            && !SystemSetting::hasEcpayCredentials('production')) {
            Flash::error('無法將綠界金流切換至正式模式:正式環境憑證尚未設定完整');
            return redirect()->route('admin.systemSettings.index');
        }
        if ($request->newebpay_mode === 'production'
            && in_array('newebpay', $request->active_payment_gateways, true)
            && !SystemSetting::hasNewebpayCredentials('production')) {
            Flash::error('無法將藍新金流切換至正式模式:正式環境憑證尚未設定完整');
            return redirect()->route('admin.systemSettings.index');
        }

        SystemSetting::set('renewal_enabled', $request->renewal_enabled);
        SystemSetting::set('ecpay_mode', $request->ecpay_mode);
        SystemSetting::set('newebpay_mode', $request->newebpay_mode);
        SystemSetting::set('renewal_test_user_ids', json_encode($request->renewal_test_user_ids ?? []));

        // 金流啟用設定
        SystemSetting::set('active_payment_gateways', json_encode(array_values($request->active_payment_gateways)));
        SystemSetting::set('default_payment_gateway', $request->default_payment_gateway);

        // 訂閱方案費用設定
        SystemSetting::set('first_time_design_fee', (string) $request->first_time_design_fee);
        SystemSetting::set('reactivation_setup_fee', (string) $request->reactivation_setup_fee);
        SystemSetting::set('card_retention_days', (string) $request->card_retention_days);

        Flash::success('系統設定已更新');
        return redirect()->route('admin.systemSettings.index');
    }
}
