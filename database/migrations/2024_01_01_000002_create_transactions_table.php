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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique(); // TRX-YYYYMMDD-001
            $table->foreignId('farmer_id')->constrained('farmers')->onDelete('restrict');
            $table->decimal('weight_kg', 10, 2);
            $table->decimal('price_per_kg', 15, 2);
            $table->decimal('total_amount', 15, 2); // auto calculate: weight_kg * price_per_kg
            $table->string('payment_proof')->nullable(); // file path untuk foto bukti transfer
            $table->enum('payment_method', ['cash', 'transfer'])->default('cash');
            $table->enum('payment_status', ['pending', 'paid'])->default('pending');
            $table->dateTime('transaction_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            // Index untuk performa query
            $table->index('transaction_date');
            $table->index(['farmer_id', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
