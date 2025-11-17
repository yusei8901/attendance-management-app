<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        User::create([
            'name' => '一般ユーザー１',
            'email' => 'user1@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);
        User::create([
            'name' => '一般ユーザー２',
            'email' => 'user2@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);
        User::create([
            'name' => '一般ユーザー３',
            'email' => 'user3@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);
        User::create([
            'name' => '一般ユーザー４',
            'email' => 'user4@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);
    }
}
