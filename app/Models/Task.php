<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'task_list_id',
        'user_id',
        'title',
        'description',
        'priority',
        'due_date',
        'status',
        'completed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'priority' => TaskPriority::class,
            'status' => TaskStatus::class,
        ];
    }

    // ── Relationships ────────────────────────────────────────────

    /**
     * Get the user who created this task.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helper Methods ───────────────────────────────────────────

    /**
     * Mark the task as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => TaskStatus::Completed,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark the task as pending.
     */
    public function markAsPending(): void
    {
        $this->update([
            'status' => TaskStatus::Pending,
            'completed_at' => null,
        ]);
    }

    /**
     * Toggle the task status between pending and completed.
     */
    public function toggleStatus(): void
    {
        if ($this->status === TaskStatus::Completed) {
            $this->markAsPending();
        } else {
            $this->markAsCompleted();
        }
    }

    /**
     * Check if the task is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->status !== TaskStatus::Completed
            && $this->due_date->isPast();
    }

    // ── Query Scopes ─────────────────────────────────────────────

    /**
     * Scope to filter pending tasks.
     */
    public function scopePending($query)
    {
        return $query->where('status', TaskStatus::Pending);
    }

    /**
     * Scope to filter completed tasks.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', TaskStatus::Completed);
    }

    /**
     * Scope to filter tasks by priority.
     */
    public function scopeByPriority($query, TaskPriority $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to filter overdue tasks.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', TaskStatus::Pending)
                     ->whereNotNull('due_date')
                     ->where('due_date', '<', now()->toDateString());
    }
}
