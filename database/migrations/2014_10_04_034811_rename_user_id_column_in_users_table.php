<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop primary key from user_id
        Schema::table('users', function (Blueprint $table) {
            $table->dropPrimary('user_id');
            $table->integer('user_id')->unsigned()->change();
        });

        // Create a new id table and set as primary key
        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('id')
            ->unsigned();
        });

        // Copy data from user_id to id column
        DB::table('users')->update([
            'id' => DB::raw('user_id')
        ]);

        // Set the primary key to id table
        Schema::table('users', function (Blueprint $table) {
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the id column
            $table->dropPrimary('id');

            // Set the primary key to user_id
            $table->primary('user_id');
        });
    }
};
