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
        Schema::create('exam_registration_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_registration_period_id')->constrained();
            $table->foreignId('exam_registration_id')->constrained();
            $table->foreignId('institution_id')->constrained();
            $table->foreignId('course_paper_id')->constrained('course_paper');
            $table->integer('student_count');
            $table->integer('semester');
            $table->integer('year');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_registration_reports');
    }
};
