<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->enum('type', ['payment', 'refund'])->default('payment')->after('order_id');
            $table->unsignedBigInteger('parent_transaction_id')->nullable()->after('type');
            $table->string('refund_action', 20)->nullable()->after('parent_transaction_id'); // refund / void / manual

            $table->foreign('parent_transaction_id')->references('id')->on('payment_transactions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropForeign(['parent_transaction_id']);
            $table->dropColumn(['type', 'parent_transaction_id', 'refund_action']);
        });
    }
};
