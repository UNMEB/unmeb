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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 15, 2);
            $table->enum('method', ['bank', 'mobile_money'])->nullable();
            $table->enum('type', ['credit', 'debit']);
            $table->integer('is_approved')->default(0); // 0 = pending, 1 = approved
            $table->foreignId('account_id');
            $table->foreignId('approved_by')
                ->nullable();
            $table->foreignId('institution_id');
            $table->string('deposited_by')->nullable();
            $table->string('remote_transaction_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
