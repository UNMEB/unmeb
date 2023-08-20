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
        Schema::create('tmp_exam_regs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained();
            $table->foreignId('registration_id')->constrained();
            $table->foreignId('course_id')->constrained();
            $table->string('year_of_study', 30);
            $table->foreignId('registration_period_id')->constrained();
            $table->string('trial', 15);
            $table->string('surname', 30);
            $table->string('firstname', 30);
            $table->string('othername', 30);
            $table->string('passport', 100);
            $table->string('NSIN', 30);
            $table->string('gender', 10);
            $table->string('district_name', 30);
            $table->date('dob');
            $table->integer('telephone');
            $table->string('email', 30);
            $table->string('course_codes', 200);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tmp_exam_regs');
    }
};
