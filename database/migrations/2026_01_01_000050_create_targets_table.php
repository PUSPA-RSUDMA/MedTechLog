<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('targets', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->foreignId('work_period_id')->constrained('work_periods')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->foreignId('work_category_id')->constrained('work_categories')->restrictOnDelete();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('output_unit', 60);
            $table->decimal('target_value', 12, 2);
            $table->decimal('achieved_value', 12, 2)->default(0);
            $table->decimal('weight', 5, 2)->default(0);
            $table->string('priority', 20)->default('medium');
            $table->string('status', 30)->default('active');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->index(['work_period_id', 'unit_id', 'status']);
            $table->index(['owner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('targets');
    }
};
