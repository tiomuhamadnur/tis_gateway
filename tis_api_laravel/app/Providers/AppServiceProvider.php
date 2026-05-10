<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('superadmin')) {
                return true;
            }
        });

        Livewire::component('dashboard', \App\Livewire\Dashboard::class);
        Livewire::component('user-management', \App\Livewire\UserManagement::class);
        Livewire::component('failure-table', \App\Livewire\FailureTable::class);
        Livewire::component('api-docs', \App\Livewire\ApiDocs::class);
    }
}
