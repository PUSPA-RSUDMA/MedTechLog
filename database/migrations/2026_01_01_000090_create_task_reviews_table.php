<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->restrictOnDelete();
            $table->string('decision', 30);
            $table->text('comments')->nullable();
            $table->dateTime('reviewed_at');
            $table->timestamps();

            $table->index(['task_id', 'reviewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_reviews');
    }
};
