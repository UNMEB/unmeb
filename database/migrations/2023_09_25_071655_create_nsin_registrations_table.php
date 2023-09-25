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
        Schema::create('nsin_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained();
            $table->foreignId('course_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->string('receipt');
            $table->string('month');
            $table->foreignId('year_id')->constrained();
            $table->integer('completed')->default(0);
            $table->integer('approved')->default(0);
            $table->integer('books')->default(0);
            $table->integer('nsin')->default(0);
            $table->integer('nsin_verify')->default(0);
            $table->integer('old')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nsin_registrations');
    }
};