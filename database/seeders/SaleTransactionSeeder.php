<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SaleTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder is optional - it demonstrates how to manually link
     * existing sales with transactions. In production, this linking
     * happens automatically when creating a sale via the Filament form.
     */
    public function run(): void
    {
        // This seeder is intentionally empty because:
        // 1. sale_transactions is populated automatically when creating sales
        // 2. Existing data before this migration won't have the linking
        // 3. If you need to backfill, you would run a custom command

        // Example of how to manually link (for reference):
        // $sale = Sale::first();
        // $transactions = Transaction::unsold()->get();
        //
        // foreach ($transactions as $transaction) {
        //     $sale->transactions()->attach($transaction->id, [
        //         'weight_kg' => $transaction->weight_kg,
        //     ]);
        //     $transaction->update(['is_sold' => true]);
        // }
    }
}
