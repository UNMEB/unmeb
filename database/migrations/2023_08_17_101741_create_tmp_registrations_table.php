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
        Schema::create('tmp_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_paper_id');
            $table->unsignedBigInteger('registration_id');
            $table->unsignedBigInteger('registration_period_id');
            $table->unsignedBigInteger('institution_id');
            $table->integer('no');
            $table->enum("level", ["first", "second", "third"])->default("first"); // New column: level
            $table->integer('sub_level'); // New column: sub_level
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tmp_registrations');
    }
};
