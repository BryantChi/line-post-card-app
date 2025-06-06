<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('模板名稱');
            $table->string('description')->nullable()->comment('模板描述');
            $table->string('preview_image')->nullable()->comment('用於前端顯示的縮圖');
            $table->json('template_schema')->comment('JSON 結構：定義預設版型');
            $table->json('editable_fields')->nullable()->comment('JSON 結構：定義可編輯欄位');
            $table->boolean('active')->default(true)->comment('是否啟用');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('card_templates');
    }
};
