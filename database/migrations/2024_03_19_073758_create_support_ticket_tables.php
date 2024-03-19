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
        Schema::create('ticket_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('color');
        });

        Schema::create('ticket_priorities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('color');
        });

        Schema::create('ticket_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('color');
        });

        Schema::create('ticket', function (Blueprint $table) {
            $table->increments('id');
            $table->string('subject');
            $table->longText('content');
            $table->integer('status_id')->unsigned();
            $table->integer('priority_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('agent_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->timestamps();
        });

        Schema::create('ticket_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->text('content');
            $table->integer('user_id')->unsigned();
            $table->integer('ticket_id')->unsigned();
            $table->timestamps();
        });

        Schema::create('ticket_audits', function (Blueprint $table) {
            $table->increments('id');
            $table->text('operation');
            $table->integer('user_id')->unsigned();
            $table->integer('ticket_id')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('ticket_audits');
        Schema::drop('ticket_comments');
        Schema::drop('ticket');
        Schema::drop('ticket_categories');
        Schema::drop('ticket_priorities');
        Schema::drop('ticket_statuses');
    }
};
