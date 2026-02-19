<?php

use App\Http\Controllers\Web\NewsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [NewsController::class, 'index'])->name('index');
Route::post('/', [NewsController::class, 'store'])->name('store');
Route::post('/{id}', [NewsController::class, 'edit'])->name('edit');
Route::delete('/{id}', [NewsController::class, 'delete'])->name('delete');
Route::post('/{id}/status', [NewsController::class, 'statusChange'])->name('statusChange');