<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@minimarket.test'],
            [
                'name' => 'Administrator',
                'email_verified_at' => now(),
                'password' => 'password',
                'role' => 'admin',
            ],
        );

        User::updateOrCreate(
            ['email' => 'kasir@minimarket.test'],
            [
                'name' => 'Kasir 1',
                'email_verified_at' => now(),
                'password' => 'password',
                'role' => 'kasir',
            ],
        );
    }
}
