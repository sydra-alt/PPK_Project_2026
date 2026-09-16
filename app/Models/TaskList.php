<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model TaskList — mewakili list/project milik user.
 */
class TaskList extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'owner_id',
    ];

    /**
     * Get the owner (user) of this list.
     * SRS-002: Membuat list/project (otomatis menjadi pemilik)
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the tasks belonging to this list.
     * SRS-004: Membuat tugas dalam list
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'task_list_id');
    }

    /**
     * Get the members/collaborators invited to this list.
     * SRS-008: Kolaborasi dalam list/project
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_list_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Check if a specific user is the owner of this list.
     */
    public function isOwner(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;
        return (int) $this->owner_id === (int) $userId;
    }

    /**
     * Check if a specific user is a member of this list.
     */
    public function hasMember(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;
        return $this->members()->where('users.id', $userId)->exists();
    }

    /**
     * Check if a user can access (owner or member).
     */
    public function canAccess(User $user): bool
    {
        return $this->isOwner($user) || $this->hasMember($user) || $user->isAdmin();
    }

    /**
     * Get progress statistics for this task list.
     * SRS-010: Memantau progres list/project
     */
    public function progressStats(): array
    {
        $tasks = $this->tasks()->get();
        $total = $tasks->count();
        $completed = $tasks->where('status', TaskStatus::Completed)->count();
        $pending = $tasks->where('status', TaskStatus::Pending)->count();
        $overdue = $tasks->filter(fn (Task $t) => $t->isOverdue())->count();
        $percentage = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'overdue' => $overdue,
            'percentage' => $percentage,
        ];
    }
}
