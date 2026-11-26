<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DeadlineReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Task $task,
        private readonly bool $isOverdue,
        private readonly int $daysLeft = 1,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $dueStr = $this->task->due_date?->format('d M Y') ?? '-';

        if ($this->isOverdue) {
            $title = 'Peringatan: Tugas Melewati Tenggat';
            $message = "Tugas \"{$this->task->title}\" ({$this->task->code}) telah melewati tenggat ({$dueStr}).";
            $type = 'task_overdue';
        } elseif ($this->daysLeft === 2) {
            $title = 'Pengingat Tenggat Tugas (H-2)';
            $message = "Tugas \"{$this->task->title}\" ({$this->task->code}) akan jatuh tempo 2 hari lagi ({$dueStr}). Ketercapaian: {$this->task->progress_percent}%.";
            $type = 'task_due_h2';
        } else {
            $title = 'Pengingat Tenggat Tugas (H-1)';
            $message = "Tugas \"{$this->task->title}\" ({$this->task->code}) akan jatuh tempo besok ({$dueStr}). Ketercapaian: {$this->task->progress_percent}%.";
            $type = 'task_due_h1';
        }

        return [
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'task_id' => $this->task->id,
            'url' => route('tasks.show', $this->task, false),
        ];
    }
}
