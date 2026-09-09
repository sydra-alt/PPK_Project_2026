@extends('layouts.app')

@section('title', $task->title)

@section('styles')
<style>
    .page-header {
        margin-bottom: 2rem;
    }

    .page-header .breadcrumb {
        color: var(--text-muted);
        font-size: 0.875rem;
        margin-bottom: 0.75rem;
    }

    .page-header .breadcrumb a {
        color: var(--accent);
        text-decoration: none;
    }

    .page-header .breadcrumb a:hover {
        color: var(--accent-hover);
        text-decoration: underline;
    }

    .page-header h1 {
        font-size: 1.75rem;
        font-weight: 800;
        letter-spacing: -0.5px;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .detail-card {
        max-width: 720px;
    }

    .detail-section {
        padding: 1.25rem 0;
        border-bottom: 1px solid var(--border-color);
    }

    .detail-section:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .detail-section:first-child {
        padding-top: 0;
    }

    .detail-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }

    .detail-value {
        font-size: 0.9375rem;
        color: var(--text-primary);
        line-height: 1.7;
    }

    .detail-value.empty {
        color: var(--text-muted);
        font-style: italic;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1.5rem;
    }

    .action-bar {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-color);
    }

    .status-display {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: var(--radius-sm);
        font-weight: 600;
        font-size: 0.875rem;
    }

    .overdue-banner {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        background: var(--danger-bg);
        border: 1px solid rgba(239, 68, 68, 0.2);
        border-radius: var(--radius-sm);
        color: var(--danger);
        font-size: 0.875rem;
        font-weight: 600;
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
</style>
@endsection

@section('content')
    <div class="page-header">
        <div class="breadcrumb">
            <a href="{{ route('task-lists.tasks.index', $taskList) }}">← Back to Tasks</a>
        </div>
        <h1>
            <span>{{ $task->status->icon() }}</span>
            {{ $task->title }}
        </h1>
    </div>

    {{-- Overdue Banner --}}
    @if ($task->isOverdue())
        <div class="overdue-banner">
            <span>🚨</span>
            Tugas ini sudah melewati tenggat waktu!
        </div>
    @endif

    <div class="card detail-card">
        {{-- Description --}}
        <div class="detail-section">
            <div class="detail-label">Description</div>
            <div class="detail-value {{ !$task->description ? 'empty' : '' }}">
                {!! $task->description ? nl2br(e($task->description)) : 'Tidak ada deskripsi.' !!}
            </div>
        </div>

        {{-- Detail Grid --}}
        <div class="detail-section">
            <div class="detail-grid">
                {{-- Priority --}}
                <div>
                    <div class="detail-label">Priority</div>
                    <span class="badge" style="background: {{ $task->priority->bgColor() }}; color: {{ $task->priority->color() }};">
                        <span class="badge-dot" style="background: {{ $task->priority->color() }};"></span>
                        {{ $task->priority->label() }}
                    </span>
                </div>

                {{-- Status --}}
                <div>
                    <div class="detail-label">Status</div>
                    <span class="status-display" style="background: {{ $task->status->bgColor() }}; color: {{ $task->status->color() }};">
                        {{ $task->status->icon() }} {{ $task->status->label() }}
                    </span>
                </div>

                {{-- Due Date --}}
                <div>
                    <div class="detail-label">Due Date</div>
                    <div class="detail-value {{ !$task->due_date ? 'empty' : '' }} {{ $task->isOverdue() ? 'overdue-text' : '' }}" style="{{ $task->isOverdue() ? 'color: var(--danger); font-weight: 600;' : '' }}">
                        {{ $task->due_date ? $task->due_date->format('d M Y') : 'Tidak ada deadline' }}
                        @if ($task->isOverdue())
                            (Overdue!)
                        @endif
                    </div>
                </div>

                {{-- Completed At --}}
                @if ($task->completed_at)
                <div>
                    <div class="detail-label">Completed At</div>
                    <div class="detail-value" style="color: var(--success);">
                        {{ $task->completed_at->format('d M Y, H:i') }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Timestamps --}}
        <div class="detail-section">
            <div class="detail-grid">
                <div>
                    <div class="detail-label">Created</div>
                    <div class="detail-value text-sm text-muted">{{ $task->created_at->format('d M Y, H:i') }}</div>
                </div>
                <div>
                    <div class="detail-label">Last Updated</div>
                    <div class="detail-value text-sm text-muted">{{ $task->updated_at->format('d M Y, H:i') }}</div>
                </div>
            </div>
        </div>

        {{-- Action Bar --}}
        <div class="action-bar">
            <form action="{{ route('task-lists.tasks.toggle', [$taskList, $task]) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-primary btn-sm">
                    @if ($task->status === \App\Enums\TaskStatus::Completed)
                        ⏳ Mark as Pending
                    @else
                        ✅ Mark as Completed
                    @endif
                </button>
            </form>

            <a href="{{ route('task-lists.tasks.edit', [$taskList, $task]) }}" class="btn btn-secondary btn-sm">
                ✏️ Edit
            </a>

            <button type="button" class="btn btn-danger btn-sm" onclick="document.getElementById('deleteModal').classList.add('active')">
                🗑️ Delete
            </button>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal-overlay" id="deleteModal">
        <div class="modal">
            <h3>⚠️ Konfirmasi Hapus</h3>
            <p>Apakah kamu yakin ingin menghapus tugas "<strong>{{ $task->title }}</strong>"? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('deleteModal').classList.remove('active')">Batal</button>
                <form action="{{ route('task-lists.tasks.destroy', [$taskList, $task]) }}" method="POST">
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
    // Close modal on overlay click
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') document.getElementById('deleteModal').classList.remove('active');
    });
</script>
@endsection
