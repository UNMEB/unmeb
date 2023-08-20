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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('surname', 30);
            $table->string('firstname', 30);
            $table->string('othername', 30);
            $table->string('passport', 500);
            $table->string('gender', 6);
            $table->date('dob');
            $table->unsignedBigInteger('district_id');
            $table->string('country', 50)->default('NULL');
            $table->string('location', 50)->default('NULL');
            $table->string('NSIN', 50);
            $table->string('telephone', 15);
            $table->string('email', 30);
            $table->unsignedInteger('old')->default(0);
            $table->dateTime('date_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
