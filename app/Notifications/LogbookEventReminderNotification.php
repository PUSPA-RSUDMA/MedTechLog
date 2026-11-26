<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\LogbookEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LogbookEventReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly LogbookEvent $event,
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
        $dateStr = $this->event->event_date?->format('d M Y') ?? '-';
        $loc = $this->event->location ? " di {$this->event->location}" : '';

        return [
            'type' => 'logbook_reminder',
            'title' => 'Pengingat Acara Logbook (H-1)',
            'message' => "Acara \"{$this->event->title}\"{$loc} dijadwalkan besok ({$dateStr}).",
            'event_id' => $this->event->id,
            'url' => route('logbook.index', [
                'month' => $this->event->event_date?->format('Y-m'),
            ], false),
        ];
    }
}
