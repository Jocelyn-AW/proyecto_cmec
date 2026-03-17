<?php

use App\Http\Controllers\Admin\MembershipsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MembershipsController::class, 'index'])->name('index');
Route::post('/new', [MembershipsController::class, 'store'])->name('store');
Route::put('/{id}', [MembershipsController::class, 'update'])->name('update');
Route::delete('/{id}', [MembershipsController::class, 'delete'])->name('delete');
Route::put('/restore/{id}', [MembershipsController::class, 'restore'])->name('restore');
