<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /** 需要加密儲存的 key */
    protected static array $encryptedKeys = [
        'ecpay_test_merchant_id',
        'ecpay_test_hash_key',
        'ecpay_test_hash_iv',
        'ecpay_prod_merchant_id',
        'ecpay_prod_hash_key',
        'ecpay_prod_hash_iv',
        'newebpay_test_merchant_id',
        'newebpay_test_hash_key',
        'newebpay_test_hash_iv',
        'newebpay_prod_merchant_id',
        'newebpay_prod_hash_key',
        'newebpay_prod_hash_iv',
    ];

    public static function get(string $key, mixed $default = null): ?string
    {
        return Cache::remember("system_setting.{$key}", 60, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            if (!$setting) {
                return $default;
            }

            if (in_array($key, static::$encryptedKeys) && $setting->value) {
                try {
                    return Crypt::decryptString($setting->value);
                } catch (\Exception $e) {
                    return $setting->value; // 相容未加密的舊值
                }
            }

            return $setting->value;
        });
    }

    public static function set(string $key, ?string $value): void
    {
        $storedValue = $value;
        if (in_array($key, static::$encryptedKeys) && $value) {
            $storedValue = Crypt::encryptString($value);
        }

        static::updateOrCreate(['key' => $key], ['value' => $storedValue]);
        Cache::forget("system_setting.{$key}");
    }

    public static function isRenewalEnabled(): bool
    {
        return static::get('renewal_enabled', 'true') === 'true';
    }

    public static function getEcpayMode(): string
    {
        return static::get('ecpay_mode', 'production');
    }

    public static function getNewebpayMode(): string
    {
        return static::get('newebpay_mode', 'test');
    }

    /**
     * 取得啟用中的金流 code 清單 (依設定順序回傳)
     * @return string[]
     */
    public static function getActiveGateways(): array
    {
        $json = static::get('active_payment_gateways', '["newebpay","bank_transfer"]');
        $list = json_decode($json, true);

        return is_array($list) ? array_values($list) : ['newebpay', 'bank_transfer'];
    }

    /**
     * 預設金流 code (前台預選用)
     */
    public static function getDefaultGateway(): ?string
    {
        return static::get('default_payment_gateway', 'newebpay');
    }

    /**
     * 取得測試帳號 ID 列表
     */
    public static function getRenewalTestUserIds(): array
    {
        $json = static::get('renewal_test_user_ids', '[]');
        return json_decode($json, true) ?: [];
    }

    /**
     * 判斷指定用戶是否可使用續約功能
     */
    public static function canUserAccessRenewal(int $userId): bool
    {
        if (!static::isRenewalEnabled()) {
            return false;
        }

        if (static::getEcpayMode() === 'test') {
            return in_array($userId, static::getRenewalTestUserIds());
        }

        return true;
    }

    /**
     * 取得指定模式的 ECPay 憑證
     * 優先讀資料庫設定，fallback 到 config（.env）
     */
    public static function getEcpayCredentials(string $mode): array
    {
        $prefix = $mode === 'test' ? 'ecpay_test' : 'ecpay_prod';

        return [
            'merchant_id' => static::get("{$prefix}_merchant_id") ?: config("ecpay.{$mode}.merchant_id"),
            'hash_key'    => static::get("{$prefix}_hash_key")    ?: config("ecpay.{$mode}.hash_key"),
            'hash_iv'     => static::get("{$prefix}_hash_iv")     ?: config("ecpay.{$mode}.hash_iv"),
            'gateway_url' => static::get("{$prefix}_gateway_url") ?: config("ecpay.{$mode}.gateway_url"),
        ];
    }

    /**
     * 檢查指定模式的憑證是否完整
     */
    public static function hasEcpayCredentials(string $mode): bool
    {
        $creds = static::getEcpayCredentials($mode);
        return !empty($creds['merchant_id']) && !empty($creds['hash_key']) && !empty($creds['hash_iv']);
    }

    /**
     * 取得指定模式的 NewebPay 憑證
     * 優先讀資料庫設定,fallback 到 config/payment.php (.env)
     */
    public static function getNewebpayCredentials(string $mode): array
    {
        $prefix = $mode === 'test' ? 'newebpay_test' : 'newebpay_prod';

        return [
            'merchant_id' => static::get("{$prefix}_merchant_id") ?: config("payment.newebpay.{$mode}.merchant_id"),
            'hash_key'    => static::get("{$prefix}_hash_key")    ?: config("payment.newebpay.{$mode}.hash_key"),
            'hash_iv'     => static::get("{$prefix}_hash_iv")     ?: config("payment.newebpay.{$mode}.hash_iv"),
            'gateway_url' => config("payment.newebpay.{$mode}.gateway_url"),
        ];
    }

    public static function hasNewebpayCredentials(string $mode): bool
    {
        $creds = static::getNewebpayCredentials($mode);
        return !empty($creds['merchant_id']) && !empty($creds['hash_key']) && !empty($creds['hash_iv']);
    }

    /**
     * 第一次設計費（新台幣整數）
     */
    public static function getFirstTimeDesignFee(): int
    {
        return (int) static::get('first_time_design_fee', '1500');
    }

    /**
     * 重新開通設定費（新台幣整數）
     */
    public static function getReactivationSetupFee(): int
    {
        return (int) static::get('reactivation_setup_fee', '2000');
    }

    /**
     * 名片保留天數（到期未續約後的保留期）
     */
    public static function getCardRetentionDays(): int
    {
        return (int) static::get('card_retention_days', '90');
    }
}
