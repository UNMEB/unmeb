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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained();
            $table->foreignId('course_id')->constrained();
            $table->string('receipt', 50);
            $table->integer('amount');
            $table->string('year_of_study', 20);
            $table->string('registration_period_id', 20);
            $table->integer('completed')->default(0);
            $table->integer('verify')->default(0);
            $table->integer('approved')->default(0);
            $table->foreignId('surcharge_id')->constrained();
            $table->timestamp('date_time')->useCurrent()->onUpdateCurrent();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
