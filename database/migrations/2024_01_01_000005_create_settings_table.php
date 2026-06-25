<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, number, boolean, json
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
            [
                'key' => 'default_price_per_kg',
                'value' => '100000',
                'type' => 'number',
                'description' => 'Harga default per kg saat input transaksi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'stock_alert_threshold',
                'value' => '900',
                'type' => 'number',
                'description' => 'Alert ketika stok mencapai nilai ini (kg)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'low_stock_warning',
                'value' => '100',
                'type' => 'number',
                'description' => 'Warning ketika stok dibawah nilai ini (kg)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'target_stock',
                'value' => '1000',
                'type' => 'number',
                'description' => 'Target stok untuk penjualan bulk (kg)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
