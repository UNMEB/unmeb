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
        Schema::create('continuous_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_period_id');
            $table->foreignId('institution_id');
            $table->foreignId('course_id');
            $table->foreignId('paper_id');
            $table->foreignId('student_id');
            $table->string('paper_type'); // 'theory' or 'practical'
            $table->json('theory_marks')->nullable(); // Stores JSON for theory marks
            $table->json('practical_marks')->nullable(); // Stores JSON for practical marks
            $table->float('total_marks'); // Calculated total marks
            $table->integer('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('continuous_assessment');
    }
};
