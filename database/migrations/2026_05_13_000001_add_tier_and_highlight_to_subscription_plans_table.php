<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->string('plan_tier', 20)->nullable()->after('name');
            $table->unsignedSmallInteger('page_limit')->nullable()->after('plan_tier');
            $table->string('target_audience', 255)->nullable()->after('description');
            $table->boolean('is_popular')->default(false)->after('active');
            $table->boolean('is_featured')->default(false)->after('is_popular');
            $table->index('plan_tier');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropIndex(['plan_tier']);
            $table->dropColumn([
                'plan_tier',
                'page_limit',
                'target_audience',
                'is_popular',
                'is_featured',
            ]);
        });
    }
};
