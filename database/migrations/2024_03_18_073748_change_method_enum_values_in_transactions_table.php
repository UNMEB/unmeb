<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('method');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('method', ['bank', 'agent_banking'])->default('bank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('method'); // Drop the updated column

            // Add back the old column with previous enum values
            $table->enum('method', ['bank', 'mobile_money']);
        });
    }
};
