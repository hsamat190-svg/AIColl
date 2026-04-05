<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Demo admin for local / Open Server (Breeze login uses email field).
     * Email: admin@aicoll.test — Password: 123456
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@aicoll.test'],
            [
                'name' => 'admin',
                'password' => '123456',
                'email_verified_at' => now(),
            ],
        );
    }
}
