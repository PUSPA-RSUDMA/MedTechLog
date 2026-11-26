<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pics', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('phone', 30)->nullable();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('professions', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('diklat_tasks', function (Blueprint $table): void {
            $table->id();
            $table->string('participant_name', 150);
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->foreignId('profession_id')->constrained('professions')->restrictOnDelete();
            $table->string('material', 255);
            $table->date('event_date')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('task_due_date_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->date('old_due_date');
            $table->date('new_due_date');
            $table->text('reason')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['task_id', 'created_at']);
        });

        Schema::table('tasks', function (Blueprint $table): void {
            $table->string('department', 20)->default('it')->after('code')->index(); // it | rm
            $table->foreignId('pic_id')->nullable()->after('assigned_to')->constrained('pics')->nullOnDelete();
            $table->timestamp('reminded_h2_at')->nullable()->after('actual_completed_at');
            $table->timestamp('reminded_h1_at')->nullable()->after('reminded_h2_at');
        });

        Schema::create('wa_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('gateway_url', 255)->default('http://127.0.0.1:3001');
            $table->string('api_key', 255)->nullable();
            $table->string('default_target_phone', 40)->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->boolean('auto_remind_tasks')->default(true);
            $table->boolean('auto_remind_logbook')->default(true);
            $table->string('session_status', 40)->default('disconnected');
            $table->timestamps();
        });

        Schema::create('wa_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('recipient_phone', 40);
            $table->string('recipient_name', 150)->nullable();
            $table->text('message');
            $table->string('category', 30)->default('manual'); // manual | task_h2 | task_h1 | logbook_h1
            $table->string('status', 20)->default('pending'); // pending | sent | failed
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_messages');
        Schema::dropIfExists('wa_settings');

        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropForeign(['pic_id']);
            $table->dropColumn(['department', 'pic_id', 'reminded_h2_at', 'reminded_h1_at']);
        });

        Schema::dropIfExists('task_due_date_histories');
        Schema::dropIfExists('diklat_tasks');
        Schema::dropIfExists('professions');
        Schema::dropIfExists('pics');
    }
};
