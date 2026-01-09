<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Owner
        User::create([
            'name' => 'Owner Warung',
            'email' => 'owner@warungsetor.test',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create Admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@warungsetor.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create Kasir
        User::create([
            'name' => 'Kasir 1',
            'email' => 'kasir@warungsetor.test',
            'password' => Hash::make('password'),
            'role' => 'kasir',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
