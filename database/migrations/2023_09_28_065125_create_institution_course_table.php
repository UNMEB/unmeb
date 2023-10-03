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
        Schema::create('institution_course', function (Blueprint $table) {
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('course_id');
            $table->timestamps();

            $table->primary(['institution_id', 'course_id']);
            $table->foreign('institution_id')->references('id')->on('institutions');
            $table->foreign('course_id')->references('id')->on('courses');

            $table->integer('flag')->default(0);

            // Add indices for the foreign keys
            $table->index('institution_id');
            $table->index('course_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institution_course');
    }
};
