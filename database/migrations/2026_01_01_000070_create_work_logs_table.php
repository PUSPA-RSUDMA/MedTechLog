<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->date('log_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->unsignedInteger('duration_minutes');
            $table->decimal('output_achieved', 12, 2)->default(0);
            $table->text('activity_description');
            $table->string('status_after_log', 30);
            $table->text('obstacle')->nullable();
            $table->text('solution_plan')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'log_date']);
            $table->index(['task_id', 'log_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_logs');
    }
};
