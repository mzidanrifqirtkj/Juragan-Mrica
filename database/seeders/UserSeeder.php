<?php

namespace Database\Seeders;

use App\Models\Farmer;
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
        $petaniProfile = Farmer::query()->first();

        // Create Owner
        User::create([
            'name' => 'Owner Warung',
            'username' => 'owner',
            'email' => 'owner@warungsetor.test',
            'password' => Hash::make('12345678'),
            'role' => 'owner',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create Admin
        User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@warungsetor.test',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create Petani
        User::create([
            'name' => 'Petani 1',
            'username' => 'petani1',
            'email' => 'petani@warungsetor.test',
            'password' => Hash::make('12345678'),
            'role' => 'petani',
            'farmer_id' => $petaniProfile?->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
