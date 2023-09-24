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
        Schema::create('surcharge_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surcharge_id')->constrained();
            $table->foreignId('course_id')->constrained();
            $table->decimal('fee', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surcharge_fees');
    }
};
