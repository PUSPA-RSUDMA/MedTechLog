<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\PriorityLevel;
use App\Enums\TargetStatus;
use App\Http\Requests\StoreTargetRequest;
use App\Models\Target;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkCategory;
use App\Models\WorkPeriod;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TargetController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Target::query()
            ->visibleTo($user)
            ->with(['workPeriod', 'unit', 'category', 'owner'])
            ->withCount('tasks');

        if ($request->filled('search')) {
            $s = $request->string('search')->toString();
            $query->where(fn ($q) => $q->where('title', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"));
        }

        if ($request->filled('work_period_id')) {
            $query->where('work_period_id', $request->integer('work_period_id'));
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->integer('unit_id'));
        }

        if ($request->filled('work_category_id')) {
            $query->where('work_category_id', $request->integer('work_category_id'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->string('priority')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $targets = $query->latest()->paginate(12)->withQueryString();

        return view('targets.index', [
            'targets' => $targets,
            'periods' => WorkPeriod::orderByDesc('start_date')->get(),
            'units' => Unit::active()->orderBy('name')->get(),
            'categories' => WorkCategory::active()->orderBy('name')->get(),
            'priorities' => PriorityLevel::options(),
            'statuses' => TargetStatus::options(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Target::class);

        $user = $request->user();
        $ownersQuery = User::where('is_active', true)->orderBy('name');
        if ($user->isSupervisor()) {
            $ownersQuery->whereIn('id', $user->visibleUserIds() ?? []);
        }

        return view('targets.create', [
            'periods' => WorkPeriod::orderByDesc('is_active')->orderByDesc('start_date')->get(),
            'units' => Unit::active()->orderBy('name')->get(),
            'categories' => WorkCategory::active()->orderBy('name')->get(),
            'owners' => $ownersQuery->get(),
            'priorities' => PriorityLevel::options(),
            'statuses' => TargetStatus::options(),
        ]);
    }

    public function store(StoreTargetRequest $request): RedirectResponse
    {
        $this->authorize('create', Target::class);

        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        $target = Target::create($data);
        $this->auditLogService->record('created', $target, null, $target->only(['code', 'title', 'target_value', 'weight', 'status']));

        return redirect()->route('targets.show', $target)
            ->with('success', "Target {$target->code} berhasil ditambahkan.");
    }

    public function show(Target $target): View
    {
        $this->authorize('view', $target);

        $target->load([
            'workPeriod',
            'unit',
            'category',
            'owner',
            'creator',
            'tasks' => fn ($q) => $q->with(['assignee', 'category'])->orderBy('due_date'),
        ]);

        $unitPeriodTotalWeight = (float) Target::query()
            ->where('work_period_id', $target->work_period_id)
            ->where('unit_id', $target->unit_id)
            ->where('status', '!=', TargetStatus::Archived->value)
            ->sum('weight');

        return view('targets.show', [
            'target' => $target,
            'unitPeriodTotalWeight' => $unitPeriodTotalWeight,
            'totalEstimatedHours' => round((float) $target->tasks->sum('estimated_hours'), 2),
            'totalActualHours' => round((float) $target->tasks->sum('actual_hours'), 2),
        ]);
    }

    public function edit(Request $request, Target $target): View
    {
        $this->authorize('update', $target);

        $user = $request->user();
        $ownersQuery = User::where('is_active', true)->orderBy('name');
        if ($user->isSupervisor()) {
            $ownersQuery->whereIn('id', $user->visibleUserIds() ?? []);
        }

        return view('targets.edit', [
            'target' => $target,
            'periods' => WorkPeriod::orderByDesc('is_active')->orderByDesc('start_date')->get(),
            'units' => Unit::active()->orderBy('name')->get(),
            'categories' => WorkCategory::active()->orderBy('name')->get(),
            'owners' => $ownersQuery->get(),
            'priorities' => PriorityLevel::options(),
            'statuses' => TargetStatus::options(),
        ]);
    }

    public function update(StoreTargetRequest $request, Target $target): RedirectResponse
    {
        $this->authorize('update', $target);

        $old = $target->only(['title', 'target_value', 'weight', 'status', 'priority']);
        $target->update($request->validated());

        $this->auditLogService->record('updated', $target, $old, $target->only(['title', 'target_value', 'weight', 'status', 'priority']));

        return redirect()->route('targets.show', $target)
            ->with('success', "Target {$target->code} berhasil diperbarui.");
    }

    public function destroy(Target $target): RedirectResponse
    {
        $this->authorize('delete', $target);

        $old = $target->only(['code', 'title']);
        $target->delete();

        $this->auditLogService->record('deleted', $target, $old, null);

        return redirect()->route('targets.index')
            ->with('success', 'Target berhasil dihapus.');
    }
}
