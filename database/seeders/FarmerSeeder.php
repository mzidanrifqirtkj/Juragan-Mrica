<?php

namespace Database\Seeders;

use App\Models\Farmer;
use Illuminate\Database\Seeder;

class FarmerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $farmers = [
            [
                'name' => 'Pak Budi Santoso',
                'address' => 'Desa Sukamaju RT 01/02, Kec. Cilacap Selatan',
                'phone' => '081234567890',
                'notes' => 'Petani mrica langganan sejak 2020',
            ],
            [
                'name' => 'Bu Siti Aminah',
                'address' => 'Desa Karanganyar RT 03/01, Kec. Sampang',
                'phone' => '081234567891',
                'notes' => 'Mrica kualitas premium',
            ],
            [
                'name' => 'Pak Joko Widodo',
                'address' => 'Desa Margasari RT 02/03, Kec. Sidareja',
                'phone' => '081234567892',
                'notes' => null,
            ],
            [
                'name' => 'Pak Ahmad Dahlan',
                'address' => 'Desa Kedungreja RT 04/02, Kec. Kedungreja',
                'phone' => '081234567893',
                'notes' => 'Setor rutin setiap minggu',
            ],
            [
                'name' => 'Bu Kartini',
                'address' => 'Desa Gandrungmangu RT 01/04, Kec. Gandrungmangu',
                'phone' => '081234567894',
                'notes' => 'Mrica hitam dan putih',
            ],
            [
                'name' => 'Pak Suparman',
                'address' => 'Desa Kawunganten RT 02/01, Kec. Kawunganten',
                'phone' => '081234567895',
                'notes' => null,
            ],
            [
                'name' => 'Bu Rahayu',
                'address' => 'Desa Bantarsari RT 03/02, Kec. Bantarsari',
                'phone' => '081234567896',
                'notes' => 'Petani baru, mulai 2024',
            ],
        ];

        foreach ($farmers as $farmer) {
            Farmer::create($farmer);
        }
    }
}
