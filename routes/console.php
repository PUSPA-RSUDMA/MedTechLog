<?php

declare(strict_types=1);

use App\Models\WaSetting;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

Schedule::command('app:check-deadlines')
    ->timezone('Asia/Jakarta')
    ->everyMinute()
    ->when(function (): bool {
        if (! Schema::hasTable('wa_settings')) {
            return now('Asia/Jakarta')->format('H:i') === '07:00';
        }

        $targetTime = WaSetting::query()->value('reminder_time') ?: '07:00';

        return now('Asia/Jakarta')->format('H:i') === $targetTime;
    });
