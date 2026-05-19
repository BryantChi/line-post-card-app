<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();
        $defaults = [
            ['key' => 'first_time_design_fee',  'value' => '1500'],
            ['key' => 'reactivation_setup_fee', 'value' => '2000'],
            ['key' => 'card_retention_days',    'value' => '90'],
        ];

        foreach ($defaults as $row) {
            $exists = DB::table('system_settings')->where('key', $row['key'])->exists();
            if (!$exists) {
                DB::table('system_settings')->insert(array_merge($row, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }
    }

    public function down(): void
    {
        DB::table('system_settings')->whereIn('key', [
            'first_time_design_fee',
            'reactivation_setup_fee',
            'card_retention_days',
        ])->delete();
    }
};
