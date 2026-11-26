<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\LogbookReminderService;
use App\Services\TaskDeadlineReminderService;
use Illuminate\Console\Command;

class CheckDeadlinesCommand extends Command
{
    protected $signature = 'app:check-deadlines';

    protected $description = 'Kirim notifikasi & WA otomatis untuk tugas H-2/H-1 dan acara logbook H-1';

    public function handle(
        TaskDeadlineReminderService $taskReminder,
        LogbookReminderService $logbookReminder,
    ): int {
        $taskResult = $taskReminder->sendDueReminders();
        $logbookSent = $logbookReminder->sendDueReminders();

        $this->info("Pengingat Tugas dikirim: H-2 = {$taskResult['h2']}, H-1 = {$taskResult['h1']}.");
        $this->info("Pengingat Logbook Acara H-1 dikirim: {$logbookSent}.");

        return self::SUCCESS;
    }
}
