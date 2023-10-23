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
        Schema::table('registration_periods', function (Blueprint $table) {
            $table->renameColumn('registration_period_id', 'id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registration_periods', function (Blueprint $table) {
            $table->renameColumn('id', 'registration_period_id');
        });
    }
};
