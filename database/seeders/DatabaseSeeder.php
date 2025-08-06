<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'first_name' => 'Admin',
            'last_name' => '1',
            'email' => 'admin@admin.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'first_name' => 'User',
            'last_name' => '2',
            'email' => 'user@user.com',
            'password' => Hash::make('12345678'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);
        User::create([
            'first_name' => 'Mr.',
            'last_name' => 'Engineer',
            'email' => 'engineer@engineer.com',
            'password' => Hash::make('12345678'),
            'role' => 'engineer',
            'email_verified_at' => now(),
        ]);

    }
}
