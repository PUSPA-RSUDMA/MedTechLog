<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ComplexityLevel;
use App\Enums\PriorityLevel;
use App\Enums\TaskStatus;
use App\Http\Requests\StoreTaskRequest;
use App\Models\Pic;
use App\Models\Task;
use App\Models\TaskDueDateHistory;
use App\Models\WorkCategory;
use App\Services\AuditLogService;
use App\Services\ProgressCalculationService;
use App\Services\TaskDeadlineReminderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function __construct(
        private readonly ProgressCalculationService $progressService,
        private readonly AuditLogService $auditLogService,
        private readonly TaskDeadlineReminderService $deadlineReminderService,
    ) {}

    public function index(Request $request, ?string $department = null): View
    {
        $this->deadlineReminderService->sendDueReminders();

        $dept = $department ?? $request->string('department')->toString();
        if (! in_array($dept, ['it', 'rm'], true)) {
            $dept = null;
        }

        $query = Task::query()
            ->with(['pic.unit', 'assignee.unit'])
            ->withCount('dueDateHistories');

        if ($dept !== null) {
            $query->where('department', $dept);
        }

        if ($request->filled('search')) {
            $s = $request->string('search')->toString();
            $query->where(fn ($q) => $q->where('title', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('pic_id')) {
            $query->where('pic_id', $request->integer('pic_id'));
        }

        if ($request->filled('health')) {
            $health = $request->string('health')->toString();
            $today = now()->toDateString();
            if ($health === 'completed') {
                $query->where('status', TaskStatus::Completed->value);
            } elseif ($health === 'overdue') {
                $query->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Rejected->value])
                    ->whereDate('due_date', '<', $today);
            } elseif ($health === 'at_risk') {
                $query->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Rejected->value])
                    ->whereDate('due_date', '>=', $today)
                    ->whereDate('due_date', '<=', now()->addDays(2)->toDateString())
                    ->where('progress_percent', '<', 75);
            } elseif ($health === 'on_track') {
                $query->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Rejected->value])
                    ->whereDate('due_date', '>', now()->addDays(2)->toDateString());
            }
        }

        $tasks = $query->orderBy('due_date')->paginate(15)->withQueryString();

        return view('tasks.index', [
            'tasks' => $tasks,
            'department' => $dept,
            'pics' => $this->picsForDepartment($dept),
            'statuses' => [
                TaskStatus::NotStarted->value => TaskStatus::NotStarted->label(),
                TaskStatus::InProgress->value => TaskStatus::InProgress->label(),
                TaskStatus::OnHold->value => TaskStatus::OnHold->label(),
                TaskStatus::Completed->value => TaskStatus::Completed->label(),
            ],
            'priorities' => PriorityLevel::options(),
        ]);
    }

    public function create(Request $request): View
    {
        $dept = $request->string('department')->toString();
        if (! in_array($dept, ['it', 'rm'], true)) {
            $dept = 'it';
        }

        return view('tasks.create', [
            'department' => $dept,
            'pics' => $this->picsForDepartment($dept),
            'priorities' => PriorityLevel::options(),
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $dept = in_array($data['department'] ?? '', ['it', 'rm'], true) ? $data['department'] : 'it';

        $defaultCategoryId = $data['work_category_id']
            ?? WorkCategory::query()->value('id')
            ?? WorkCategory::create(['code' => 'UMUM', 'name' => 'Umum', 'color' => '#0F766E', 'is_active' => true])->id;

        $task = Task::create([
            'department' => $dept,
            'target_id' => $data['target_id'] ?? null,
            'parent_task_id' => null,
            'work_category_id' => $defaultCategoryId,
            'assigned_to' => $data['assigned_to'] ?? $request->user()->id,
            'pic_id' => $data['pic_id'] ?? null,
            'created_by' => $request->user()->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'is_incidental' => false,
            'estimated_hours' => 1,
            'estimated_start_date' => $data['estimated_start_date'],
            'due_date' => $data['due_date'],
            'complexity' => ComplexityLevel::Medium->value,
            'estimated_output' => 100, // Paten 100%
            'output_unit' => '%',
            'priority' => $data['priority'],
            'status' => TaskStatus::NotStarted->value,
        ]);

        $this->auditLogService->record('created', $task, null, $task->only(['code', 'department', 'title', 'due_date']));
        $this->deadlineReminderService->sendDueReminders();

        return redirect()->route('tasks.show', $task)
            ->with('success', "Tugas {$task->code} berhasil ditambahkan (Target paten 100%).");
    }

    public function show(Task $task): View
    {
        $task->load([
            'pic.unit',
            'assignee.unit',
            'creator',
            'workLogs.user',
            'dueDateHistories.changer',
        ]);

        return view('tasks.show', [
            'task' => $task,
        ]);
    }

    public function edit(Task $task): View
    {
        return view('tasks.edit', [
            'task' => $task,
            'department' => $task->department,
            'pics' => $this->picsForDepartment($task->department),
            'priorities' => PriorityLevel::options(),
        ]);
    }

    private function picsForDepartment(?string $department)
    {
        $query = Pic::active()->with('unit')->orderBy('name');

        if ($department === 'it') {
            $query->whereHas('unit', fn ($q) => $q->where('code', 'IT-SIMRS')->orWhere('name', 'like', '%IT%'));
        } elseif ($department === 'rm') {
            $query->whereHas('unit', fn ($q) => $q->where('code', 'RM')->orWhere('name', 'like', '%Rekam Medis%'));
        }

        return $query->get();
    }

    public function update(StoreTaskRequest $request, Task $task): RedirectResponse
    {
        $old = $task->only(['title', 'due_date', 'status', 'priority', 'pic_id']);
        $oldDueDate = $task->due_date?->format('Y-m-d');
        $data = $request->validated();

        $dept = in_array($data['department'] ?? '', ['it', 'rm'], true) ? $data['department'] : $task->department;
        $newDueDate = $data['due_date'];
        $dueDateChanged = $oldDueDate !== null && $oldDueDate !== $newDueDate;

        $task->update([
            'department' => $dept,
            'pic_id' => $data['pic_id'] ?? $task->pic_id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'estimated_start_date' => $data['estimated_start_date'],
            'due_date' => $newDueDate,
            'estimated_output' => 100,
            'priority' => $data['priority'],
            'reminded_h2_at' => $dueDateChanged ? null : $task->reminded_h2_at,
            'reminded_h1_at' => $dueDateChanged ? null : $task->reminded_h1_at,
        ]);

        if ($dueDateChanged) {
            TaskDueDateHistory::create([
                'task_id' => $task->id,
                'old_due_date' => $oldDueDate,
                'new_due_date' => $newDueDate,
                'reason' => $data['due_date_change_reason'] ?? 'Perubahan tanggal target melalui edit tugas',
                'changed_by' => $request->user()->id,
                'created_at' => now(),
            ]);
        }

        $this->progressService->recalculateTask($task);
        $this->auditLogService->record('updated', $task, $old, $task->only(['title', 'due_date', 'status', 'priority', 'pic_id']));
        $this->deadlineReminderService->sendDueReminders();

        return redirect()->route('tasks.show', $task)
            ->with('success', "Tugas {$task->code} berhasil diperbarui.");
    }

    public function updateDueDate(Request $request, Task $task): RedirectResponse
    {
        $data = $request->validate([
            'new_due_date' => ['required', 'date', 'after_or_equal:' . ($task->estimated_start_date?->format('Y-m-d') ?? '2000-01-01')],
            'reason' => ['required', 'string', 'max:500'],
        ], [
            'new_due_date.required' => 'Tanggal target baru wajib diisi.',
            'reason.required' => 'Alasan perubahan tanggal target wajib diisi.',
        ]);

        $oldDueDate = $task->due_date?->format('Y-m-d');
        if ($oldDueDate === $data['new_due_date']) {
            return back()->with('error', 'Tanggal target baru sama dengan tanggal target saat ini.');
        }

        TaskDueDateHistory::create([
            'task_id' => $task->id,
            'old_due_date' => $oldDueDate,
            'new_due_date' => $data['new_due_date'],
            'reason' => $data['reason'],
            'changed_by' => $request->user()->id,
            'created_at' => now(),
        ]);

        $task->update([
            'due_date' => $data['new_due_date'],
            'reminded_h2_at' => null,
            'reminded_h1_at' => null,
        ]);

        $this->deadlineReminderService->sendDueReminders();

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Tanggal target berhasil diperbarui dan tercatat dalam riwayat perubahan.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $dept = $task->department;
        $old = $task->only(['code', 'title']);
        $task->delete();

        $this->auditLogService->record('deleted', $task, $old, null);

        return redirect()->route($dept === 'rm' ? 'tasks.rm' : 'tasks.it')
            ->with('success', 'Tugas berhasil dihapus.');
    }
}
