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
        Schema::table('settings', function (Blueprint $table) {
            // We add the columns only if they don't exist yet
            if (!Schema::hasColumn('settings', 'barangay_name')) {
                $table->string('barangay_name')->default('Barangay Tourism');
            }
            
            if (!Schema::hasColumn('settings', 'eco_fee')) {
                $table->decimal('eco_fee', 8, 2)->default(20.00);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            //
        });
    }
};
