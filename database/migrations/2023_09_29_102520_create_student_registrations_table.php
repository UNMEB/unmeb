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
        Schema::create('student_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id');
            $table->foreignId('course_id');
            $table->foreignId('student_id');
            $table->string('month')->nullable();
            $table->foreignId('year_id');
            $table->integer('is_completed')->default(0);
            $table->integer('is_approved')->default(0);
            $table->integer('is_book')->default(0);
            $table->integer('is_verified')->default(0);
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_registrations');
    }
};
