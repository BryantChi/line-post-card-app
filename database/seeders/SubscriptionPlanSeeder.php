<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'          => '月繳方案',
                'description'   => '每月自動續約，彈性選擇',
                'price'         => 299,
                'duration_days' => 30,
                'active'        => true,
                'sort_order'    => 1,
            ],
            [
                'name'          => '季繳方案',
                'description'   => '每季付款，比月繳更划算',
                'price'         => 799,
                'duration_days' => 90,
                'active'        => true,
                'sort_order'    => 2,
            ],
            [
                'name'          => '年繳方案',
                'description'   => '年付方案，享最優惠價格',
                'price'         => 2999,
                'duration_days' => 365,
                'active'        => true,
                'sort_order'    => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::firstOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}
