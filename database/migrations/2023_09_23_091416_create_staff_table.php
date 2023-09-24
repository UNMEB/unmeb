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
            $table->id();
            $table->foreignId('institution_id')->constrained();
            $table->foreignId('district_id')
                ->nullable()
                ->constrained();
            $table->string('name');
            $table->string('designation');
            $table->string('status');
            $table->string('education');
            $table->string('qualification');
            $table->string('council');
            $table->string('reg_no');
            $table->string('reg_date');
            $table->string('lic_exp');
            $table->string('experience');
            $table->string('telephone');
            $table->string('email');
            $table->string('bank');
            $table->string('branch');
            $table->string('acc_no');
            $table->string('acc_name');
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
