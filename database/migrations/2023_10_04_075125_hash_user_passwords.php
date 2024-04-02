<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add a temporary column to hold the new hashed passwords
            $table->string('new_password')->nullable()->after('password');
            // Add a column to store the old unhashed passwords
            $table->string('old_password')->nullable()->after('new_password');
        });

        // Update the new_password column with hashed passwords or default password
        DB::table('users')->chunkById(100, function ($users) {
            foreach ($users as $user) {
                // Replace short passwords with a default hashed password
                $hashedPassword = Hash::make('unmeb@2023');

                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                            'new_password' => $hashedPassword,
                            'old_password' => $user->password
                        ]);
            }
        });

        // Remove the old password column and rename the new_password column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password');
            $table->renameColumn('new_password', 'password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password');
            $table->renameColumn('old_password', 'password');
        });
    }
};
