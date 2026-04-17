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
            $table->string('phone', 30)->nullable()->after('signature')->comment('會員聯絡電話，供名片預覽頁「打電話」按鈕使用');
            $table->string('line_url', 500)->nullable()->after('phone')->comment('會員 LINE 連結，供名片預覽頁「加 LINE」按鈕使用');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'line_url']);
        });
    }
};
