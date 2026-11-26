<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Models\Pic;
use App\Models\Task;
use App\Models\Unit;
use App\Services\ReportExportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportExportService $exportService,
    ) {}

    public function index(Request $request): View
    {
        $tasks = $this->buildQuery($request)->orderBy('due_date')->get();

        $byUnit = $tasks
            ->groupBy(fn (Task $t) => $t->display_unit_name)
            ->map(fn ($group, $unitName) => [
                'unit_name' => $unitName,
                'total_tasks' => $group->count(),
                'completed_tasks' => $group->where('status', TaskStatus::Completed)->count(),
                'avg_progress' => round((float) $group->avg('progress_percent'), 1),
            ])
            ->values();

        $byPic = $tasks
            ->groupBy(fn (Task $t) => $t->display_pic_name)
            ->map(function ($group, $picName) {
                /** @var Task $first */
                $first = $group->first();

                return [
                    'pic_name' => $picName,
                    'unit_name' => $first->display_unit_name,
                    'total_tasks' => $group->count(),
                    'completed_tasks' => $group->where('status', TaskStatus::Completed)->count(),
                    'avg_progress' => round((float) $group->avg('progress_percent'), 1),
                ];
            })
            ->sortByDesc('avg_progress')
            ->values();

        $overdueCount = $tasks->filter(fn (Task $t) => $t->schedule_health['key'] === 'overdue')->count();

        return view('reports.index', [
            'tasks' => $tasks,
            'byUnit' => $byUnit,
            'byPic' => $byPic,
            'summary' => [
                'total_tasks' => $tasks->count(),
                'completed_tasks' => $tasks->where('status', TaskStatus::Completed)->count(),
                'overdue_tasks' => $overdueCount,
                'avg_progress' => $tasks->isNotEmpty() ? round((float) $tasks->avg('progress_percent'), 1) : 0.0,
            ],
            'units' => Unit::active()->orderBy('name')->get(),
            'pics' => Pic::active()->with('unit')->orderBy('name')->get(),
            'statuses' => [
                TaskStatus::NotStarted->value => TaskStatus::NotStarted->label(),
                TaskStatus::InProgress->value => TaskStatus::InProgress->label(),
                TaskStatus::OnHold->value => TaskStatus::OnHold->label(),
                TaskStatus::Completed->value => TaskStatus::Completed->label(),
            ],
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $tasks = $this->buildQuery($request)->orderBy('due_date')->get();

        return $this->exportService->exportPdf($tasks, $this->buildMeta($request), $request->user());
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $tasks = $this->buildQuery($request)->orderBy('due_date')->get();

        return $this->exportService->exportExcel($tasks, $this->buildMeta($request));
    }

    private function buildQuery(Request $request): Builder
    {
        $query = Task::query()->with(['pic.unit', 'assignee.unit']);

        if ($request->filled('department')) {
            $query->where('department', $request->string('department')->toString());
        }

        if ($request->filled('date_from')) {
            $query->whereDate('due_date', '>=', $request->string('date_from')->toString());
        }

        if ($request->filled('date_to')) {
            $query->whereDate('due_date', '<=', $request->string('date_to')->toString());
        }

        if ($request->filled('unit_id')) {
            $unitId = $request->integer('unit_id');
            $query->whereHas('pic', fn ($q) => $q->where('unit_id', $unitId));
        }

        if ($request->filled('pic_id')) {
            $query->where('pic_id', $request->integer('pic_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return $query;
    }

    /**
     * @return array<string, string>
     */
    private function buildMeta(Request $request): array
    {
        $parts = [];
        if ($request->filled('department')) {
            $parts[] = 'Kelompok: ' . ($request->string('department')->toString() === 'rm' ? 'Tugas RM' : 'Tugas IT');
        }
        if ($request->filled('unit_id')) {
            $u = Unit::find($request->integer('unit_id'));
            if ($u) {
                $parts[] = 'Unit: ' . $u->name;
            }
        }
        if ($request->filled('pic_id')) {
            $p = Pic::find($request->integer('pic_id'));
            if ($p) {
                $parts[] = 'PIC: ' . $p->name;
            }
        }
        if ($request->filled('status')) {
            $st = TaskStatus::tryFrom($request->string('status')->toString());
            if ($st) {
                $parts[] = 'Status: ' . $st->label();
            }
        }

        return [
            'filter_summary' => $parts !== [] ? implode(' | ', $parts) : 'Seluruh Data Tugas IT & RM',
            'printed_at' => now()->format('d/m/Y H:i') . ' WIB',
        ];
    }
}
