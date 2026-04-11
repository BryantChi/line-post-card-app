<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            // MySQL 允許 nullable unique index（多個 NULL 被視為不同值，不衝突）
            $table->unique('transaction_no', 'uniq_payment_transaction_no');
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropUnique('uniq_payment_transaction_no');
        });
    }
};
