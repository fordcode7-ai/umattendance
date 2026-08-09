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
        Schema::create('attendance_users', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('student_id')->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->string('role');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('year_level')->nullable();
            $table->string('course')->nullable();
            $table->string('contact')->nullable();
            $table->string('sport')->nullable();
            $table->string('avatar')->nullable();
            $table->string('title')->nullable();
            $table->json('sports_handled')->nullable();
            $table->timestamps();
            $table->index(['role', 'sport']);
            $table->index('student_id');
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->date('date');
            $table->string('status');
            $table->string('time')->nullable();
            $table->text('note')->nullable();
            $table->string('sport');
            $table->timestamps();
            $table->index(['student_id', 'date']);
            $table->index(['sport', 'date']);
        });

        Schema::create('attendance_excuses', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('student_id');
            $table->date('date');
            $table->text('reason');
            $table->json('attachments')->nullable();
            $table->string('sport');
            $table->boolean('approved')->default(false);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'date']);
            $table->index(['sport', 'approved']);
        });

        Schema::create('attendance_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('sport');
            $table->date('date');
            $table->string('time')->nullable();
            $table->string('venue')->nullable();
            $table->timestamps();
            $table->unique(['sport', 'date']);
            $table->index(['sport', 'date']);
        });

        Schema::create('attendance_announcements', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('sport');
            $table->string('title');
            $table->text('body');
            $table->timestamps();
            $table->index(['sport', 'created_at']);
        });

        Schema::create('attendance_system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key');
            $table->longText('setting_value')->nullable();
            $table->timestamps();
            $table->unique('setting_key');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_system_settings');
        Schema::dropIfExists('attendance_announcements');
        Schema::dropIfExists('attendance_schedules');
        Schema::dropIfExists('attendance_excuses');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('attendance_users');
    }
};
