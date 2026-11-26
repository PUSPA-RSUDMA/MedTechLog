<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskSubmittedForReviewNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Task $task,
        private readonly string $submittedByName,
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
            'type' => 'task_submitted',
            'title' => 'Pengajuan Verifikasi Tugas',
            'message' => "{$this->submittedByName} mengajukan verifikasi untuk tugas \"{$this->task->title}\" ({$this->task->code}).",
            'task_id' => $this->task->id,
            'url' => route('reviews.show', $this->task, false),
        ];
    }
}
