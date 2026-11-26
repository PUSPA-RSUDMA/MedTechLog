<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 30)->unique();
            $table->string('color', 20)->default('#0F766E');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_categories');
    }
};
