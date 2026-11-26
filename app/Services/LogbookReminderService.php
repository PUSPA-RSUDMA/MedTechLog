<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use App\Models\LogbookEvent;
use App\Models\User;
use App\Models\WaSetting;
use App\Notifications\LogbookEventReminderNotification;

class LogbookReminderService
{
    public function __construct(
        private readonly WaManagerService $waService,
    ) {}

    /**
     * Send H-1 reminders (in-app + WhatsApp) to all active Admins for tomorrow's logbook events.
     */
    public function sendDueReminders(): int
    {
        $tomorrow = now()->addDay()->toDateString();

        $events = LogbookEvent::query()
            ->whereDate('event_date', $tomorrow)
            ->whereNull('reminder_sent_at')
            ->get();

        if ($events->isEmpty()) {
            return 0;
        }

        $admins = User::query()
            ->where('role', UserRole::Admin->value)
            ->where('is_active', true)
            ->get();

        $waSetting = WaSetting::current();
        $sent = 0;

        foreach ($events as $event) {
            foreach ($admins as $admin) {
                $admin->notify(new LogbookEventReminderNotification($event));
            }

            if ($waSetting->auto_remind_logbook && $waSetting->default_target_phone) {
                $loc = $event->location ? "\nLokasi: {$event->location}" : '';
                $msg = "*[MedTechLog — Pengingat Logbook Acara H-1]*\n"
                    . "Acara: *{$event->title}*\n"
                    . "Tanggal: *{$event->event_date->format('d M Y')}* (BESOK!){$loc}";
                $this->waService->send($waSetting->default_target_phone, $msg, 'logbook_h1', 'Admin Logbook');
            }

            $event->update(['reminder_sent_at' => now()]);
            $sent++;
        }

        return $sent;
    }
}
