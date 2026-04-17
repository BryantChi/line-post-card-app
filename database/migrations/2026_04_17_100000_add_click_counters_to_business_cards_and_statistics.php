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
        Schema::table('business_cards', function (Blueprint $table) {
            $table->unsignedInteger('call_clicks')->default(0)->after('shares');
            $table->unsignedInteger('line_clicks')->default(0)->after('call_clicks');
        });

        Schema::table('business_card_statistics', function (Blueprint $table) {
            $table->unsignedInteger('call_clicks')->default(0)->after('shares')->comment('當日撥打電話點擊數');
            $table->unsignedInteger('line_clicks')->default(0)->after('call_clicks')->comment('當日加 LINE 點擊數');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_cards', function (Blueprint $table) {
            $table->dropColumn(['call_clicks', 'line_clicks']);
        });

        Schema::table('business_card_statistics', function (Blueprint $table) {
            $table->dropColumn(['call_clicks', 'line_clicks']);
        });
    }
};
