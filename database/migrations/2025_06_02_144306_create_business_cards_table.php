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
        Schema::create('business_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('使用者 ID');
            $table->string('title')->comment('卡片組名稱');
            $table->string('subtitle')->nullable()->comment('卡片副標題');
            $table->string('profile_image')->nullable()->comment('卡片頭像/Logo');
            $table->text('content')->nullable()->comment('卡片簡介');
            $table->json('flex_json')->nullable()->comment('合併後的 Flex JSON');
            $table->uuid('uuid')->unique()->comment('分享用唯一連結');
            $table->boolean('active')->default(true)->comment('是否啟用');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_cards');
    }
};
