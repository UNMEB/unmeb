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
            /// Add a new 'status' column with an ENUM type
            Schema::table('transactions', function (Blueprint $table) {
                $table->enum('status', ['approved', 'rejected', 'pending'])->default('pending');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Drop status column
            $table->dropColumn('status');
        });
    }
};
