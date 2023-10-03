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
        Schema::create('exam_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_registration_period_id');
            $table->foreignId('institution_id');
            $table->foreignId('course_id');
            $table->foreignId('student_id');
            $table->integer('is_active')->default(0);
            $table->integer('number_of_papers')->default(0);
            $table->string('papers_registerd')->nullable();
            $table->enum('trial', ['First', 'Second', 'Third'])
                ->nullable();
            $table->string('study_period');
            $table->integer('is_completed')->default(0);
            $table->integer('is_approved')->default(0);
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
        Schema::dropIfExists('exam_registrations');
    }
};
