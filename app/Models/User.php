<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'name',
        'email',
        'password',
        'role',
        'unit_id',
        'supervisor_id',
        'position',
        'is_active',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supervisor_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(self::class, 'supervisor_id');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(Target::class, 'owner_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function workLogs(): HasMany
    {
        return $this->hasMany(WorkLog::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isSupervisor(): bool
    {
        return $this->role === UserRole::Supervisor;
    }

    public function isStaff(): bool
    {
        return $this->role === UserRole::Staff;
    }

    /**
     * Returns user IDs visible to this user based on RBAC scope.
     * Returns null for Admin (unrestricted).
     *
     * @return list<int>|null
     */
    public function visibleUserIds(): ?array
    {
        if ($this->isAdmin()) {
            return null;
        }

        if ($this->isSupervisor()) {
            $subIds = self::query()
                ->where(function ($q): void {
                    $q->where('supervisor_id', $this->id);
                    if ($this->unit_id !== null) {
                        $q->orWhere('unit_id', $this->unit_id);
                    }
                })
                ->pluck('id')
                ->all();

            return array_values(array_unique(array_merge([$this->id], $subIds)));
        }

        return [$this->id];
    }
}
