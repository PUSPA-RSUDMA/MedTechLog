<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('employee_id', 50)->nullable()->unique()->after('id');
            $table->string('role', 30)->default('staff')->after('password');
            $table->foreignId('unit_id')->nullable()->after('role')->constrained('units')->nullOnDelete();
            $table->foreignId('supervisor_id')->nullable()->after('unit_id')->constrained('users')->nullOnDelete();
            $table->string('position', 120)->nullable()->after('supervisor_id');
            $table->boolean('is_active')->default(true)->after('position');

            $table->index(['role', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['supervisor_id']);
            $table->dropIndex(['role', 'is_active']);
            $table->dropColumn(['employee_id', 'role', 'unit_id', 'supervisor_id', 'position', 'is_active']);
        });
    }
};
