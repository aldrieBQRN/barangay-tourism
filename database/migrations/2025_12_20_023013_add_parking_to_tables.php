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
        // 1. Add Rates to Settings
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'parking_motor_rate')) {
                $table->decimal('parking_motor_rate', 8, 2)->default(50.00);
                $table->decimal('parking_van_rate', 8, 2)->default(150.00); // Covers Cars/Vans
            }
        });

        // 2. Add Vehicle Counts to Bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->integer('motor_count')->default(0)->after('remarks');
            $table->integer('van_count')->default(0)->after('motor_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }
};
