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
        // 1. Add Rates to Settings (Small: 1-4 pax, Medium: 5-10 pax, Large: 11+ pax)
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'island_hopping_small_rate')) {
                $table->decimal('island_hopping_small_rate', 8, 2)->default(1500.00); // 1-4 pax
                $table->decimal('island_hopping_medium_rate', 8, 2)->default(2500.00); // 5-10 pax
                $table->decimal('island_hopping_large_rate', 8, 2)->default(3500.00); // 11+ pax
            }
        });

        // 2. Add Toggle and Subtotal to Bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('is_island_hopping')->default(false)->after('stay_type');
            $table->decimal('subtotal_island_hopping', 10, 2)->default(0)->after('subtotal_parking_fee');
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
