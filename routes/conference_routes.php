<?php

use App\Http\Controllers\Admin\ConferencesController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ConferencesController::class, 'index'])->name('index');
Route::get('/new', [ConferencesController::class, 'new'])->name('new');
Route::post('/new', [ConferencesController::class, 'store'])->name('store');
Route::get('/{id}', [ConferencesController::class, 'edit'])->name('edit');
Route::put('/{id}', [ConferencesController::class, 'update'])->name('update');
Route::delete('/{id}', [ConferencesController::class, 'delete'])->name('delete');
Route::put('restore/{id}', [ConferencesController::class, 'restore'])->name('restore');

Route::get('/changeStaus/{id}', [ConferencesController::class, 'changeStatus'])->name('change-status');