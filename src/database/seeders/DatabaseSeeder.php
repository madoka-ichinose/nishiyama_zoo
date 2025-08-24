<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
   public function run(): void
{
    // 他の Seeder のあとでOK
    $this->call([
        AnimalsTableSeeder::class,
    ]);

    // 管理者アカウント作成
        User::updateOrCreate(
            ['email' => 'admin@example.com'], // 重複防止
            [
                'name' => '管理者ユーザー',
                'email_verified_at' => now(),
                'password' => Hash::make('password'), // 実運用では必ず変更
                'is_admin' => true,
            ]
        );
}
}
