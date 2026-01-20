<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\Farmer;
use App\Models\User;
use App\Services\InventoryService;
use App\Models\InventoryLog;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $farmers = Farmer::all();
        $kasir = User::where('role', 'kasir')->first();

        // Generate 25 transactions over the last 30 days
        $transactions = [];

        for ($i = 0; $i < 25; $i++) {
            $farmer = $farmers->random();
            $date = Carbon::now()->subDays(rand(0, 30));
            $weight = rand(5, 50) + (rand(0, 99) / 100); // 5.00 - 50.99 kg
            $pricePerKg = rand(95000, 110000); // Harga beli dari petani

            $transactions[] = [
                'farmer' => $farmer,
                'date' => $date,
                'weight' => round($weight, 2),
                'price' => $pricePerKg,
                'payment_method' => rand(0, 1) ? 'cash' : 'transfer',
            ];
        }

        // Sort by date
        usort($transactions, function ($a, $b) {
            return $a[ 'date' ]->timestamp - $b[ 'date' ]->timestamp;
        });

        // Create transactions
        foreach ($transactions as $data) {
            $transaction = Transaction::create([
                'farmer_id' => $data[ 'farmer' ]->id,
                'weight_kg' => $data[ 'weight' ],
                'price_per_kg' => $data[ 'price' ],
                'total_amount' => $data[ 'weight' ] * $data[ 'price' ],
                'payment_method' => $data[ 'payment_method' ],
                'payment_status' => rand(0, 4) > 0 ? 'paid' : 'pending', // 80% paid
                'is_sold' => false, // All new transactions start as unsold
                'transaction_date' => $data[ 'date' ],
                'notes' => rand(0, 1) ? 'Lada kering kualitas ' . [ 'A', 'B', 'C' ][ rand(0, 2) ] : null,
                'created_by' => $kasir->id,
            ]);

            // Add stock to inventory
            InventoryService::addStock($transaction);
        }
    }
}
