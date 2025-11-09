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
        Schema::table('user_login_logs', function (Blueprint $table) {
            $table->timestamp('logged_out_at')->nullable()->after('logged_in_at')->comment('登出時間');
            $table->integer('session_duration')->nullable()->after('logged_out_at')->comment('線上時長(秒)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_login_logs', function (Blueprint $table) {
            $table->dropColumn(['logged_out_at', 'session_duration']);
        });
    }
};
