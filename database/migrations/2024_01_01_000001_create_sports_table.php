<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sports', function (Blueprint $table) {
            $table->increments('sport_id');
            $table->string('sport_name', 50)->unique();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->enum('role', ['student', 'coach', 'super_admin']);
            $table->unsignedInteger('sport_id')->nullable();
            $table->string('student_id', 20)->nullable()->unique();
            $table->string('email', 100)->nullable()->unique();
            $table->string('password', 255);
            $table->string('first_name', 50);
            $table->string('middle_initial', 5)->nullable();
            $table->string('last_name', 50);
            $table->string('year_level', 20)->nullable();
            $table->string('course', 100)->nullable();
            $table->string('contact_number', 20)->nullable();
            $table->string('profile_picture', 255)->nullable()->default('default.png');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at')->nullable();

            $table->foreign('sport_id')->references('sport_id')->on('sports')
                ->onDelete('set null')->onUpdate('cascade');
            $table->index(['role', 'sport_id']);
        });

        Schema::create('attendance', function (Blueprint $table) {
            $table->increments('attendance_id');
            $table->unsignedInteger('user_id');
            $table->date('attendance_date');
            $table->enum('status', ['present', 'late', 'absent', 'excused', 'special_training']);
            $table->time('time_recorded')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('user_id')->references('user_id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->unique(['user_id', 'attendance_date'], 'uq_user_date');
            $table->index('attendance_date');
        });

        Schema::create('excuses', function (Blueprint $table) {
            $table->increments('excuse_id');
            $table->unsignedInteger('user_id');
            $table->date('excuse_date');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('user_id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('reviewed_by')->references('user_id')->on('users')
                ->onDelete('set null')->onUpdate('cascade');
            $table->unique(['user_id', 'excuse_date'], 'uq_user_excuse_date');
            $table->index('status');
        });

        Schema::create('excuse_attachments', function (Blueprint $table) {
            $table->increments('attachment_id');
            $table->unsignedInteger('excuse_id');
            $table->string('file_path', 255);
            $table->timestamp('uploaded_at')->useCurrent();

            $table->foreign('excuse_id')->references('excuse_id')->on('excuses')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::create('special_training_requests', function (Blueprint $table) {
            $table->increments('request_id');
            $table->unsignedInteger('user_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('user_id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('reviewed_by')->references('user_id')->on('users')
                ->onDelete('set null')->onUpdate('cascade');
            $table->index('status');
        });
        DB::statement('ALTER TABLE special_training_requests ADD CONSTRAINT chk_str_dates CHECK (end_date >= start_date)');

        Schema::create('special_training_attachments', function (Blueprint $table) {
            $table->increments('attachment_id');
            $table->unsignedInteger('request_id');
            $table->string('file_path', 255);
            $table->timestamp('uploaded_at')->useCurrent();

            $table->foreign('request_id')->references('request_id')->on('special_training_requests')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::create('schedules', function (Blueprint $table) {
            $table->increments('schedule_id');
            $table->unsignedInteger('sport_id');
            $table->date('schedule_date');
            $table->string('venue', 150);
            $table->time('time_start');
            $table->time('time_end')->nullable();
            $table->unsignedInteger('created_by');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('sport_id')->references('sport_id')->on('sports')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('created_by')->references('user_id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->increments('announcement_id');
            $table->unsignedInteger('sport_id')->nullable();
            $table->string('title', 150);
            $table->text('body');
            $table->unsignedInteger('posted_by');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('sport_id')->references('sport_id')->on('sports')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('posted_by')->references('user_id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('log_id');
            $table->unsignedInteger('actor_id')->nullable();
            $table->string('table_name', 50);
            $table->unsignedInteger('record_id');
            $table->enum('action', ['create', 'update', 'delete']);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('actor_id')->references('user_id')->on('users')
                ->onDelete('set null')->onUpdate('cascade');
            $table->index(['table_name', 'record_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('special_training_attachments');
        Schema::dropIfExists('special_training_requests');
        Schema::dropIfExists('excuse_attachments');
        Schema::dropIfExists('excuses');
        Schema::dropIfExists('attendance');
        Schema::dropIfExists('users');
        Schema::dropIfExists('sports');
    }
};
