<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $visibleIds = $user->visibleUserIds() ?? [];

        return in_array($task->assigned_to, $visibleIds, true) || $task->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSupervisor()) {
            $visibleIds = $user->visibleUserIds() ?? [];

            return in_array($task->assigned_to, $visibleIds, true);
        }

        return $task->assigned_to === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->isAdmin();
    }

    public function review(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSupervisor()) {
            $visibleIds = $user->visibleUserIds() ?? [];

            return in_array($task->assigned_to, $visibleIds, true) && $task->assigned_to !== $user->id;
        }

        return false;
    }

    public function submitReview(User $user, Task $task): bool
    {
        return $task->assigned_to === $user->id || $user->isAdmin();
    }
}
