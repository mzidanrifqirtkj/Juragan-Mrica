<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\User;
use App\Services\InventoryService;
use App\Models\InventoryLog;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kasir = User::where('role', 'kasir')->first();

        // Buyers data
        $retailBuyers = [
            [ 'name' => 'Toko Bumbu Ibu Ani', 'phone' => '082111222333' ],
            [ 'name' => 'Warung Bu Sari', 'phone' => '082111222334' ],
            [ 'name' => 'Pasar Manis Stall 12', 'phone' => '082111222335' ],
            [ 'name' => 'Toko Rempah Pak Darmo', 'phone' => '082111222336' ],
        ];

        $bulkBuyers = [
            [ 'name' => 'CV. Rempah Nusantara', 'phone' => '081999888777' ],
            [ 'name' => 'PT. Bumbu Sejahtera', 'phone' => '081999888776' ],
            [ 'name' => 'Pengepul Pak Haji Rahman', 'phone' => '081999888775' ],
        ];

        $sales = [];

        // Generate 10 retail sales
        for ($i = 0; $i < 10; $i++) {
            $buyer = $retailBuyers[ array_rand($retailBuyers) ];
            $date = Carbon::now()->subDays(rand(0, 25));
            $weight = rand(2, 20) + (rand(0, 99) / 100); // 2.00 - 20.99 kg
            $pricePerKg = rand(115000, 130000); // Harga jual retail

            $sales[] = [
                'type' => 'retail',
                'buyer' => $buyer,
                'date' => $date,
                'weight' => round($weight, 2),
                'price' => $pricePerKg,
            ];
        }

        // Generate 3 bulk sales
        for ($i = 0; $i < 3; $i++) {
            $buyer = $bulkBuyers[ array_rand($bulkBuyers) ];
            $date = Carbon::now()->subDays(rand(5, 20));
            $weight = rand(100, 300) + (rand(0, 99) / 100); // 100.00 - 300.99 kg
            $pricePerKg = rand(110000, 120000); // Harga jual bulk (lebih murah)

            $sales[] = [
                'type' => 'bulk',
                'buyer' => $buyer,
                'date' => $date,
                'weight' => round($weight, 2),
                'price' => $pricePerKg,
            ];
        }

        // Sort by date
        usort($sales, function ($a, $b) {
            return $a[ 'date' ]->timestamp - $b[ 'date' ]->timestamp;
        });

        // Create sales
        foreach ($sales as $data) {
            $currentStock = InventoryService::getCurrentStock();

            // Only create sale if we have enough stock
            if ($currentStock >= $data[ 'weight' ]) {
                Sale::create([
                    'sale_type' => $data[ 'type' ],
                    'buyer_name' => $data[ 'buyer' ][ 'name' ],
                    'buyer_phone' => $data[ 'buyer' ][ 'phone' ],
                    'weight_kg' => $data[ 'weight' ],
                    'price_per_kg' => $data[ 'price' ],
                    'total_amount' => $data[ 'weight' ] * $data[ 'price' ],
                    'sale_date' => $data[ 'date' ],
                    'notes' => $data[ 'type' ] === 'bulk' ? 'Penjualan partai besar' : null,
                    'created_by' => $kasir->id,
                ]);
            }
        }
    }
}
