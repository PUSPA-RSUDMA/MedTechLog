<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diklat_units', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::dropIfExists('diklat_tasks');

        Schema::create('diklat_tasks', function (Blueprint $table): void {
            $table->id();
            $table->string('participant_name', 150);
            $table->foreignId('unit_id')->constrained('diklat_units')->restrictOnDelete();
            $table->foreignId('profession_id')->constrained('professions')->restrictOnDelete();
            $table->string('material', 255);
            $table->date('event_date')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diklat_tasks');
        Schema::dropIfExists('diklat_units');
    }
};
