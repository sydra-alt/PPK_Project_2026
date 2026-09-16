<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Programmer 2: Task management design tokens --}}
        <style>
            :root {
                --bg-primary: #0f172a;
                --bg-secondary: #1e293b;
                --bg-card: #1e293b;
                --bg-card-hover: #253349;
                --bg-input: #0f172a;
                --border-color: #334155;
                --border-focus: #6366f1;
                --text-primary: #f1f5f9;
                --text-secondary: #94a3b8;
                --text-muted: #64748b;
                --accent: #6366f1;
                --accent-hover: #818cf8;
                --accent-glow: rgba(99, 102, 241, 0.3);
                --success: #22c55e;
                --success-bg: rgba(34, 197, 94, 0.1);
                --warning: #f59e0b;
                --warning-bg: rgba(245, 158, 11, 0.1);
                --danger: #ef4444;
                --danger-bg: rgba(239, 68, 68, 0.1);
                --danger-hover: #dc2626;
                --radius: 12px;
                --radius-sm: 8px;
                --radius-full: 9999px;
                --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -2px rgba(0, 0, 0, 0.2);
                --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.4), 0 4px 6px -4px rgba(0, 0, 0, 0.3);
                --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            }

            /* ── Buttons ───────────────────────────────────────── */
            .btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.625rem 1.25rem;
                border-radius: var(--radius-sm);
                font-size: 0.875rem;
                font-weight: 600;
                font-family: inherit;
                cursor: pointer;
                transition: var(--transition);
                border: 1px solid transparent;
                text-decoration: none;
                line-height: 1.4;
            }

            .btn:active { transform: scale(0.97); }

            .btn-primary {
                background: var(--accent);
                color: white;
                box-shadow: 0 0 0 0 var(--accent-glow);
            }

            .btn-primary:hover {
                background: var(--accent-hover);
                box-shadow: 0 0 20px 0 var(--accent-glow);
            }

            .btn-secondary {
                background: var(--bg-secondary);
                color: var(--text-secondary);
                border-color: var(--border-color);
            }

            .btn-secondary:hover {
                background: var(--bg-card-hover);
                color: var(--text-primary);
                border-color: var(--text-muted);
            }

            .btn-danger {
                background: var(--danger-bg);
                color: var(--danger);
                border-color: rgba(239, 68, 68, 0.2);
            }

            .btn-danger:hover {
                background: var(--danger);
                color: white;
            }

            .btn-sm {
                padding: 0.375rem 0.75rem;
                font-size: 0.8125rem;
            }

            .btn-icon {
                padding: 0.5rem;
                border-radius: var(--radius-sm);
                background: transparent;
                border: 1px solid transparent;
                color: var(--text-muted);
                cursor: pointer;
                transition: var(--transition);
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .btn-icon:hover {
                background: var(--bg-card-hover);
                color: var(--text-primary);
            }

            /* ── Forms ─────────────────────────────────────────── */
            .form-group { margin-bottom: 1.5rem; }

            .form-label {
                display: block;
                font-size: 0.875rem;
                font-weight: 600;
                color: var(--text-secondary);
                margin-bottom: 0.5rem;
                letter-spacing: 0.025em;
            }

            .form-label .required {
                color: var(--danger);
                margin-left: 0.25rem;
            }

            .form-input,
            .form-textarea,
            .form-select {
                width: 100%;
                padding: 0.75rem 1rem;
                background: var(--bg-input);
                border: 1px solid var(--border-color);
                border-radius: var(--radius-sm);
                color: var(--text-primary);
                font-size: 0.9375rem;
                font-family: inherit;
                transition: var(--transition);
                outline: none;
            }

            .form-input:focus,
            .form-textarea:focus,
            .form-select:focus {
                border-color: var(--border-focus);
                box-shadow: 0 0 0 3px var(--accent-glow);
            }

            .form-input::placeholder,
            .form-textarea::placeholder {
                color: var(--text-muted);
            }

            .form-textarea { min-height: 120px; resize: vertical; }

            .form-select {
                cursor: pointer;
                appearance: none;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
                background-repeat: no-repeat;
                background-position: right 0.75rem center;
                padding-right: 2.5rem;
            }

            .form-error {
                color: var(--danger);
                font-size: 0.8125rem;
                margin-top: 0.375rem;
                display: flex;
                align-items: center;
                gap: 0.25rem;
            }

            .form-hint {
                color: var(--text-muted);
                font-size: 0.8125rem;
                margin-top: 0.375rem;
            }

            /* ── Cards ─────────────────────────────────────────── */
            .card {
                background: var(--bg-card);
                border: 1px solid var(--border-color);
                border-radius: var(--radius);
                padding: 1.5rem;
                box-shadow: var(--shadow);
                transition: var(--transition);
            }

            .card:hover { border-color: var(--text-muted); }

            .card-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 1.5rem;
                flex-wrap: wrap;
                gap: 1rem;
            }

            .card-title {
                font-size: 1.25rem;
                font-weight: 700;
                color: var(--text-primary);
            }

            /* ── Badges ────────────────────────────────────────── */
            .badge {
                display: inline-flex;
                align-items: center;
                gap: 0.375rem;
                padding: 0.25rem 0.625rem;
                border-radius: var(--radius-full);
                font-size: 0.75rem;
                font-weight: 600;
                letter-spacing: 0.025em;
                white-space: nowrap;
            }

            .badge-dot {
                width: 6px;
                height: 6px;
                border-radius: 50%;
                flex-shrink: 0;
            }

            /* ── Flash Messages ────────────────────────────────── */
            .alert {
                padding: 1rem 1.25rem;
                border-radius: var(--radius-sm);
                margin-bottom: 1.5rem;
                display: flex;
                align-items: center;
                gap: 0.75rem;
                font-size: 0.875rem;
                font-weight: 500;
                animation: slideDown 0.3s ease-out;
                border: 1px solid;
            }

            .alert-success {
                background: var(--success-bg);
                color: var(--success);
                border-color: rgba(34, 197, 94, 0.2);
            }

            .alert-error {
                background: var(--danger-bg);
                color: var(--danger);
                border-color: rgba(239, 68, 68, 0.2);
            }

            .alert-icon { font-size: 1.15rem; flex-shrink: 0; }

            /* ── Animations ────────────────────────────────────── */
            @keyframes slideDown {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }

            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
        </style>
        @yield('styles')
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @yield('scripts')
    </body>
</html>
