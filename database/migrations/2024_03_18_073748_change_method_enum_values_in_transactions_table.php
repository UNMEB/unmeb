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
        Schema::table('your_table_name', function (Blueprint $table) {
            $table->dropColumn('method'); // Drop the existing column

            // Add a new column with updated enum values
            $table->enum('method', ['bank', 'agent_banking'])->after('previous_column_after_which_you_want_to_place_this_column');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('your_table_name', function (Blueprint $table) {
            $table->dropColumn('method'); // Drop the updated column

            // Add back the old column with previous enum values
            $table->enum('method', ['bank', 'mobile_money'])->after('previous_column_after_which_you_want_to_place_this_column');
        });
    }
};
