<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('max_business_cards')->default(1)
                  ->after('parent_id')
                  ->comment('子帳號可建立的名片數量上限');

            $table->unsignedInteger('max_card_bubbles')->default(10)
                  ->after('max_business_cards')
                  ->comment('每張名片的卡片數量上限(最大10)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['max_business_cards', 'max_card_bubbles']);
        });
    }
};
