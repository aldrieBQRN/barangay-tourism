<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('resorts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('day_entrance', 10, 2)->default(0);
            $table->decimal('night_entrance', 10, 2)->default(0);
            $table->integer('boat_threshold')->default(6);
            $table->decimal('boat_fixed_price', 10, 2)->default(3000);
            $table->decimal('boat_per_head_price', 10, 2)->default(300);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resorts');
    }
};
