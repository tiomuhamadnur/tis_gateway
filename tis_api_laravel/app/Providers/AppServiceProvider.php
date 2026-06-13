<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
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

        $this->ensureStorageLink();

        Livewire::component('dashboard', \App\Livewire\Dashboard::class);
        Livewire::component('user-management', \App\Livewire\UserManagement::class);
        Livewire::component('failure-table', \App\Livewire\FailureTable::class);
        Livewire::component('api-docs', \App\Livewire\ApiDocs::class);
    }

    private function ensureStorageLink(): void
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (! file_exists($link) && ! is_link($link)) {
            try {
                if (PHP_OS_FAMILY === 'Windows') {
                    exec('mklink /J "'.str_replace('/','\\',$link).'" "'.str_replace('/','\\',$target).'" 2>NUL', $out, $code);
                } else {
                    symlink($target, $link);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        if (! Storage::disk('public')->exists('photos')) {
            Storage::disk('public')->makeDirectory('photos');
        }
    }
}
