<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\AdminController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ScannerController;

Route::get('/dashboard', [AdminController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/admin/invite', [AdminController::class, 'invite'])->name('admin.invite');
    Route::delete('/admin/attendees/{id}', [AdminController::class, 'destroy'])->name('admin.attendees.destroy');
    Route::post('/admin/departments', [AdminController::class, 'storeDepartment'])->name('admin.departments.store');
    Route::delete('/admin/departments/{id}', [AdminController::class, 'destroyDepartment'])->name('admin.departments.destroy');
    Route::post('/admin/companies', [AdminController::class, 'storeCompany'])->name('admin.companies.store');
    Route::delete('/admin/companies/{id}', [AdminController::class, 'destroyCompany'])->name('admin.companies.destroy');
    Route::get('/scanner', [ScannerController::class, 'index'])->name('scanner.index');
    Route::post('/api/scan', [ScannerController::class, 'processScan'])->name('scanner.process');
    Route::get('/admin/deploy-helper/{action}', function ($action) {
        if (!in_array($action, ['migrate', 'config-cache', 'config-clear', 'cache-clear', 'storage-link'])) {
            return response()->json(['error' => 'Invalid action'], 400);
        }
        try {
            $command = str_replace('-', ':', $action);
            \Illuminate\Support\Facades\Artisan::call($command);
            $output = \Illuminate\Support\Facades\Artisan::output();
            return response()->json([
                'success' => true,
                'message' => "Command 'php artisan $command' executed successfully.",
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    })->name('admin.deploy-helper');
});

// Participant Routes
Route::get('/invite/{token}', [RegistrationController::class, 'showForm'])->name('register.form');
Route::post('/register/complete', [RegistrationController::class, 'complete'])->name('register.complete');
Route::get('/success/{attendee_id}', [RegistrationController::class, 'success'])->name('register.success');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
