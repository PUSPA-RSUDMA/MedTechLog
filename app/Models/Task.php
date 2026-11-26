<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ComplexityLevel;
use App\Enums\PriorityLevel;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Task extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'department',
        'target_id',
        'parent_task_id',
        'work_category_id',
        'assigned_to',
        'pic_id',
        'created_by',
        'title',
        'description',
        'is_incidental',
        'estimated_hours',
        'estimated_start_date',
        'due_date',
        'complexity',
        'estimated_cost',
        'estimated_output',
        'output_unit',
        'actual_hours',
        'actual_output',
        'actual_completed_at',
        'reminded_h2_at',
        'reminded_h1_at',
        'progress_percent',
        'priority',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_incidental' => 'boolean',
            'estimated_hours' => 'decimal:2',
            'estimated_start_date' => 'date',
            'due_date' => 'date',
            'complexity' => ComplexityLevel::class,
            'estimated_cost' => 'decimal:2',
            'estimated_output' => 'decimal:2',
            'actual_hours' => 'decimal:2',
            'actual_output' => 'decimal:2',
            'actual_completed_at' => 'datetime',
            'reminded_h2_at' => 'datetime',
            'reminded_h1_at' => 'datetime',
            'progress_percent' => 'integer',
            'priority' => PriorityLevel::class,
            'status' => TaskStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $task): void {
            if (empty($task->code)) {
                $year = now()->format('Y');
                $prefix = "TSK-{$year}-";
                $last = self::query()
                    ->where('code', 'like', $prefix . '%')
                    ->orderByDesc('id')
                    ->value('code');

                $nextNum = 1;
                if ($last !== null && preg_match('/(\d+)$/', $last, $matches)) {
                    $nextNum = ((int) $matches[1]) + 1;
                }

                $task->code = $prefix . str_pad((string) $nextNum, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(Target::class);
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_task_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(self::class, 'parent_task_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(WorkCategory::class, 'work_category_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(Pic::class);
    }

    public function dueDateHistories(): HasMany
    {
        return $this->hasMany(TaskDueDateHistory::class)->orderByDesc('created_at')->orderByDesc('id');
    }

    public function getDisplayPicNameAttribute(): string
    {
        return $this->pic?->name ?? $this->assignee?->name ?? '-';
    }

    public function getDisplayUnitNameAttribute(): string
    {
        return $this->pic?->unit?->name ?? $this->assignee?->unit?->name ?? '-';
    }

    public function getDepartmentLabelAttribute(): string
    {
        return $this->department === 'rm' ? 'Tugas RM' : 'Tugas IT';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function workLogs(): HasMany
    {
        return $this->hasMany(WorkLog::class)->orderByDesc('log_date')->orderByDesc('id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(TaskReview::class)->orderByDesc('reviewed_at');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable')->latest();
    }

    /**
     * Indikator Kesehatan Jadwal Otomatis:
     * - completed: status === Completed
     * - overdue: belum selesai & today > due_date
     * - at_risk: belum selesai, sisa waktu <= 20% total durasi rencana (atau sisa <= 2 hari) namun progress_percent < 75%
     * - on_track: kondisi normal lainnya
     *
     * @return array{key: string, label: string, color: string}
     */
    public function getScheduleHealthAttribute(): array
    {
        if ($this->status === TaskStatus::Completed) {
            return [
                'key' => 'completed',
                'label' => 'Selesai',
                'color' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            ];
        }

        $today = now()->startOfDay();
        $due = $this->due_date?->copy()->startOfDay();
        $start = $this->estimated_start_date?->copy()->startOfDay();

        if ($due !== null && $today->gt($due)) {
            return [
                'key' => 'overdue',
                'label' => 'Overdue',
                'color' => 'bg-rose-100 text-rose-800 border-rose-200',
            ];
        }

        if ($due !== null) {
            $remainingDays = (int) $today->diffInDays($due, false);
            $totalDays = $start !== null ? max(1, (int) $start->diffInDays($due)) : max(1, $remainingDays);
            $remainingRatio = $remainingDays / $totalDays;

            if (($remainingRatio <= 0.20 || $remainingDays <= 2) && $this->progress_percent < 75) {
                return [
                    'key' => 'at_risk',
                    'label' => 'At Risk',
                    'color' => 'bg-amber-100 text-amber-800 border-amber-200',
                ];
            }
        }

        return [
            'key' => 'on_track',
            'label' => 'On Track',
            'color' => 'bg-teal-100 text-teal-800 border-teal-200',
        ];
    }

    public function getDurationVarianceHoursAttribute(): float
    {
        return round((float) $this->actual_hours - (float) $this->estimated_hours, 2);
    }

    public function getDurationVariancePercentAttribute(): float
    {
        $est = (float) $this->estimated_hours;
        if ($est <= 0) {
            return 0.0;
        }

        return round((((float) $this->actual_hours - $est) / $est) * 100, 1);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        $visibleIds = $user->visibleUserIds() ?? [];

        return $query->whereIn('assigned_to', $visibleIds);
    }
}
