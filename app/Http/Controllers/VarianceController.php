<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkCategory;
use App\Models\WorkPeriod;
use App\Services\VarianceAnalysisService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VarianceController extends Controller
{
    public function __construct(
        private readonly VarianceAnalysisService $varianceService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Task::query()
            ->visibleTo($user)
            ->with(['target.workPeriod', 'category', 'assignee.unit']);

        if ($request->filled('work_period_id')) {
            $periodId = $request->integer('work_period_id');
            $query->whereHas('target', fn ($q) => $q->where('work_period_id', $periodId));
        }

        if ($request->filled('unit_id')) {
            $unitId = $request->integer('unit_id');
            $query->whereHas('assignee', fn ($q) => $q->where('unit_id', $unitId));
        }

        if ($request->filled('user_id')) {
            $query->where('assigned_to', $request->integer('user_id'));
        }

        if ($request->filled('work_category_id')) {
            $query->where('work_category_id', $request->integer('work_category_id'));
        }

        $tasks = $query->orderBy('due_date')->get();
        $analysis = $this->varianceService->analyze($tasks);

        $usersQuery = User::where('is_active', true)->orderBy('name');
        if (! $user->isAdmin()) {
            $usersQuery->whereIn('id', $user->visibleUserIds() ?? []);
        }

        return view('variance.index', [
            'analysis' => $analysis,
            'periods' => WorkPeriod::orderByDesc('start_date')->get(),
            'units' => Unit::active()->orderBy('name')->get(),
            'users' => $usersQuery->get(),
            'categories' => WorkCategory::active()->orderBy('name')->get(),
        ]);
    }
}
