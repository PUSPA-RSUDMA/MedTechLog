<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PriorityLevel;
use App\Enums\TargetStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Target extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'title',
        'description',
        'work_period_id',
        'unit_id',
        'work_category_id',
        'owner_id',
        'created_by',
        'output_unit',
        'target_value',
        'achieved_value',
        'weight',
        'priority',
        'status',
        'start_date',
        'end_date',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_value' => 'decimal:2',
            'achieved_value' => 'decimal:2',
            'weight' => 'decimal:2',
            'priority' => PriorityLevel::class,
            'status' => TargetStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $target): void {
            if (empty($target->code)) {
                $year = now()->format('Y');
                $prefix = "TGT-{$year}-";
                $last = self::query()
                    ->where('code', 'like', $prefix . '%')
                    ->orderByDesc('id')
                    ->value('code');

                $nextNum = 1;
                if ($last !== null && preg_match('/(\d+)$/', $last, $matches)) {
                    $nextNum = ((int) $matches[1]) + 1;
                }

                $target->code = $prefix . str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function workPeriod(): BelongsTo
    {
        return $this->belongsTo(WorkPeriod::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(WorkCategory::class, 'work_category_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function getAchievementPercentAttribute(): float
    {
        $targetVal = (float) $this->target_value;
        if ($targetVal <= 0) {
            return 0.0;
        }

        return round(min(100.0, ((float) $this->achieved_value / $targetVal) * 100), 1);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        $visibleIds = $user->visibleUserIds() ?? [];

        return $query->where(function (Builder $q) use ($user, $visibleIds): void {
            $q->whereIn('owner_id', $visibleIds);
            if ($user->isSupervisor() && $user->unit_id !== null) {
                $q->orWhere('unit_id', $user->unit_id);
            }
        });
    }
}
