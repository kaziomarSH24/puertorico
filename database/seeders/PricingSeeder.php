<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subscriptions = [
            ['id' => 1, 'plan_name' => 'daily', 'price' => 5.99, 'audio_limit' => 15, 'order' => 1, 'created_at' => Carbon::create(2025, 3, 12, 8, 33, 35), 'updated_at' => Carbon::create(2025, 3, 12, 8, 33, 35)],
            ['id' => 2, 'plan_name' => 'weekly', 'price' => 20.00, 'audio_limit' => 100, 'order' => 2, 'created_at' => Carbon::create(2025, 3, 12, 8, 33, 55), 'updated_at' => Carbon::create(2025, 3, 12, 8, 33, 55)],
            ['id' => 3, 'plan_name' => 'monthly', 'price' => 35.00, 'audio_limit' => 300, 'order' => 3, 'created_at' => Carbon::create(2025, 3, 12, 8, 34, 35), 'updated_at' => Carbon::create(2025, 3, 12, 8, 34, 35)],
            ['id' => 4, 'plan_name' => 'yearly', 'price' => 49.90, 'audio_limit' => -1, 'order' => 4, 'created_at' => Carbon::create(2025, 3, 12, 8, 35, 0), 'updated_at' => Carbon::create(2025, 3, 12, 8, 35, 27)],
        ];

        DB::table('pricing_plans')->insert($subscriptions);
    }
}
