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
            if (!Schema::hasColumn('users', 'role')) {
                // 新增角色欄位 (super_admin, main_user, sub_user)
                $table->string('role')->default('sub_user')->after('password');
            }

            // 如果尚未有這些欄位，則新增
            if (!Schema::hasColumn('users', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('role');
                $table->foreign('parent_id')->references('id')->on('users')->onDelete('cascade');
            }

            if (!Schema::hasColumn('users', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('parent_id');
            }

            if (!Schema::hasColumn('users', 'active')) {
                $table->boolean('active')->default(true)->after('expires_at');
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['role', 'parent_id', 'expires_at', 'active']);

        });
    }
};
