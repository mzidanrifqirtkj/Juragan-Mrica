<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('reference_type', [ 'purchase', 'sale' ]); // purchase dari transaction, sale dari sales
            $table->unsignedBigInteger('reference_id'); // ID dari transaction atau sale
            $table->enum('type', [ 'in', 'out' ]); // in = masuk, out = keluar
            $table->decimal('weight_kg', 10, 2);
            $table->decimal('current_stock', 10, 2); // stok setelah transaksi ini
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index untuk performa query
            $table->index([ 'reference_type', 'reference_id' ]);
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};
