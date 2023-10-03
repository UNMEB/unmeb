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
            $table->string('surname', 50)->nullable();
            $table->string('firstname', 50)->nullable();
            $table->string('othername', 50)->nullable();
            $table->string('dob')->nullable();
            $table->foreignId('district_id')
            ->nullable()
                ->constrained();
            $table->string('gender')->nullable();
            $table->string('country', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('nsin')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->integer('old')
            ->nullable()
            ->default(0);
            $table->dateTime('registration_date')->nullable();
            $table->text('passport')->nullable();
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
