<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ReviewDecision;
use App\Enums\TaskStatus;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Task;
use App\Models\TaskReview;
use App\Notifications\TaskReviewedNotification;
use App\Services\AuditLogService;
use App\Services\ProgressCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function __construct(
        private readonly ProgressCalculationService $progressService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $pendingTasks = Task::query()
            ->visibleTo($user)
            ->where('status', TaskStatus::PendingReview->value)
            ->when($user->isSupervisor(), fn ($q) => $q->where('assigned_to', '!=', $user->id))
            ->with(['target', 'category', 'assignee.unit', 'workLogs', 'attachments'])
            ->orderBy('due_date')
            ->paginate(12);

        $recentReviews = TaskReview::query()
            ->with(['task.assignee', 'reviewer'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('reviewer_id', $user->id))
            ->latest('reviewed_at')
            ->take(10)
            ->get();

        return view('reviews.index', [
            'pendingTasks' => $pendingTasks,
            'recentReviews' => $recentReviews,
        ]);
    }

    public function show(Task $task): View
    {
        $this->authorize('review', $task);

        $task->load([
            'target',
            'category',
            'assignee.unit',
            'workLogs.user',
            'workLogs.attachments',
            'attachments.uploader',
            'reviews.reviewer',
        ]);

        return view('reviews.show', [
            'task' => $task,
            'decisions' => ReviewDecision::options(),
        ]);
    }

    public function store(StoreReviewRequest $request, Task $task): RedirectResponse
    {
        $this->authorize('review', $task);

        $decision = ReviewDecision::from($request->validated('decision'));
        $comments = $request->validated('comments');
        $oldStatus = $task->status->value;

        TaskReview::create([
            'task_id' => $task->id,
            'reviewer_id' => $request->user()->id,
            'decision' => $decision,
            'comments' => $comments,
            'reviewed_at' => now(),
        ]);

        $newStatus = match ($decision) {
            ReviewDecision::Approved => TaskStatus::Completed,
            ReviewDecision::RevisionRequired => TaskStatus::RevisionRequired,
            ReviewDecision::Rejected => TaskStatus::Rejected,
        };

        $this->progressService->recalculateTask($task, $newStatus);

        $this->auditLogService->record(
            'reviewed',
            $task,
            ['status' => $oldStatus],
            ['status' => $newStatus->value, 'decision' => $decision->value, 'comments' => $comments]
        );

        $task->assignee?->notify(new TaskReviewedNotification($task, $decision, $request->user()->name, $comments));

        return redirect()->route('reviews.index')
            ->with('success', "Verifikasi tugas {$task->code} berhasil disimpan dengan keputusan: {$decision->label()}.");
    }
}
