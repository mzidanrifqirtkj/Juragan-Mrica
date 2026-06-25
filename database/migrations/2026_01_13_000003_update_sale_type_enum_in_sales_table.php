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
        if (! Schema::hasColumn('sales', 'sale_type')) {
            return;
        }

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_sale_type_index');
            $table->dropColumn('sale_type');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->string('sale_type', 20)->default('warehouse');
            $table->index('sale_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('sales', 'sale_type')) {
            return;
        }

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_sale_type_index');
            $table->dropColumn('sale_type');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->string('sale_type', 20)->default('retail');
        });
    }
};
