<?php

use App\Http\Controllers\Admin\AttendeesController;
use Illuminate\Support\Facades\Route;

Route::get('/{event}', [AttendeesController::class, 'index'])->name('index');
Route::post('/new', [AttendeesController::class, 'store'])->name('store');
Route::put('/{id}', [AttendeesController::class, 'update'])->name('update');
Route::delete('/{id}', [AttendeesController::class, 'delete'])->name('delete');
Route::post('/upload-diploma/{id}', [AttendeesController::class, 'uploadDiploma'])->name('upload-diploma');