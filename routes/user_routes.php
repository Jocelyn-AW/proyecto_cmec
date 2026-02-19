<?php

use App\Http\Controllers\Admin\UsersController;
use Illuminate\Support\Facades\Route;


Route::get('/', [UsersController::class, 'index'])->name('index');
Route::post('/', [UsersController::class, 'store'])->name('store');
Route::put('/{id}', [UsersController::class, 'edit'])->name('edit');
Route::delete('/{id}', [UsersController::class, 'delete'])->name('delete');
Route::patch('/{id}/status', [UsersController::class, 'statusChange'])->name('statusChange');

