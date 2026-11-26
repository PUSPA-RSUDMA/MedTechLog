<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\Task;
use App\Models\User;
use App\Models\WaSetting;
use App\Notifications\DeadlineReminderNotification;

class TaskDeadlineReminderService
{
    public function __construct(
        private readonly WaManagerService $waService,
    ) {}

    /**
     * Send H-2 and H-1 reminders (in-app + WhatsApp) for unfinished tasks.
     *
     * @return array{h2: int, h1: int}
     */
    public function sendDueReminders(): array
    {
        $h1Date = now()->addDay()->toDateString();
        $h2Date = now()->addDays(2)->toDateString();

        $admins = User::query()
            ->where('role', UserRole::Admin->value)
            ->where('is_active', true)
            ->get();

        $waSetting = WaSetting::current();
        $sentH2 = 0;
        $sentH1 = 0;

        // H-2 tasks
        $h2Tasks = Task::query()
            ->with(['pic.unit', 'assignee.unit'])
            ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Rejected->value])
            ->whereDate('due_date', $h2Date)
            ->whereNull('reminded_h2_at')
            ->get();

        foreach ($h2Tasks as $task) {
            foreach ($admins as $admin) {
                $admin->notify(new DeadlineReminderNotification($task, false, 2));
            }

            if ($waSetting->auto_remind_tasks) {
                $phone = $task->pic?->phone ?: $waSetting->default_target_phone;
                if ($phone) {
                    $msg = "*[MedTechLog — Pengingat Tugas H-2]*\n"
                        . "Tugas: *{$task->title}* ({$task->code})\n"
                        . "PIC / Unit: {$task->display_pic_name} ({$task->display_unit_name})\n"
                        . "Tanggal Target: *{$task->due_date->format('d M Y')}* (2 hari lagi)\n"
                        . "Ketercapaian saat ini: *{$task->progress_percent}%*";
                    $this->waService->send($phone, $msg, 'task_h2', $task->display_pic_name);
                }
            }

            $task->update(['reminded_h2_at' => now()]);
            $sentH2++;
        }

        // H-1 tasks
        $h1Tasks = Task::query()
            ->with(['pic.unit', 'assignee.unit'])
            ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Rejected->value])
            ->whereDate('due_date', $h1Date)
            ->whereNull('reminded_h1_at')
            ->get();

        foreach ($h1Tasks as $task) {
            foreach ($admins as $admin) {
                $admin->notify(new DeadlineReminderNotification($task, false, 1));
            }

            if ($waSetting->auto_remind_tasks) {
                $phone = $task->pic?->phone ?: $waSetting->default_target_phone;
                if ($phone) {
                    $msg = "*[MedTechLog — Pengingat Tugas H-1]*\n"
                        . "Tugas: *{$task->title}* ({$task->code})\n"
                        . "PIC / Unit: {$task->display_pic_name} ({$task->display_unit_name})\n"
                        . "Tanggal Target: *{$task->due_date->format('d M Y')}* (BESOK!)\n"
                        . "Ketercapaian saat ini: *{$task->progress_percent}%*\n"
                        . "Mohon segera diselesaikan hingga 100%.";
                    $this->waService->send($phone, $msg, 'task_h1', $task->display_pic_name);
                }
            }

            $task->update(['reminded_h1_at' => now()]);
            $sentH1++;
        }

        return ['h2' => $sentH2, 'h1' => $sentH1];
    }
}
