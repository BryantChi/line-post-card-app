<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE renewal_orders MODIFY COLUMN payment_method ENUM('ecpay_credit', 'newebpay_credit', 'bank_transfer', 'cash') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("UPDATE renewal_orders SET payment_method = 'ecpay_credit' WHERE payment_method = 'newebpay_credit'");
        DB::statement("ALTER TABLE renewal_orders MODIFY COLUMN payment_method ENUM('ecpay_credit', 'bank_transfer', 'cash') NOT NULL");
    }
};
