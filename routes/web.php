<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('/tasks')->group(function (){
    Route::get('/', [TaskController::class, 'index']);
    Route::post('/', [TaskController::class, 'store']);
    Route::put('/{task}', [TaskController::class, 'update']);
    Route::delete('/{task}', [TaskController::class, 'destroy']);
    Route::put('/{task}/status', [TaskController::class, 'updateStatus']);
});

Route::get('/kanban', function () {
    return Inertia::render('Kanban');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
