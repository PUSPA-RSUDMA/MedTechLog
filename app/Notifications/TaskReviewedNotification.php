<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\ReviewDecision;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskReviewedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Task $task,
        private readonly ReviewDecision $decision,
        private readonly string $reviewerName,
        private readonly ?string $comments = null,
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
        $suffix = $this->comments ? " Catatan: \"{$this->comments}\"" : '';

        return [
            'type' => 'task_reviewed',
            'title' => 'Hasil Verifikasi: ' . $this->decision->label(),
            'message' => "{$this->reviewerName} memberikan status {$this->decision->label()} pada tugas \"{$this->task->title}\" ({$this->task->code}).{$suffix}",
            'task_id' => $this->task->id,
            'url' => route('tasks.show', $this->task, false),
        ];
    }
}
