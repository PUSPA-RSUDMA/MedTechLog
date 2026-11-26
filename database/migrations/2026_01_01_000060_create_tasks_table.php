<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 40)->unique();
            $table->foreignId('target_id')->nullable()->constrained('targets')->nullOnDelete();
            $table->foreignId('parent_task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->foreignId('work_category_id')->constrained('work_categories')->restrictOnDelete();
            $table->foreignId('assigned_to')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->boolean('is_incidental')->default(false);

            // Estimasi
            $table->decimal('estimated_hours', 8, 2);
            $table->date('estimated_start_date');
            $table->date('due_date');
            $table->string('complexity', 20)->default('medium');
            $table->decimal('estimated_cost', 15, 2)->nullable();
            $table->decimal('estimated_output', 12, 2)->default(1);
            $table->string('output_unit', 60)->nullable();

            // Realisasi Agregat
            $table->decimal('actual_hours', 8, 2)->default(0);
            $table->decimal('actual_output', 12, 2)->default(0);
            $table->dateTime('actual_completed_at')->nullable();
            $table->unsignedTinyInteger('progress_percent')->default(0);

            $table->string('priority', 20)->default('medium');
            $table->string('status', 30)->default('not_started');
            $table->timestamps();

            $table->index(['assigned_to', 'status']);
            $table->index(['due_date', 'status']);
            $table->index(['target_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
