<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'user_id' => 'USRADMIN001',
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create Regular User (Active)
        User::create([
            'user_id' => 'USRUSER001',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
            'last_login_at' => now()->subDays(1),
        ]);

        // Create Inactive User
        User::create([
            'user_id' => 'USRUSER002',
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'is_active' => false,
            'email_verified_at' => now(),
            'last_login_at' => now()->subWeeks(2),
        ]);

        // Create Unverified User
        User::create([
            'user_id' => 'USRUSER003',
            'name' => 'Bob Wilson',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => null, // Belum verify email
        ]);

        // Create Multiple Users using Factory (jika ada factory)
        // User::factory(10)->create();
    }
}
