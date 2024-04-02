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
        DB::statement('UPDATE students s
            INNER JOIN nsin_student_registrations nsr ON s.id = nsr.student_id
            INNER JOIN nsin_registrations nr ON nsr.nsin_registration_id = nr.id
            SET s.institution_id = nr.institution_id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('UPDATE students SET institution_id = NULL');
    }
};
