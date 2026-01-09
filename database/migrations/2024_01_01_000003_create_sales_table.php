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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_code')->unique(); // SALE-YYYYMMDD-001
            $table->enum('sale_type', [ 'retail', 'bulk' ]); // retail=pasar, bulk=pengepul
            $table->string('buyer_name');
            $table->string('buyer_phone')->nullable();
            $table->decimal('weight_kg', 10, 2);
            $table->decimal('price_per_kg', 15, 2);
            $table->decimal('total_amount', 15, 2); // auto calculate
            $table->dateTime('sale_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            // Index untuk performa query
            $table->index('sale_date');
            $table->index('sale_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
