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
        Schema::table('bookings', function (Blueprint $table) {
            // These columns will store the split amounts
            $table->decimal('subtotal_eco_fee', 10, 2)->default(0)->after('status');
            $table->decimal('subtotal_boat_fee', 10, 2)->default(0)->after('subtotal_eco_fee');
            $table->decimal('subtotal_accommodation_fee', 10, 2)->default(0)->after('subtotal_boat_fee');
            $table->decimal('subtotal_parking_fee', 10, 2)->default(0)->after('subtotal_accommodation_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            //
        });
    }
};
