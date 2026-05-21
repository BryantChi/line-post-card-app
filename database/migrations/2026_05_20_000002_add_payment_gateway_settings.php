<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $defaults = [
            ['key' => 'active_payment_gateways',  'value' => json_encode(['newebpay', 'bank_transfer'])],
            ['key' => 'default_payment_gateway',  'value' => 'newebpay'],
            ['key' => 'newebpay_mode',            'value' => 'test'],
        ];

        foreach ($defaults as $row) {
            $exists = DB::table('system_settings')->where('key', $row['key'])->exists();
            if (!$exists) {
                DB::table('system_settings')->insert(array_merge($row, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }
    }

    public function down(): void
    {
        DB::table('system_settings')->whereIn('key', [
            'active_payment_gateways',
            'default_payment_gateway',
            'newebpay_mode',
            'newebpay_test_merchant_id',
            'newebpay_test_hash_key',
            'newebpay_test_hash_iv',
            'newebpay_prod_merchant_id',
            'newebpay_prod_hash_key',
            'newebpay_prod_hash_iv',
        ])->delete();
    }
};
