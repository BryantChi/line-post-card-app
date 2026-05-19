<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;

class SystemSettingsController extends Controller
{
    public function index()
    {
        $renewalEnabled = SystemSetting::isRenewalEnabled();
        $ecpayMode      = SystemSetting::getEcpayMode();
        $testUserIds    = SystemSetting::getRenewalTestUserIds();

        // 取得所有子帳號供測試帳號選擇
        $subUsers = User::where('role', 'sub_user')
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        // 憑證：含 fallback 的完整值（用於 placeholder 顯示）
        $testCreds = SystemSetting::getEcpayCredentials('test');
        $prodCreds = SystemSetting::getEcpayCredentials('production');

        // 資料庫中是否有自行設定過憑證（不含 config fallback）
        $testCredsInDb = !empty(SystemSetting::get('ecpay_test_merchant_id'));
        $prodCredsInDb = !empty(SystemSetting::get('ecpay_prod_merchant_id'));

        // 訂閱費用相關設定
        $firstTimeDesignFee   = SystemSetting::getFirstTimeDesignFee();
        $reactivationSetupFee = SystemSetting::getReactivationSetupFee();
        $cardRetentionDays    = SystemSetting::getCardRetentionDays();

        return view('admin.system_settings.index', compact(
            'renewalEnabled', 'ecpayMode', 'testUserIds', 'subUsers',
            'testCreds', 'prodCreds', 'testCredsInDb', 'prodCredsInDb',
            'firstTimeDesignFee', 'reactivationSetupFee', 'cardRetentionDays'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'renewal_enabled'         => 'required|in:true,false',
            'ecpay_mode'              => 'required|in:test,production',
            'renewal_test_user_ids'   => 'nullable|array',
            'renewal_test_user_ids.*' => 'integer|exists:users,id',
            // 憑證欄位（選填，空值表示不修改）
            'ecpay_test_merchant_id'  => 'nullable|string|max:20',
            'ecpay_test_hash_key'     => 'nullable|string|max:50',
            'ecpay_test_hash_iv'      => 'nullable|string|max:50',
            'ecpay_test_gateway_url'  => 'nullable|url|max:255',
            'ecpay_prod_merchant_id'  => 'nullable|string|max:20',
            'ecpay_prod_hash_key'     => 'nullable|string|max:50',
            'ecpay_prod_hash_iv'      => 'nullable|string|max:50',
            'ecpay_prod_gateway_url'  => 'nullable|url|max:255',
            // 訂閱方案費用設定
            'first_time_design_fee'   => 'required|integer|min:0|max:999999',
            'reactivation_setup_fee'  => 'required|integer|min:0|max:999999',
            'card_retention_days'     => 'required|integer|min:0|max:3650',
        ]);

        // 儲存憑證（只在有填值時更新，空值不覆蓋）
        $credentialKeys = [
            'ecpay_test_merchant_id', 'ecpay_test_hash_key', 'ecpay_test_hash_iv', 'ecpay_test_gateway_url',
            'ecpay_prod_merchant_id', 'ecpay_prod_hash_key', 'ecpay_prod_hash_iv', 'ecpay_prod_gateway_url',
        ];
        foreach ($credentialKeys as $key) {
            $value = $request->input($key);
            if ($value !== null && $value !== '') {
                SystemSetting::set($key, $value);
            }
        }

        // 切換到正式模式時，檢查正式環境憑證是否完整
        if ($request->ecpay_mode === 'production' && !SystemSetting::hasEcpayCredentials('production')) {
            Flash::error('無法切換至正式模式：正式環境金流憑證尚未設定完整');
            return redirect()->route('admin.systemSettings.index');
        }

        SystemSetting::set('renewal_enabled', $request->renewal_enabled);
        SystemSetting::set('ecpay_mode', $request->ecpay_mode);
        SystemSetting::set('renewal_test_user_ids', json_encode($request->renewal_test_user_ids ?? []));

        // 訂閱方案費用設定
        SystemSetting::set('first_time_design_fee', (string) $request->first_time_design_fee);
        SystemSetting::set('reactivation_setup_fee', (string) $request->reactivation_setup_fee);
        SystemSetting::set('card_retention_days', (string) $request->card_retention_days);

        Flash::success('系統設定已更新');
        return redirect()->route('admin.systemSettings.index');
    }
}
