<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    Route::get('users', function () {
        return view('user-management');
    })->name('users.index')->middleware('can:manage users');

    Route::get('failures', function () {
        return view('failure-table');
    })->name('failures.index')->middleware('can:view failures');

    Route::get('failures/data', [App\Http\Controllers\FailureTableController::class, 'getData'])->name('failures.data');
    Route::get('failures/export/excel', [App\Http\Controllers\ExportController::class, 'exportExcel'])->name('failures.export.excel');
    Route::get('failures/export/pdf', [App\Http\Controllers\ExportController::class, 'exportPdf'])->name('failures.export.pdf');

    Route::get('api-docs', function () {
        return view('api-docs');
    })->name('api.docs')->middleware('role:superadmin');
});

require __DIR__.'/auth.php';
