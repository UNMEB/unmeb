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
        Schema::create('biometric_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id');
            $table->foreignId('course_id');
            $table->foreignId('paper_id');
            $table->dateTime('verification_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_access_logs');
    }
};
