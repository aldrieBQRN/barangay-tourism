<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // If the table doesn't exist, we create it. If it does, we modify it.
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('barangay_name')->default('Barangay Tourism');
                $table->decimal('eco_fee', 8, 2)->default(20.00);
                $table->timestamps();
            });
            
            // Insert default immediately
            DB::table('settings')->insert([
                'barangay_name' => 'Barangay Tourism',
                'eco_fee' => 20.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
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
