<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_settings', function (Blueprint $table): void {
            $table->string('reminder_time', 5)->default('07:00')->after('auto_remind_logbook');
        });
    }

    public function down(): void
    {
        Schema::table('wa_settings', function (Blueprint $table): void {
            $table->dropColumn('reminder_time');
        });
    }
};
