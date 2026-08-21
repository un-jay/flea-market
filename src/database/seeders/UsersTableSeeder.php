<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            "id" => 1,
            'user_name' => 'mercari',
            'email' => 'mercari@coachtech.com',
            'password' => Hash::make('password'),
            'postal_code' => '106-6118',
            'address' => '東京都港区六本木6-10-1',
            'building' => '六本木ヒルズ森タワー',
            'created_at' => now()->subDays(rand(0, 3650)), // ランダムな過去の日付と時間を挿入
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            "id" => 2,
            'user_name' => 'amazon',
            'email' => 'amazon@coachtech.com',
            'password' => Hash::make('password'),
            'created_at' => now()->subDays(rand(0, 3650)), // ランダムな過去の日付と時間を挿入
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            "id" => 3,
            'user_name' => 'rakuten',
            'email' => 'rakuten@coachtech.com',
            'password' => Hash::make('password'),
            'postal_code' => '158-0094',
            'address' => '東京都世田谷区玉川1-14-1',
            'building' => '楽天クリムゾンハウス',
            'created_at' => now()->subDays(rand(0, 3650)), // ランダムな過去の日付と時間を挿入
            'updated_at' => now(),
        ]);
    }
}
