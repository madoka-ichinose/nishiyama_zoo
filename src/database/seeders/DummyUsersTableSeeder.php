<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DummyUsersTableSeeder extends Seeder
{
    public function run()
    {
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name'              => "テストユーザー{$i}",
                'email'             => "test{$i}@example.com",
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),   
                'is_admin'          => false,   
            ]);
        }
    }
}
