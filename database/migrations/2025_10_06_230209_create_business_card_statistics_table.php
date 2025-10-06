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
        Schema::create('business_card_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_card_id')->constrained('business_cards')->onDelete('cascade');
            $table->date('date')->comment('統計日期');
            $table->unsignedInteger('views')->default(0)->comment('當日點閱數');
            $table->unsignedInteger('shares')->default(0)->comment('當日分享數');
            $table->timestamps();

            // 確保每張名片每天只有一筆記錄
            $table->unique(['business_card_id', 'date']);

            // 加速查詢
            $table->index('date');
            $table->index(['business_card_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_card_statistics');
    }
};
