<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('farmer_id')
                ->nullable()
                ->after('role')
                ->constrained('farmers')
                ->nullOnDelete();

            $table->unique('farmer_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['farmer_id']);
            $table->dropConstrainedForeignId('farmer_id');
        });
    }
};
