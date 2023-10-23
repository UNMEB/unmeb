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
        Schema::table('nsin_student_registrations', function (Blueprint $table) {
            $table->renameColumn('nsinregistration_id', 'nsin_registration_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nsin_student_registrations', function (Blueprint $table) {
            $table->renameColumn('nsin_registration_id', 'nsinregistration_id');
        });
    }
};
