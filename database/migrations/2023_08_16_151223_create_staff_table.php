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
        Schema::create('staff', function (Blueprint $table) {
            $table->id('staff_id');
            $table->foreignId('institution_id')->constrained();
            $table->string('staff_name', 30);
            $table->string('designation', 30);
            $table->string('status', 15);
            $table->string('education', 30);
            $table->string('qualification', 30);
            $table->string('council', 30);
            $table->integer('reg_no');
            $table->date('reg_date');
            $table->date('license_expiry');
            $table->integer('experience');
            $table->bigInteger('telephone');
            $table->string('email', 20);
            $table->string('bank', 30);
            $table->string('branch', 20);
            $table->bigInteger('acc_no');
            $table->string('acc_name', 30);
            $table->string('receipt', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
