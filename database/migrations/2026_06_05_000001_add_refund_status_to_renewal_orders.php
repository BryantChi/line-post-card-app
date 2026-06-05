<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('renewal_orders', function (Blueprint $table) {
            $table->unsignedInteger('refunded_amount')->default(0)->after('amount');
        });

        // status 為 ENUM,需以 raw SQL 擴充新增 refunded / partially_refunded
        DB::statement("ALTER TABLE renewal_orders MODIFY COLUMN status ENUM('pending','paid','cancelled','expired','refunded','partially_refunded') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("UPDATE renewal_orders SET status='paid' WHERE status IN ('refunded','partially_refunded')");
        DB::statement("ALTER TABLE renewal_orders MODIFY COLUMN status ENUM('pending','paid','cancelled','expired') NOT NULL DEFAULT 'pending'");

        Schema::table('renewal_orders', function (Blueprint $table) {
            $table->dropColumn('refunded_amount');
        });
    }
};
