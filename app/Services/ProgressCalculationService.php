<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TargetStatus;
use App\Enums\TaskStatus;
use App\Models\Target;
use App\Models\Task;

class ProgressCalculationService
{
    /**
     * Recalculate a task's actual_hours, actual_output, progress_percent, and status from its work logs,
     * then cascade the recalculation to its parent target (if linked).
     */
    public function recalculateTask(Task $task, ?TaskStatus $explicitStatus = null): Task
    {
        $aggregates = $task->workLogs()
            ->reorder()
            ->selectRaw('COALESCE(SUM(duration_minutes), 0) as total_minutes, COALESCE(SUM(output_achieved), 0) as total_output')
            ->first();

        $totalMinutes = (int) ($aggregates->total_minutes ?? 0);
        $totalOutput = (float) ($aggregates->total_output ?? 0);
        $actualHours = round($totalMinutes / 60, 2);

        $status = $explicitStatus ?? $task->status;

        $estimatedOutput = (float) $task->estimated_output > 0 ? (float) $task->estimated_output : 100.0;
        $progress = (int) min(100, round(($totalOutput / $estimatedOutput) * 100));

        // Auto-complete immediately when reaching 100% (no verification required)
        if ($progress >= 100 || $status === TaskStatus::Completed) {
            $progress = 100;
            $status = TaskStatus::Completed;
        }

        $completedAt = $task->actual_completed_at;
        if ($status === TaskStatus::Completed && $completedAt === null) {
            $completedAt = now();
        } elseif ($status !== TaskStatus::Completed) {
            $completedAt = null;
        }

        $task->update([
            'actual_hours' => $actualHours,
            'actual_output' => $totalOutput,
            'progress_percent' => $progress,
            'status' => $status,
            'actual_completed_at' => $completedAt,
        ]);

        if ($task->target_id !== null) {
            $target = $task->target()->first();
            if ($target !== null) {
                $this->recalculateTarget($target);
            }
        }

        return $task->fresh();
    }

    /**
     * Recalculate a target's achieved_value based on linked tasks' actual_output (or weighted task progress).
     */
    public function recalculateTarget(Target $target): Target
    {
        $tasks = $target->tasks()->get(['actual_output', 'estimated_output', 'progress_percent', 'status']);

        if ($tasks->isEmpty()) {
            return $target;
        }

        $sumActualOutput = (float) $tasks->sum('actual_output');
        $achieved = $sumActualOutput;

        if ($achieved <= 0 && (float) $target->target_value > 0) {
            $avgProgress = (float) $tasks->avg('progress_percent');
            $achieved = round(((float) $target->target_value) * ($avgProgress / 100), 2);
        }

        $achieved = min((float) $target->target_value, round($achieved, 2));

        $status = $target->status;
        if ($status === TargetStatus::Active && (float) $target->target_value > 0 && $achieved >= (float) $target->target_value) {
            $allCompleted = $tasks->every(fn (Task $t) => $t->status === TaskStatus::Completed);
            if ($allCompleted) {
                $status = TargetStatus::Completed;
            }
        }

        $target->update([
            'achieved_value' => $achieved,
            'status' => $status,
        ]);

        return $target->fresh();
    }
}
