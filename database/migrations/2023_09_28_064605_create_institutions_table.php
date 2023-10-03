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
        Schema::create('institutions', function (Blueprint $table) {
            $table->id('id');
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('location');
            $table->string('type')->nullable();
            $table->string('code', 20);
            $table->string('phone_no', 20);
            $table->string('box_no', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
