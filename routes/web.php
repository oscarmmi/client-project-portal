<?php

use App\Http\Controllers\Client\ProjectController as ClientProjectController;
use App\Http\Controllers\Owner\ProjectController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function (Request $request) {
    return to_route($request->user()->isOwner() ? 'owner.project.show' : 'client.project.show');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:owner'])->prefix('owner')->group(function () {
    Route::get('/', [ProjectController::class, 'show'])->name('owner.dashboard');
    Route::get('/project', [ProjectController::class, 'show'])->name('owner.project.show');
    Route::post('/project', [ProjectController::class, 'store'])->name('owner.project.store');
    Route::patch('/project/{project}', [ProjectController::class, 'update'])->name('owner.project.update');
    Route::delete('/project/{project}', [ProjectController::class, 'destroy'])->name('owner.project.destroy');
    Route::post('/project/{project}/milestones', [ProjectController::class, 'storeMilestone'])->name('owner.milestones.store');
    Route::patch('/project/{project}/milestones/{milestone}', [ProjectController::class, 'updateMilestone'])->name('owner.milestones.update');
    Route::delete('/project/{project}/milestones/{milestone}', [ProjectController::class, 'destroyMilestone'])->name('owner.milestones.destroy');
    Route::post('/project/{project}/updates', [ProjectController::class, 'storeUpdate'])->name('owner.updates.store');
});

Route::middleware(['auth', 'role:client'])->prefix('client')->group(function () {
    Route::get('/project', [ClientProjectController::class, 'show'])->name('client.project.show');
});

require __DIR__.'/auth.php';
