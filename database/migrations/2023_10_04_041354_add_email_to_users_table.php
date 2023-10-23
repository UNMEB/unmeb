<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->after('username');
        });

        DB::table('users')->chunkById(100, function ($users) {
            foreach ($users as $user) {
                $username = Str::lower(Str::slug($user->username, ''));

                // Remove special characters from the username

                // Save
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'email' => $username . '@unmeb.go.ug'
                    ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
