<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('documents.pending-approvals');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Workflow routes
    Route::get('workflows/{id}/details', [WorkflowController::class, 'getDetails'])->name('workflows.details');
    Route::resource('workflows', WorkflowController::class);

    // Document routes
    Route::resource('documents', DocumentController::class);
    Route::get('pending-approvals', [DocumentController::class, 'pendingApprovals'])->name('documents.pending-approvals');
    Route::post('documents/{document}/approve', [DocumentController::class, 'approve'])->name('documents.approve');
    Route::post('documents/{document}/reject', [DocumentController::class, 'reject'])->name('documents.reject');
   // Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
