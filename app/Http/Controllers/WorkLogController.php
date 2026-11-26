<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ComplexityLevel;
use App\Enums\PriorityLevel;
use App\Enums\TaskStatus;
use App\Http\Requests\StoreWorkLogRequest;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkCategory;
use App\Models\WorkLog;
use App\Services\AttachmentService;
use App\Services\AuditLogService;
use App\Services\ProgressCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkLogController extends Controller
{
    public function __construct(
        private readonly ProgressCalculationService $progressService,
        private readonly AttachmentService $attachmentService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $query = WorkLog::query()
            ->visibleTo($user)
            ->with(['task.category', 'user', 'attachments']);

        if ($request->filled('search')) {
            $s = $request->string('search')->toString();
            $query->where(fn ($q) => $q->where('activity_description', 'like', "%{$s}%")
                ->orWhereHas('task', fn ($tq) => $tq->where('title', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%")));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('log_date', '>=', $request->string('date_from')->toString());
        }

        if ($request->filled('date_to')) {
            $query->whereDate('log_date', '<=', $request->string('date_to')->toString());
        }

        $workLogs = $query->orderByDesc('log_date')->orderByDesc('id')->paginate(15)->withQueryString();

        $usersQuery = User::where('is_active', true)->orderBy('name');
        if (! $user->isAdmin()) {
            $usersQuery->whereIn('id', $user->visibleUserIds() ?? []);
        }

        return view('work-logs.index', [
            'workLogs' => $workLogs,
            'users' => $usersQuery->get(),
            'totalMinutes' => (int) (clone $query)->reorder()->sum('duration_minutes'),
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        $activeTasks = Task::query()
            ->visibleTo($user)
            ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Rejected->value])
            ->with('category')
            ->orderBy('due_date')
            ->get();

        return view('work-logs.create', [
            'tasks' => $activeTasks,
            'categories' => WorkCategory::active()->orderBy('name')->get(),
            'statuses' => [
                TaskStatus::InProgress->value => TaskStatus::InProgress->label(),
                TaskStatus::OnHold->value => TaskStatus::OnHold->label(),
                TaskStatus::PendingReview->value => TaskStatus::PendingReview->label(),
            ],
            'defaultTaskId' => $request->integer('task_id') ?: null,
        ]);
    }

    public function store(StoreWorkLogRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();

        if ($request->boolean('create_incidental_task')) {
            $task = Task::create([
                'target_id' => null,
                'work_category_id' => $data['incidental_category_id'],
                'assigned_to' => $user->id,
                'created_by' => $user->id,
                'title' => $data['incidental_title'],
                'description' => 'Tugas insidental dibuat cepat dari pencatatan log kerja harian.',
                'is_incidental' => true,
                'estimated_hours' => $data['incidental_estimated_hours'],
                'estimated_start_date' => $data['log_date'],
                'due_date' => $data['log_date'],
                'complexity' => ComplexityLevel::Light,
                'estimated_output' => max(1, (float) ($data['output_achieved'] ?? 1)),
                'output_unit' => 'kegiatan',
                'priority' => PriorityLevel::Medium,
                'status' => TaskStatus::from($data['status_after_log']),
            ]);
        } else {
            $task = Task::findOrFail($data['task_id']);
            $this->authorize('view', $task);
        }

        $workLog = WorkLog::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'log_date' => $data['log_date'],
            'start_time' => null,
            'end_time' => null,
            'duration_minutes' => (int) ($data['duration_minutes'] ?? 0),
            'output_achieved' => $data['output_achieved'] ?? 0,
            'activity_description' => $data['activity_description'],
            'status_after_log' => $data['status_after_log'],
            'obstacle' => $data['obstacle'] ?? null,
            'solution_plan' => $data['solution_plan'] ?? null,
        ]);

        $statusAfter = TaskStatus::from($data['status_after_log']);
        $this->progressService->recalculateTask($task, $statusAfter);
        $this->auditLogService->record('created', $workLog, null, $workLog->only(['task_id', 'log_date', 'status_after_log']));

        if ($statusAfter === TaskStatus::PendingReview) {
            $assignee = $task->assignee;
            if ($assignee?->supervisor !== null) {
                $assignee->supervisor->notify(new \App\Notifications\TaskSubmittedForReviewNotification($task, $user->name));
            }
        }

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Update ketercapaian tugas berhasil disimpan.');
    }

    public function destroy(WorkLog $workLog): RedirectResponse
    {
        $this->authorize('delete', $workLog);

        $task = $workLog->task;
        $old = $workLog->only(['task_id', 'log_date', 'duration_minutes']);
        $workLog->delete();

        if ($task !== null) {
            $this->progressService->recalculateTask($task);
        }

        $this->auditLogService->record('deleted', $workLog, $old, null);

        return back()->with('success', 'Log pekerjaan berhasil dihapus.');
    }
}
