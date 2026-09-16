<?php

namespace App\Providers;

use App\Models\TaskList;
use App\Policies\TaskListPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Daftarkan policy TaskList agar bisa dipanggil via $this->authorize()
        Gate::policy(TaskList::class, TaskListPolicy::class);
    }
}
