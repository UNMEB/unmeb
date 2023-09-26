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
        Schema::create('registration_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained();
            $table->foreignId('paper_id')->constrained();
            $table->foreignId('registration_id')->constrained();
            $table->foreignId('registration_period_id')->constrained();
            $table->foreignId('institution_id')->constrained();
            $table->string('atempt'); // Packing List, Second Attempt, Third Attempt
            $table->string('semester');
            $table->string('year');
            $table->integer('total');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_reports');
    }
};
