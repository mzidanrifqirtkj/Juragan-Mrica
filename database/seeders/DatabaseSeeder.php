<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            FarmerSeeder::class,
            UserSeeder::class,
            TransactionSeeder::class,
            SaleSeeder::class,
            SaleTransactionSeeder::class,
        ]);
    }
}
