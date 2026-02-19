<?php

use App\Http\Controllers\Web\PublicityController;
use Illuminate\Support\Facades\Route;

Route::post('/reorder', [PublicityController::class, 'reorder'])->name('reorder');
Route::get('/', [PublicityController::class, 'index'])->name('index');
Route::post('/', [PublicityController::class, 'store'])->name('store');
Route::post('/{id}', [PublicityController::class, 'edit'])->name('edit');
Route::delete('/{id}', [PublicityController::class, 'delete'])->name('delete');
