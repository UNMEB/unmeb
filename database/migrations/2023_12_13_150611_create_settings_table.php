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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        DB::table('settings')->insert([
            ['key' => 'email.smtp_host', 'value' => 'mail.unmeb.go.ug'],
            ['key' => 'email.smtp_port', 'value' => '2525'],
            ['key' => 'email.smtp_username', 'value' => 'mail@unmeb.go.ug'],
            ['key' => 'email.smtp_password', 'value' => 'qwerty123'],
            ['key' => 'fees.nsin_registration', 'value' => 0],
            ['key' => 'fess.paper_registration', 'value' => 0],
            ['key' => 'finance.minimum_balance', 'value' => 0],
            ['key' => 'signature.finance_signature', 'value' => ''],
            ['key' => 'signature.registra_signature', 'value' => ''],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
