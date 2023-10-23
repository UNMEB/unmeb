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
        Schema::rename('surchage_fee', 'surcharge_fees');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('surcharge_fees', 'surchage_fee');
    }
};
