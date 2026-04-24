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
        $petani = User::where('role', 'petani')->first();

        // Buyers data for different sale types
        $warehouseBuyers = [
            [ 'name' => 'CV. Rempah Nusantara', 'phone' => '081999888777' ],
            [ 'name' => 'PT. Bumbu Sejahtera', 'phone' => '081999888776' ],
            [ 'name' => 'Gudang Lada Pak Haji', 'phone' => '081999888775' ],
        ];

        $marketBuyers = [
            [ 'name' => 'Pasar Manis Stall 12', 'phone' => '082111222335' ],
            [ 'name' => 'Pasar Induk Stand 5', 'phone' => '082111222336' ],
            [ 'name' => 'Pasar Rempah Blok C', 'phone' => '082111222337' ],
        ];

        $retailBuyers = [
            [ 'name' => 'Toko Bumbu Ibu Ani', 'phone' => '082111222333' ],
            [ 'name' => 'Warung Bu Sari', 'phone' => '082111222334' ],
            [ 'name' => 'Toko Rempah Pak Darmo', 'phone' => '082111222338' ],
        ];

        $sales = [];

        // Generate 2 warehouse sales (large quantities - but limited by stock)
        for ($i = 0; $i < 2; $i++) {
            $buyer = $warehouseBuyers[ array_rand($warehouseBuyers) ];
            $date = Carbon::now()->subDays(rand(5, 20));
            $weight = rand(50, 100) + (rand(0, 99) / 100); // 50.00 - 100.99 kg
            $pricePerKg = rand(100000, 110000); // Harga jual ke gudang

            $sales[] = [
                'type' => 'warehouse',
                'buyer' => $buyer,
                'date' => $date,
                'weight' => round($weight, 2),
                'price' => $pricePerKg,
            ];
        }

        // Generate 3 market sales (medium quantities)
        for ($i = 0; $i < 3; $i++) {
            $buyer = $marketBuyers[ array_rand($marketBuyers) ];
            $date = Carbon::now()->subDays(rand(0, 25));
            $weight = rand(10, 30) + (rand(0, 99) / 100); // 10.00 - 30.99 kg
            $pricePerKg = rand(115000, 125000); // Harga jual ke pasar

            $sales[] = [
                'type' => 'market',
                'buyer' => $buyer,
                'date' => $date,
                'weight' => round($weight, 2),
                'price' => $pricePerKg,
            ];
        }

        // Generate 5 retail sales (small quantities)
        for ($i = 0; $i < 5; $i++) {
            $buyer = $retailBuyers[ array_rand($retailBuyers) ];
            $date = Carbon::now()->subDays(rand(0, 25));
            $weight = rand(1, 5) + (rand(0, 99) / 100); // 1.00 - 5.99 kg
            $pricePerKg = rand(125000, 140000); // Harga jual eceran (tertinggi)

            $sales[] = [
                'type' => 'retail',
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

        // Create sales - only if we have enough stock
        foreach ($sales as $data) {
            $currentStock = InventoryService::getCurrentStock();

            // Only create sale if we have enough stock
            if ($currentStock >= $data[ 'weight' ]) {
                $sale = Sale::create([
                    'sale_type' => $data[ 'type' ],
                    'buyer_name' => $data[ 'buyer' ][ 'name' ],
                    'buyer_phone' => $data[ 'buyer' ][ 'phone' ],
                    'weight_kg' => $data[ 'weight' ],
                    'price_per_kg' => $data[ 'price' ],
                    'total_amount' => $data[ 'weight' ] * $data[ 'price' ],
                    'sale_date' => $data[ 'date' ],
                    'notes' => $data[ 'type' ] === 'warehouse' ? 'Penjualan ke gudang besar' : null,
                    'created_by' => $petani->id,
                ]);

                // Reduce stock via InventoryService
                try {
                    InventoryService::reduceStock($sale);
                } catch (\Exception $e) {
                    // Skip if stock is insufficient - just log the sale without reducing
                }
            }
        }
    }
}

