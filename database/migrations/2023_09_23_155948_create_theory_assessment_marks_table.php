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
        Schema::create('theory_assessment_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->foreignId('course_paper_id')->constrained();
            $table->decimal('assignment_1', 5, 2);
            $table->decimal('assignment_2', 5, 2);
            $table->decimal('total_assignment_mark', 5, 2);
            $table->decimal('test_1', 5, 2);
            $table->decimal('test_2', 5, 2);
            $table->decimal('total_test_mark', 5, 2);
            $table->decimal('total_mark', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('theory_assessment_marks');
    }
};
