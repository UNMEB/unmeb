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
        // Clear existing data before seeding
        DB::table('ticket_statuses')->truncate();
        DB::table('ticket_priorities')->truncate();
        DB::table('ticket_categories')->truncate();

        // Seed default ticket statuses
        DB::table('ticket_statuses')->insert([
            ['name' => 'Pending', 'color' => '#ff9900'], // Vibrant orange
            ['name' => 'Open', 'color' => '#ff0000'], // Vibrant red
            ['name' => 'Closed', 'color' => '#00cc00'], // Vibrant green
        ]);

        // Seed default ticket priorities
        DB::table('ticket_priorities')->insert([
            ['name' => 'Low', 'color' => '#00cc00'], // Vibrant green
            ['name' => 'Medium', 'color' => '#ff9900'], // Vibrant orange
            ['name' => 'High', 'color' => '#ff0000'], // Vibrant red
            ['name' => 'Critical', 'color' => '#ff0000'], // Vibrant red
        ]);

        // Seed default ticket categories
        DB::table('ticket_categories')->insert([
            ['name' => 'Finance - Deposits/Payments', 'color' => '#ff6600'], // Vibrant orange
            ['name' => 'Exam Registrations', 'color' => '#ff0000'], // Vibrant red
            ['name' => 'NSIN Registrations', 'color' => '#996633'], // Brown
            ['name' => 'Account Issues', 'color' => '#996633'], // Brown
            ['name' => 'Complaints', 'color' => '#ff0000'], // Vibrant red
            ['name' => 'Inquiries', 'color' => '#0099cc'], // Vibrant blue
            ['name' => 'Feedback', 'color' => '#00cc00'], // Vibrant green
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete inserted records
        DB::table('ticket_statuses')->whereIn('name', ['Pending', 'Open', 'Closed'])->delete();
        DB::table('ticket_priorities')->whereIn('name', ['Low', 'Medium', 'High', 'Critical'])->delete();
        DB::table('ticket_categories')->whereIn('name', ['Finance - Deposits/Payments', 'Exam Registrations', 'NSIN Registrations', 'Account Issues', 'Complaints', 'Inquiries', 'Feedback'])->delete();
    }
};
