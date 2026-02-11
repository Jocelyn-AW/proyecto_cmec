<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicityController;

Route::get('/', [PublicityController::class, 'index'])->name('publicity.index');
Route::post('/', [PublicityController::class, 'store'])->name('publicity.store');
Route::put('/{$id}', [PublicityController::class, 'edit'])->name('publicity.edit');
Route::delete('/{$id}', [PublicityController::class, 'delete'])->name('publicity.delete');
