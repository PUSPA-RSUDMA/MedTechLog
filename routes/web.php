<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiklatTaskController;
use App\Http\Controllers\LogbookController;
use App\Http\Controllers\Master\PicController;
use App\Http\Controllers\Master\ProfessionController;
use App\Http\Controllers\Master\UnitController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WaManagerController;
use App\Http\Controllers\WorkLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 3 Menu Tugas Terpisah: Tugas IT, Tugas RM, Tugas Diklat
    Route::get('/tasks/it', [TaskController::class, 'index'])->defaults('department', 'it')->name('tasks.it');
    Route::get('/tasks/rm', [TaskController::class, 'index'])->defaults('department', 'rm')->name('tasks.rm');
    Route::resource('tasks', TaskController::class);
    Route::post('/tasks/{task}/due-date', [TaskController::class, 'updateDueDate'])->name('tasks.update-due-date');

    // Tugas Diklat & Google Drive Settings
    Route::get('/diklat', [DiklatTaskController::class, 'index'])->name('diklat.index');
    Route::post('/diklat', [DiklatTaskController::class, 'store'])->name('diklat.store');
    Route::put('/diklat/{diklatTask}', [DiklatTaskController::class, 'update'])->name('diklat.update');
    Route::delete('/diklat/{diklatTask}', [DiklatTaskController::class, 'destroy'])->name('diklat.destroy');
    Route::post('/diklat/drive-settings', [DiklatTaskController::class, 'saveDriveSettings'])->name('diklat.drive-settings');
    Route::post('/diklat/drive-test', [DiklatTaskController::class, 'testDriveConnection'])->name('diklat.drive-test');

    // Update Ketercapaian (Work Logs)
    Route::resource('work-logs', WorkLogController::class)->only(['index', 'create', 'store', 'destroy']);

    // Logbook Acara (Kalender)
    Route::get('/logbook', [LogbookController::class, 'index'])->name('logbook.index');
    Route::post('/logbook', [LogbookController::class, 'store'])->name('logbook.store');
    Route::put('/logbook/{logbookEvent}', [LogbookController::class, 'update'])->name('logbook.update');
    Route::delete('/logbook/{logbookEvent}', [LogbookController::class, 'destroy'])->name('logbook.destroy');

    // WA Manager
    Route::get('/wa-manager', [WaManagerController::class, 'index'])->name('wa-manager.index');
    Route::post('/wa-manager/settings', [WaManagerController::class, 'updateSettings'])->name('wa-manager.settings');
    Route::post('/wa-manager/send-test', [WaManagerController::class, 'sendTest'])->name('wa-manager.send-test');
    Route::post('/wa-manager/run-reminders', [WaManagerController::class, 'runRemindersNow'])->name('wa-manager.run-reminders');
    Route::post('/wa-manager/retry/{waMessage}', [WaManagerController::class, 'retryMessage'])->name('wa-manager.retry');

    // Analisis & Laporan
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
    Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel');

    // Notifikasi
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Master Data (Unit PIC, PIC, Unit Diklat, Profesi)
    Route::prefix('master')->name('master.')->group(function (): void {
        Route::resource('units', UnitController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('pics', PicController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('diklat-units', \App\Http\Controllers\Master\DiklatUnitController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('professions', ProfessionController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
