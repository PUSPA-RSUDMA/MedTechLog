<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\LogbookEvent;
use App\Services\LogbookReminderService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogbookController extends Controller
{
    public function __construct(
        private readonly LogbookReminderService $reminderService,
    ) {}

    public function index(Request $request): View
    {
        // Trigger H-1 reminders automatically when Admin opens Logbook
        $this->reminderService->sendDueReminders();

        $monthParam = $request->string('month')->toString();
        try {
            $currentMonth = preg_match('/^\d{4}-\d{2}$/', $monthParam)
                ? Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth()
                : now()->startOfMonth();
        } catch (\Throwable) {
            $currentMonth = now()->startOfMonth();
        }

        $gridStart = $currentMonth->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $currentMonth->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $events = LogbookEvent::query()
            ->with('creator')
            ->whereDate('event_date', '>=', $gridStart->toDateString())
            ->whereDate('event_date', '<=', $gridEnd->toDateString())
            ->orderBy('event_date')
            ->orderBy('id')
            ->get();

        $eventsByDate = $events->groupBy(fn (LogbookEvent $e) => $e->event_date->format('Y-m-d'))
            ->map(fn ($group) => $group->map(fn (LogbookEvent $e) => [
                'id' => $e->id,
                'event_date' => $e->event_date->format('Y-m-d'),
                'title' => $e->title,
                'location' => $e->location,
                'description' => $e->description,
                'color' => $e->color,
                'creator_name' => $e->creator?->name ?? 'Admin',
            ])->values()->all())
            ->all();

        $days = [];
        $cursor = $gridStart->copy();
        while ($cursor->lte($gridEnd)) {
            $days[] = [
                'date' => $cursor->format('Y-m-d'),
                'day' => $cursor->day,
                'isCurrentMonth' => $cursor->month === $currentMonth->month,
                'isToday' => $cursor->isToday(),
            ];
            $cursor->addDay();
        }

        $upcomingEvents = LogbookEvent::query()
            ->whereDate('event_date', '>=', now()->toDateString())
            ->whereDate('event_date', '<=', now()->addDays(14)->toDateString())
            ->orderBy('event_date')
            ->orderBy('id')
            ->get();

        return view('logbook.index', [
            'currentMonth' => $currentMonth,
            'prevMonth' => $currentMonth->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $currentMonth->copy()->addMonth()->format('Y-m'),
            'days' => $days,
            'eventsByDate' => $eventsByDate,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'event_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:200'],
            'location' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:20'],
        ], [
            'event_date.required' => 'Tanggal acara wajib dipilih.',
            'title.required' => 'Nama/judul acara wajib diisi.',
        ]);

        $event = LogbookEvent::create([
            'user_id' => $request->user()->id,
            'event_date' => $data['event_date'],
            'title' => $data['title'],
            'location' => $data['location'] ?? null,
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?: '#0F766E',
        ]);

        $this->reminderService->sendDueReminders();

        return redirect()
            ->route('logbook.index', ['month' => $event->event_date->format('Y-m')])
            ->with('success', "Acara \"{$event->title}\" berhasil ditambahkan ke logbook.");
    }

    public function update(Request $request, LogbookEvent $logbookEvent): RedirectResponse
    {
        $data = $request->validate([
            'event_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:200'],
            'location' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $dateChanged = $logbookEvent->event_date->format('Y-m-d') !== $data['event_date'];

        $logbookEvent->update([
            'event_date' => $data['event_date'],
            'title' => $data['title'],
            'location' => $data['location'] ?? null,
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?: '#0F766E',
            'reminder_sent_at' => $dateChanged ? null : $logbookEvent->reminder_sent_at,
        ]);

        $this->reminderService->sendDueReminders();

        return redirect()
            ->route('logbook.index', ['month' => $logbookEvent->event_date->format('Y-m')])
            ->with('success', "Acara \"{$logbookEvent->title}\" berhasil diperbarui.");
    }

    public function destroy(LogbookEvent $logbookEvent): RedirectResponse
    {
        $month = $logbookEvent->event_date->format('Y-m');
        $title = $logbookEvent->title;
        $logbookEvent->delete();

        return redirect()
            ->route('logbook.index', ['month' => $month])
            ->with('success', "Acara \"{$title}\" berhasil dihapus dari logbook.");
    }
}
