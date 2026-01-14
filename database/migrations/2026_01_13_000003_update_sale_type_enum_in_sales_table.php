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
        Schema::table('sales', function (Blueprint $table) {
            // Update enum untuk sale_type
            $table->dropColumn('sale_type');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->enum('sale_type', [ 'warehouse', 'market', 'retail' ])->default('warehouse')->after('sale_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('sale_type');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->enum('sale_type', [ 'retail', 'bulk' ])->after('sale_code');
        });
    }
};
