<?php

namespace App\Policies;

use App\Models\TaskList;
use App\Models\User;

class TaskListPolicy
{
    /**
     * Determine whether the user can view the model.
     * Only the owner can view their own list.
     */
    public function view(User $user, TaskList $taskList): bool
    {
        return $user->id === $taskList->owner_id;
    }

    /**
     * Determine whether the user can update the model.
     * Only the owner can update their list.
     */
    public function update(User $user, TaskList $taskList): bool
    {
        return $user->id === $taskList->owner_id;
    }

    /**
     * Determine whether the user can delete the model.
     * Only the owner can delete their list.
     */
    public function delete(User $user, TaskList $taskList): bool
    {
        return $user->id === $taskList->owner_id;
    }
}
