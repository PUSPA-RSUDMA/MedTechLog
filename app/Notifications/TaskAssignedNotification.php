<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Task $task,
        private readonly string $assignedByName,
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
        return [
            'type' => 'task_assigned',
            'title' => 'Tugas Baru Ditugaskan',
            'message' => "{$this->assignedByName} menugaskan \"{$this->task->title}\" ({$this->task->code}) kepada Anda.",
            'task_id' => $this->task->id,
            'url' => route('tasks.show', $this->task, false),
        ];
    }
}
