<?php

use App\Http\Controllers\Web\BannersController;
use Illuminate\Support\Facades\Route;

Route::post('/reorder', [BannersController::class, 'reorder'])->name('reorder');
Route::get('/', [BannersController::class, 'index'])->name('index');
Route::post('/', [BannersController::class, 'store'])->name('store');
//Cambiar a PUT en web para pruebas // No funciono
Route::post('/{id}', [BannersController::class, 'edit'])->name('edit');
Route::delete('/{id}', [BannersController::class, 'delete'])->name('delete');
