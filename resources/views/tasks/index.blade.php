@extends('layouts.app')

@section('title', 'Tasks — List #' . $taskList)

@section('styles')
<style>
    /* ── Page Header ───────────────────────────────────── */
    .page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .page-header h1 {
        font-size: 1.75rem;
        font-weight: 800;
        letter-spacing: -0.5px;
        background: linear-gradient(135deg, var(--text-primary), var(--text-secondary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .page-header p {
        color: var(--text-muted);
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    /* ── Stats Grid ────────────────────────────────────── */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 1.25rem;
        text-align: center;
        transition: var(--transition);
    }

    .stat-card:hover {
        border-color: var(--text-muted);
        transform: translateY(-2px);
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 0.375rem;
    }

    .stat-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-total .stat-value { color: var(--accent); }
    .stat-completed .stat-value { color: var(--success); }
    .stat-pending .stat-value { color: var(--warning); }
    .stat-overdue .stat-value { color: var(--danger); }

    /* ── Task List ──────────────────────────────────────── */
    .task-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .task-item {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: var(--transition);
        animation: fadeIn 0.3s ease-out;
    }

    .task-item:hover {
        border-color: var(--text-muted);
        background: var(--bg-card-hover);
        box-shadow: var(--shadow);
    }

    .task-item.completed {
        opacity: 0.6;
    }

    .task-item.overdue {
        border-left: 3px solid var(--danger);
    }

    /* ── Checkbox Toggle ───────────────────────────────── */
    .task-checkbox {
        flex-shrink: 0;
    }

    .task-checkbox form {
        display: flex;
    }

    .checkbox-btn {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 2px solid var(--border-color);
        background: transparent;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        color: transparent;
        padding: 0;
    }

    .checkbox-btn:hover {
        border-color: var(--success);
        background: var(--success-bg);
    }

    .checkbox-btn.checked {
        background: var(--success);
        border-color: var(--success);
        color: white;
    }

    /* ── Task Info ──────────────────────────────────────── */
    .task-info {
        flex: 1;
        min-width: 0;
    }

    .task-title {
        font-size: 0.9375rem;
        font-weight: 600;
        color: var(--text-primary);
        text-decoration: none;
        transition: var(--transition);
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .task-title:hover {
        color: var(--accent-hover);
    }

    .completed .task-title {
        text-decoration: line-through;
        color: var(--text-muted);
    }

    .task-meta {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-top: 0.375rem;
        flex-wrap: wrap;
    }

    .task-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .task-meta-item.overdue-text {
        color: var(--danger);
        font-weight: 600;
    }

    /* ── Priority Badge ────────────────────────────────── */
    .priority-badge {
        flex-shrink: 0;
    }

    /* ── Task Actions ──────────────────────────────────── */
    .task-actions {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        flex-shrink: 0;
    }

    .task-actions .btn-icon {
        font-size: 0.875rem;
    }

    /* ── Empty State ───────────────────────────────────── */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        animation: fadeIn 0.5s ease-out;
    }

    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-state h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: var(--text-muted);
        font-size: 0.875rem;
        margin-bottom: 1.5rem;
    }

    /* ── Delete Modal ──────────────────────────────────── */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        z-index: 200;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 2rem;
        max-width: 420px;
        width: 90%;
        box-shadow: var(--shadow-lg);
        animation: slideDown 0.2s ease-out;
    }

    .modal h3 {
        font-size: 1.125rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .modal p {
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin-bottom: 1.5rem;
    }

    .modal-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
    }

    @media (max-width: 640px) {
        .task-item {
            flex-wrap: wrap;
        }

        .task-actions {
            width: 100%;
            justify-content: flex-end;
            padding-top: 0.5rem;
            border-top: 1px solid var(--border-color);
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endsection

@section('content')
    {{-- Page Header --}}
    <div class="page-header">
        <div>
            <h1>📋 Task List #{{ $taskList }}</h1>
            <p>Manage your tasks efficiently</p>
        </div>
        <a href="{{ route('task-lists.tasks.create', $taskList) }}" class="btn btn-primary" id="btn-add-task">
            <span>＋</span> Add Task
        </a>
    </div>

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card stat-total">
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-card stat-completed">
            <div class="stat-value">{{ $stats['completed'] }}</div>
            <div class="stat-label">Completed</div>
        </div>
        <div class="stat-card stat-pending">
            <div class="stat-value">{{ $stats['pending'] }}</div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card stat-overdue">
            <div class="stat-value">{{ $stats['overdue'] }}</div>
            <div class="stat-label">Overdue</div>
        </div>
    </div>

    {{-- Task List --}}
    @if ($tasks->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">📝</div>
            <h3>Belum ada tugas</h3>
            <p>Mulai dengan membuat tugas pertama di list ini.</p>
            <a href="{{ route('task-lists.tasks.create', $taskList) }}" class="btn btn-primary">
                <span>＋</span> Add Task
            </a>
        </div>
    @else
        <div class="task-list">
            @foreach ($tasks as $task)
                <div class="task-item {{ $task->status === \App\Enums\TaskStatus::Completed ? 'completed' : '' }} {{ $task->isOverdue() ? 'overdue' : '' }}" id="task-{{ $task->id }}">
                    {{-- Toggle Checkbox --}}
                    <div class="task-checkbox">
                        <form action="{{ route('task-lists.tasks.toggle', [$taskList, $task]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="checkbox-btn {{ $task->status === \App\Enums\TaskStatus::Completed ? 'checked' : '' }}"
                                    title="{{ $task->status === \App\Enums\TaskStatus::Completed ? 'Kembalikan ke pending' : 'Tandai selesai' }}">
                                {{ $task->status === \App\Enums\TaskStatus::Completed ? '✓' : '' }}
                            </button>
                        </form>
                    </div>

                    {{-- Task Info --}}
                    <div class="task-info">
                        <a href="{{ route('task-lists.tasks.show', [$taskList, $task]) }}" class="task-title">
                            {{ $task->title }}
                        </a>
                        <div class="task-meta">
                            {{-- Priority --}}
                            <span class="badge priority-badge" style="background: {{ $task->priority->bgColor() }}; color: {{ $task->priority->color() }};">
                                <span class="badge-dot" style="background: {{ $task->priority->color() }};"></span>
                                {{ $task->priority->label() }}
                            </span>

                            {{-- Due Date --}}
                            @if ($task->due_date)
                                <span class="task-meta-item {{ $task->isOverdue() ? 'overdue-text' : '' }}">
                                    📅 {{ $task->due_date->format('d M Y') }}
                                    @if ($task->isOverdue())
                                        (Overdue!)
                                    @endif
                                </span>
                            @endif

                            {{-- Status --}}
                            @if ($task->status === \App\Enums\TaskStatus::Completed && $task->completed_at)
                                <span class="task-meta-item" style="color: var(--success);">
                                    ✅ {{ $task->completed_at->format('d M Y H:i') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="task-actions">
                        <a href="{{ route('task-lists.tasks.edit', [$taskList, $task]) }}" class="btn-icon" title="Edit">
                            ✏️
                        </a>
                        <button type="button" class="btn-icon" title="Hapus" onclick="confirmDelete({{ $task->id }}, '{{ addslashes($task->title) }}')">
                            🗑️
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    <div class="modal-overlay" id="deleteModal">
        <div class="modal">
            <h3>⚠️ Konfirmasi Hapus</h3>
            <p>Apakah kamu yakin ingin menghapus tugas "<strong id="deleteTaskName"></strong>"? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Batal</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function confirmDelete(taskId, taskName) {
        document.getElementById('deleteTaskName').textContent = taskName;
        document.getElementById('deleteForm').action = '/task-lists/{{ $taskList }}/tasks/' + taskId;
        document.getElementById('deleteModal').classList.add('active');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('active');
    }

    // Close modal on overlay click
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeDeleteModal();
    });
</script>
@endsection
