<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Determine if the user is an administrator.
     * SRS-011: Manajemen akun oleh admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Get all task lists owned by this user.
     * SRS-002: Membuat list/project
     */
    public function taskLists(): HasMany
    {
        return $this->hasMany(TaskList::class, 'owner_id');
    }

    /**
     * Get all task lists shared with this user as a member/collaborator.
     * SRS-008, SRS-009: Kolaborasi dalam list/project
     */
    public function sharedTaskLists(): BelongsToMany
    {
        return $this->belongsToMany(TaskList::class, 'task_list_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get all task lists the user has access to (owned or shared).
     */
    public function accessibleTaskLists()
    {
        $ownedIds = $this->taskLists()->pluck('id');
        $sharedIds = $this->sharedTaskLists()->pluck('task_lists.id');
        $allIds = $ownedIds->merge($sharedIds)->unique();

        return TaskList::whereIn('id', $allIds);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the tasks created by this user.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
