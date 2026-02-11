<?php

use App\Http\Controllers\Web\BannersController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::post('/reorder', [BannersController::class, 'reorder'])->name('banners.reorder');
Route::get('/', [BannersController::class, 'index'])->name('banners.index');
Route::post('/', [BannersController::class, 'store'])->name('banners.store');
//Cambiar a PUT en web para pruebas
Route::post('/{id}', [BannersController::class, 'edit'])->name('banners.edit');
Route::delete('/{id}', [BannersController::class, 'delete'])->name('banners.delete');


