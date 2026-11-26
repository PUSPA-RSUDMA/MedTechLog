<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diklat_tasks', function (Blueprint $table): void {
            $table->string('drive_file_id', 200)->nullable()->after('file_name');
            $table->string('drive_url', 1000)->nullable()->after('drive_file_id');
        });

        Schema::create('google_drive_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_enabled')->default(false);
            $table->string('client_id', 255)->nullable();
            $table->string('client_secret', 255)->nullable();
            $table->text('refresh_token')->nullable();
            $table->string('folder_id', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_drive_settings');

        Schema::table('diklat_tasks', function (Blueprint $table): void {
            $table->dropColumn(['drive_file_id', 'drive_url']);
        });
    }
};
