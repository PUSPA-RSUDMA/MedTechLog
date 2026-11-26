<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Support\Collection;

class VarianceAnalysisService
{
    /**
     * Analyze variance across a collection of tasks.
     *
     * @param  Collection<int, Task>  $tasks
     * @return array<string, mixed>
     */
    public function analyze(Collection $tasks): array
    {
        $taskRows = $tasks->map(function (Task $task): array {
            $estHours = (float) $task->estimated_hours;
            $actHours = (float) $task->actual_hours;
            $diffHours = round($actHours - $estHours, 2);
            $deviationPct = $estHours > 0 ? round(($diffHours / $estHours) * 100, 1) : 0.0;

            $estOutput = (float) $task->estimated_output;
            $actOutput = (float) $task->actual_output;
            $outputPct = $estOutput > 0 ? round(($actOutput / $estOutput) * 100, 1) : 0.0;

            $scheduleStatus = $this->computeSchedulePunctuality($task);

            return [
                'task' => $task,
                'estimated_hours' => $estHours,
                'actual_hours' => $actHours,
                'diff_hours' => $diffHours,
                'deviation_percent' => $deviationPct,
                'estimated_output' => $estOutput,
                'actual_output' => $actOutput,
                'output_percent' => $outputPct,
                'schedule_status' => $scheduleStatus,
            ];
        });

        $totalEstHours = round((float) $taskRows->sum('estimated_hours'), 2);
        $totalActHours = round((float) $taskRows->sum('actual_hours'), 2);
        $totalDiffHours = round($totalActHours - $totalEstHours, 2);
        $overallDeviationPct = $totalEstHours > 0 ? round(($totalDiffHours / $totalEstHours) * 100, 1) : 0.0;

        // Estimation accuracy index (100% minus average absolute deviation, clamped 0..100)
        $loggedTasks = $taskRows->filter(fn (array $r) => $r['actual_hours'] > 0 && $r['estimated_hours'] > 0);
        $avgAbsDeviation = $loggedTasks->isNotEmpty()
            ? (float) $loggedTasks->avg(fn (array $r) => abs($r['deviation_percent']))
            : 0.0;
        $accuracyIndex = round(max(0.0, 100.0 - $avgAbsDeviation), 1);

        $byCategory = $taskRows
            ->groupBy(fn (array $r) => $r['task']->work_category_id)
            ->map(function (Collection $group): array {
                /** @var Task $firstTask */
                $firstTask = $group->first()['task'];
                $est = round((float) $group->sum('estimated_hours'), 2);
                $act = round((float) $group->sum('actual_hours'), 2);
                $diff = round($act - $est, 2);
                $devPct = $est > 0 ? round(($diff / $est) * 100, 1) : 0.0;

                return [
                    'category_name' => $firstTask->category?->name ?? 'Tanpa Kategori',
                    'category_color' => $firstTask->category?->color ?? '#0F766E',
                    'task_count' => $group->count(),
                    'estimated_hours' => $est,
                    'actual_hours' => $act,
                    'diff_hours' => $diff,
                    'deviation_percent' => $devPct,
                ];
            })
            ->sortByDesc(fn (array $row) => abs($row['deviation_percent']))
            ->values();

        $byUser = $taskRows
            ->groupBy(fn (array $r) => $r['task']->assigned_to)
            ->map(function (Collection $group): array {
                /** @var Task $firstTask */
                $firstTask = $group->first()['task'];
                $est = round((float) $group->sum('estimated_hours'), 2);
                $act = round((float) $group->sum('actual_hours'), 2);
                $diff = round($act - $est, 2);
                $devPct = $est > 0 ? round(($diff / $est) * 100, 1) : 0.0;
                $completed = $group->filter(fn (array $r) => $r['task']->status === TaskStatus::Completed)->count();

                return [
                    'user_name' => $firstTask->assignee?->name ?? '-',
                    'unit_name' => $firstTask->assignee?->unit?->name ?? '-',
                    'task_count' => $group->count(),
                    'completed_count' => $completed,
                    'estimated_hours' => $est,
                    'actual_hours' => $act,
                    'diff_hours' => $diff,
                    'deviation_percent' => $devPct,
                ];
            })
            ->sortByDesc('actual_hours')
            ->values();

        $insights = $this->generateInsights($taskRows, $byCategory, $accuracyIndex);

        return [
            'rows' => $taskRows,
            'by_category' => $byCategory,
            'by_user' => $byUser,
            'summary' => [
                'total_tasks' => $taskRows->count(),
                'total_estimated_hours' => $totalEstHours,
                'total_actual_hours' => $totalActHours,
                'total_diff_hours' => $totalDiffHours,
                'overall_deviation_percent' => $overallDeviationPct,
                'accuracy_index' => $accuracyIndex,
            ],
            'insights' => $insights,
        ];
    }

