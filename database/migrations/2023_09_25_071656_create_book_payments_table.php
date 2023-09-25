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
        Schema::create('book_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nsin_registration_id')->constrained();
            $table->integer('number_of_students');
            $table->integer('total');
            $table->string('receipt');
            $table->integer('ready');
            $table->integer('approved');
            $table->date('date_submitted');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_payments');
    }
};
