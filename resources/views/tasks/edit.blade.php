@extends('layouts.app')

@section('title', 'Edit Task')

@section('styles')
<style>
    .page-header {
        margin-bottom: 2rem;
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

    .page-header .breadcrumb {
        color: var(--text-muted);
        font-size: 0.875rem;
        margin-top: 0.375rem;
    }

    .page-header .breadcrumb a {
        color: var(--accent);
        text-decoration: none;
    }

    .page-header .breadcrumb a:hover {
        color: var(--accent-hover);
        text-decoration: underline;
    }

    .form-card {
        max-width: 640px;
    }

    .form-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
        margin-top: 0.5rem;
    }

    /* Priority Radio Group */
    .priority-group {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .priority-option {
        position: relative;
    }

    .priority-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .priority-option label {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.5rem 1rem;
        border-radius: var(--radius-full);
        border: 2px solid var(--border-color);
        cursor: pointer;
        font-size: 0.8125rem;
        font-weight: 600;
        transition: var(--transition);
        background: var(--bg-input);
        color: var(--text-secondary);
    }

    .priority-option label:hover {
        border-color: var(--text-muted);
    }

    .priority-option input[type="radio"]:checked + label {
        border-color: var(--checked-color, var(--accent));
        background: var(--checked-bg, var(--accent-glow));
        color: var(--checked-color, var(--accent));
    }

    .priority-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    /* Status info */
    .current-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        background: var(--bg-input);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        font-size: 0.875rem;
        color: var(--text-secondary);
    }
</style>
@endsection

@section('content')
    <div class="page-header">
        <h1>✏️ Edit Task</h1>
        <div class="breadcrumb">
            <a href="{{ route('task-lists.tasks.index', $taskList) }}">← Back to Tasks</a>
        </div>
    </div>

    <div class="card form-card">
        <form action="{{ route('task-lists.tasks.update', [$taskList, $task]) }}" method="POST" id="task-edit-form">
            @csrf
            @method('PUT')

            {{-- Title --}}
            <div class="form-group">
                <label for="title" class="form-label">
                    Title <span class="required">*</span>
                </label>
                <input type="text"
                       name="title"
                       id="title"
                       class="form-input"
                       placeholder="e.g. Design homepage wireframe"
                       value="{{ old('title', $task->title) }}"
                       required
                       autofocus>
                @error('title')
                    <div class="form-error">⚠ {{ $message }}</div>
                @enderror
            </div>

            {{-- Description --}}
            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea name="description"
                          id="description"
                          class="form-textarea"
                          placeholder="Add details about this task...">{{ old('description', $task->description) }}</textarea>
                @error('description')
                    <div class="form-error">⚠ {{ $message }}</div>
                @enderror
            </div>

            {{-- Priority --}}
            <div class="form-group">
                <label class="form-label">
                    Priority <span class="required">*</span>
                </label>
                <div class="priority-group">
                    @foreach ($priorities as $priority)
                        <div class="priority-option"
                             style="--checked-color: {{ $priority->color() }}; --checked-bg: {{ $priority->bgColor() }};">
                            <input type="radio"
                                   name="priority"
                                   id="priority-{{ $priority->value }}"
                                   value="{{ $priority->value }}"
                                   {{ old('priority', $task->priority->value) === $priority->value ? 'checked' : '' }}>
                            <label for="priority-{{ $priority->value }}">
                                <span class="priority-dot" style="background: {{ $priority->color() }};"></span>
                                {{ $priority->label() }}
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('priority')
                    <div class="form-error">⚠ {{ $message }}</div>
                @enderror
            </div>

            {{-- Due Date --}}
            <div class="form-group">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date"
                       name="due_date"
                       id="due_date"
                       class="form-input"
                       value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}">
                <div class="form-hint">Opsional — kosongkan jika tidak ada tenggat waktu.</div>
                @error('due_date')
                    <div class="form-error">⚠ {{ $message }}</div>
                @enderror
            </div>

            {{-- Current Status (read-only info) --}}
            <div class="form-group">
                <label class="form-label">Current Status</label>
                <div class="current-status">
                    <span>{{ $task->status->icon() }}</span>
                    <span>{{ $task->status->label() }}</span>
                    @if ($task->completed_at)
                        <span class="text-muted text-sm">— completed {{ $task->completed_at->format('d M Y H:i') }}</span>
                    @endif
                </div>
                <div class="form-hint">Gunakan tombol toggle di halaman daftar tugas untuk mengubah status.</div>
            </div>

            {{-- Actions --}}
            <div class="form-actions">
                <a href="{{ route('task-lists.tasks.index', $taskList) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" id="btn-update-task">
                    💾 Update Task
                </button>
            </div>
        </form>
    </div>
@endsection
