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
            $table->string('caretaker_name')->nullable()->after('description');
            $table->string('contact_number')->nullable()->after('caretaker_name');
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
