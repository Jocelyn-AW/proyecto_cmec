<?php

use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\HistoryController;
use Illuminate\Foundation\Application;
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

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile',          [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',        [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',       [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/avatar',  [ProfileController::class, 'updateAvatar'])->name('profile.avatar');

    Route::get('/directory', [DirectoryController::class, 'index'])->name('directory');
    Route::post('/directory', [DirectoryController::class, 'saveChanges'])->name('directory.save');
    Route::post('/directory/profile/{id}',[DirectoryController::class, 'uploadProfile'])->name('directory.profile');
    Route::delete('/{id}', [DirectoryController::class, 'delete'])->name('directory.delete');
    Route::put('restore/{id}', [DirectoryController::class, 'restore'])->name('directory.restore');

    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
});

require __DIR__.'/auth.php';
