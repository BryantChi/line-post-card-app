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
        Schema::create('card_bubbles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('card_id')->comment('對應 business_cards.id');
            $table->unsignedBigInteger('template_id')->comment('使用的模板 ID');
            $table->string('title')->comment('氣泡卡片標題');
            $table->string('subtitle')->nullable()->comment('氣泡卡片副標題');
            $table->string('image')->nullable()->comment('氣泡卡片圖片');
            $table->text('content')->nullable()->comment('卡片內容');
            $table->json('bubble_data')->nullable()->comment('卡片欄位資料');
            $table->json('json_content')->nullable()->comment('儲存解析後的 Flex Bubble JSON');
            $table->integer('order')->default(0)->comment('排序順序');
            $table->boolean('active')->default(true)->comment('是否啟用');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('card_id')->references('id')->on('business_cards')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('card_templates')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_bubbles');
    }
};
