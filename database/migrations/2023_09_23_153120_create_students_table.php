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
            $table->string('nsin')
                ->nullable()
                ->unique();
            $table->string('surname');
            $table->string('firstname');
            $table->string('othername');
            $table->enum('gender', ['MALE', 'FEMALE', 'OTHER']);
            $table->date('dob')->nullable();
            $table->foreignId('district_id')->constrained();
            $table->string('country')->nullable();
            $table->string('address')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('photo')->nullable();
            $table->string('national_id')->nullable();
            $table->integer('old_student')->default(0);

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
