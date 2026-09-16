<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\TaskList;
use Illuminate\View\View;

class TaskListProgressController extends Controller
{
    /**
     * Display progress monitoring dashboard for a task list.
     * SRS-010: Memantau progres list/project
     */
    public function show(TaskList $list): View
    {
        $this->authorize('viewProgress', $list);

        $list->load(['owner', 'members', 'tasks.user']);

        $tasks = $list->tasks;
        $total = $tasks->count();
        $completed = $tasks->where('status', TaskStatus::Completed)->count();
        $pending = $tasks->where('status', TaskStatus::Pending)->count();
        $overdue = $tasks->filter(fn ($t) => $t->isOverdue())->count();
        $percentage = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        // Breakdown by priority
        $priorityStats = [
            'urgent' => [
                'total' => $tasks->where('priority', TaskPriority::Urgent)->count(),
                'completed' => $tasks->where('priority', TaskPriority::Urgent)->where('status', TaskStatus::Completed)->count(),
            ],
            'high' => [
                'total' => $tasks->where('priority', TaskPriority::High)->count(),
                'completed' => $tasks->where('priority', TaskPriority::High)->where('status', TaskStatus::Completed)->count(),
            ],
            'medium' => [
                'total' => $tasks->where('priority', TaskPriority::Medium)->count(),
                'completed' => $tasks->where('priority', TaskPriority::Medium)->where('status', TaskStatus::Completed)->count(),
            ],
            'low' => [
                'total' => $tasks->where('priority', TaskPriority::Low)->count(),
                'completed' => $tasks->where('priority', TaskPriority::Low)->where('status', TaskStatus::Completed)->count(),
            ],
        ];

        return view('lists.progress', compact(
            'list',
            'tasks',
            'total',
            'completed',
            'pending',
            'overdue',
            'percentage',
            'priorityStats'
        ));
    }
}
