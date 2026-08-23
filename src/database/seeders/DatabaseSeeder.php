<?php

namespace Database\Seeders;

use App\Models\Chat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(ItemsTableSeeder::class);
        $this->call(CategoriesTableSeeder::class);
        $this->call(ItemCategoriesTableSeeder::class);
        $this->call(PurchasesTableSeeder::class);
        $this->call(ChatsTableSeeder::class);
        $this->call(ChatMessagesTableSeeder::class);
        $this->call(ChatNotificationsTableSeeder::class);
        $this->call(RatingsTableSeeder::class);

        $this->syncPostgresSequences();
    }

    /**
     * PostgreSQLはid列に明示的な値をINSERTしても連番用のシーケンスが
     * 自動では進まない（MySQLのAUTO_INCREMENTと異なる挙動）。
     * シーダーが固定IDでレコードを挿入しているため、そのままだと
     * アプリからの次のINSERTでid重複エラーになる。シード後に
     * シーケンスを現在の最大idへ補正する。MySQLでは不要かつ
     * pg_get_serial_sequence自体が無いため、pgsql接続の時だけ実行する。
     */
    private function syncPostgresSequences()
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        $tables = [
            'users',
            'items',
            'categories',
            'item_categories',
            'purchases',
            'chats',
            'chat_messages',
            'chat_notifications',
            'ratings',
        ];

        foreach ($tables as $table) {
            DB::statement(
                "SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE((SELECT MAX(id) FROM {$table}), 1))"
            );
        }
    }
}