    /**
     * @return array{label: string, color: string, days_diff: int|null}
     */
    private function computeSchedulePunctuality(Task $task): array
    {
        $due = $task->due_date?->copy()->startOfDay();
        if ($due === null) {
            return ['label' => '-', 'color' => 'bg-slate-100 text-slate-600 border-slate-200', 'days_diff' => null];
        }

        if ($task->status === TaskStatus::Completed && $task->actual_completed_at !== null) {
            $completed = $task->actual_completed_at->copy()->startOfDay();
            $diff = (int) $due->diffInDays($completed, false); // negative = early, 0 = on time, positive = late

            if ($diff < 0) {
                return [
                    'label' => 'Lebih Cepat ' . abs($diff) . ' Hari',
                    'color' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                    'days_diff' => $diff,
                ];
            }

            if ($diff === 0) {
                return [
                    'label' => 'Tepat Waktu',
                    'color' => 'bg-teal-100 text-teal-800 border-teal-200',
                    'days_diff' => 0,
                ];
            }

            return [
                'label' => 'Terlambat ' . $diff . ' Hari',
                'color' => 'bg-rose-100 text-rose-800 border-rose-200',
                'days_diff' => $diff,
            ];
        }

        $today = now()->startOfDay();
        if ($today->gt($due)) {
            $lateDays = (int) $due->diffInDays($today);

            return [
                'label' => 'Terlambat ' . $lateDays . ' Hari (Belum Selesai)',
                'color' => 'bg-rose-100 text-rose-800 border-rose-200',
                'days_diff' => $lateDays,
            ];
        }

        return [
            'label' => 'Dalam Jadwal',
            'color' => 'bg-sky-100 text-sky-800 border-sky-200',
            'days_diff' => null,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $taskRows
     * @param  Collection<int, array<string, mixed>>  $byCategory
     * @return list<string>
     */
    private function generateInsights(Collection $taskRows, Collection $byCategory, float $accuracyIndex): array
    {
        $insights = [];

        if ($taskRows->isEmpty()) {
            return ['Belum ada data tugas pada filter yang dipilih untuk dianalisis.'];
        }

        $highestDevCat = $byCategory->first();
        if ($highestDevCat !== null && abs($highestDevCat['deviation_percent']) > 0) {
            $direction = $highestDevCat['deviation_percent'] > 0 ? 'melebihi estimasi' : 'lebih cepat dari estimasi';
            $insights[] = sprintf(
                'Kategori "%s" memiliki deviasi estimasi tertinggi sebesar %+.1f%% (%s).',
                $highestDevCat['category_name'],
                $highestDevCat['deviation_percent'],
                $direction
            );
        }

        $overrunCount = $taskRows->filter(fn (array $r) => $r['diff_hours'] > 0)->count();
        if ($overrunCount > 0) {
            $insights[] = sprintf(
                '%d dari %d tugas (%d%%) membutuhkan waktu aktual lebih lama dari estimasi awal.',
                $overrunCount,
                $taskRows->count(),
                (int) round(($overrunCount / $taskRows->count()) * 100)
            );
        }

        $insights[] = sprintf(
            'Indeks akurasi estimasi waktu saat ini berada pada %.1f%% (%s).',
            $accuracyIndex,
            $accuracyIndex >= 85 ? 'Sangat Baik' : ($accuracyIndex >= 70 ? 'Cukup Baik' : 'Perlu Kalibrasi Estimasi')
        );

        return $insights;
    }
}
