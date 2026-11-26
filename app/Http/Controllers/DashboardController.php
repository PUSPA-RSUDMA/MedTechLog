<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Models\DiklatTask;
use App\Models\LogbookEvent;
use App\Models\Task;
use App\Models\TaskDueDateHistory;
use App\Models\WaSetting;
use App\Services\LogbookReminderService;
use App\Services\TaskDeadlineReminderService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly TaskDeadlineReminderService $taskReminder,
        private readonly LogbookReminderService $logbookReminder,
    ) {}

    public function index(Request $request): View
    {
        // Trigger automatic H-2 / H-1 reminders when Admin opens Dashboard
        $this->taskReminder->sendDueReminders();
        $this->logbookReminder->sendDueReminders();

        $allTasks = Task::query()->with(['pic.unit', 'assignee.unit'])->get();
        $itTasks = $allTasks->where('department', 'it');
        $rmTasks = $allTasks->where('department', 'rm');

        $today = now()->startOfDay();
        $h2Limit = now()->addDays(2)->endOfDay();

        $dueSoonTasks = $allTasks->filter(fn (Task $t) => ! in_array($t->status, [TaskStatus::Completed, TaskStatus::Rejected], true)
            && $t->due_date !== null
            && $t->due_date->gte($today)
            && $t->due_date->lte($h2Limit))->sortBy('due_date')->values();

        $overdueTasks = $allTasks->filter(fn (Task $t) => ! in_array($t->status, [TaskStatus::Completed, TaskStatus::Rejected], true)
            && $t->due_date !== null
            && $t->due_date->lt($today))->sortBy('due_date')->values();

        $diklatCount = DiklatTask::count();
        $upcomingLogbook = LogbookEvent::query()
            ->whereDate('event_date', '>=', $today->toDateString())
            ->orderBy('event_date')
            ->take(5)
            ->get();

        $recentDueDateHistories = TaskDueDateHistory::query()
            ->with(['task', 'changer'])
            ->latest('created_at')
            ->take(6)
            ->get();

        $deptChart = [
            'labels' => ['Tugas IT', 'Tugas RM'],
            'total' => [$itTasks->count(), $rmTasks->count()],
            'completed' => [
                $itTasks->where('status', TaskStatus::Completed)->count(),
                $rmTasks->where('status', TaskStatus::Completed)->count(),
            ],
            'avg_progress' => [
                $itTasks->isNotEmpty() ? round((float) $itTasks->avg('progress_percent'), 1) : 0,
                $rmTasks->isNotEmpty() ? round((float) $rmTasks->avg('progress_percent'), 1) : 0,
            ],
        ];

        $statusChart = [
            'labels' => ['Belum Mulai', 'Berjalan', 'Tertunda', 'Selesai'],
            'counts' => [
                $allTasks->where('status', TaskStatus::NotStarted)->count(),
                $allTasks->where('status', TaskStatus::InProgress)->count(),
                $allTasks->where('status', TaskStatus::OnHold)->count(),
                $allTasks->where('status', TaskStatus::Completed)->count(),
            ],
        ];

        return view('dashboard', [
            'kpi' => [
                'it_total' => $itTasks->count(),
                'it_completed' => $itTasks->where('status', TaskStatus::Completed)->count(),
                'it_avg' => $itTasks->isNotEmpty() ? round((float) $itTasks->avg('progress_percent'), 1) : 0.0,
                'rm_total' => $rmTasks->count(),
                'rm_completed' => $rmTasks->where('status', TaskStatus::Completed)->count(),
                'rm_avg' => $rmTasks->isNotEmpty() ? round((float) $rmTasks->avg('progress_percent'), 1) : 0.0,
                'diklat_total' => $diklatCount,
                'due_soon' => $dueSoonTasks->count(),
                'overdue' => $overdueTasks->count(),
                'logbook_upcoming' => $upcomingLogbook->count(),
                'wa_status' => WaSetting::current()->session_status,
            ],
            'deptChart' => $deptChart,
            'statusChart' => $statusChart,
            'urgentTasks' => $overdueTasks->concat($dueSoonTasks)->take(8),
            'upcomingLogbook' => $upcomingLogbook,
            'recentDueDateHistories' => $recentDueDateHistories,
        ]);
    }
}
