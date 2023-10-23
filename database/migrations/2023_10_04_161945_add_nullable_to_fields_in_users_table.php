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
        Schema::table('users', function (Blueprint $table) {
            // Modify user_id column to be nullable
            $table->integer('user_id')->nullable()->change();

            // Modify institution_id column to be nullable
            $table->integer('institution_id')->nullable()->change();

            // Modify username column to be nullable
            $table->string('username')->nullable()->change();

            // Modify role column to be nullable
            $table->string('role')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
        });
    }
};
