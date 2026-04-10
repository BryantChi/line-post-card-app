<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // 預設資料
        DB::table('system_settings')->insert([
            ['key' => 'renewal_enabled', 'value' => 'true', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'ecpay_mode', 'value' => 'test', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'renewal_test_user_ids', 'value' => '[]', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
