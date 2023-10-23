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
        Schema::rename('registration_periodnsin', 'nsin_registration_periods');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
