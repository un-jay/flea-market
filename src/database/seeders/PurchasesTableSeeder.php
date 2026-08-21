<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchasesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('purchases')->insert([
            "user_id" => 3,
            "item_id" => 5,
            'shipping_postal_code' => '158-0094',
            'shipping_address' => '東京都世田谷区玉川1-14-1',
            'shipping_building' => '楽天クリムゾンハウス',
            'payment_method' => '1',
            'created_at' => now()->subDays(rand(0, 3650)),
            'updated_at' => now(),
        ]);

        DB::table('purchases')->insert([
            "user_id" => 3,
            "item_id" => 7,
            'shipping_postal_code' => '158-0094',
            'shipping_address' => '東京都世田谷区玉川1-14-1',
            'shipping_building' => '楽天クリムゾンハウス',
            'payment_method' => '2',
            'created_at' => now()->subDays(rand(0, 3650)),
            'updated_at' => now(),
        ]);
    }
}
