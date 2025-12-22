<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 管理者ユーザー
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);

        // 一般ユーザー
        User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);

        User::create([
            'name' => '佐藤花子',
            'email' => 'sato@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);

        User::create([
            'name' => '鈴木一郎',
            'email' => 'suzuki@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);
    }
}