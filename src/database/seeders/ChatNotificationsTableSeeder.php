<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatNotificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('chat_notifications')->insert([
            'chat_id' => 1,
            'receiver_id' => 1,
            'message_count' => 1,
            'is_read' => true,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        DB::table('chat_notifications')->insert([
            'chat_id' => 1,
            'receiver_id' => 3,
            'message_count' => 1,
            'is_read' => true,
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]);

        DB::table('chat_notifications')->insert([
            'chat_id' => 1,
            'receiver_id' => 1,
            'message_count' => 1,
            'is_read' => false,
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        DB::table('chat_notifications')->insert([
            'chat_id' => 2,
            'receiver_id' => 2,
            'message_count' => 1,
            'is_read' => true,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        DB::table('chat_notifications')->insert([
            'chat_id' => 2,
            'receiver_id' => 3,
            'message_count' => 1,
            'is_read' => false,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        DB::table('chat_notifications')->insert([
            'chat_id' => 2,
            'receiver_id' => 3,
            'message_count' => 2,
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
