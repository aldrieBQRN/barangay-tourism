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
        Schema::table('resorts', function (Blueprint $table) {
            // Default is true (since most are island resorts)
            $table->boolean('is_boat_ride_needed')->default(true)->after('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resorts', function (Blueprint $table) {
            //
        });
    }
};
