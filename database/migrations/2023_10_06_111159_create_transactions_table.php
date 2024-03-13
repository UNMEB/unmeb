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
            $table->enum('method', ['bank', 'agent_banking'])->nullable();
            $table->enum('type', ['credit', 'debit']);
            $table->string('status')->default('pending');
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
