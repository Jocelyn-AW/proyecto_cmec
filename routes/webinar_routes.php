<?php

use App\Http\Controllers\Admin\WebinarsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebinarsController::class, 'index'])->name('index');
Route::get('/new', [WebinarsController::class, 'new'])->name('new');
Route::post('/new', [WebinarsController::class, 'store'])->name('store');
Route::get('/{id}', [WebinarsController::class, 'edit'])->name('edit');
Route::put('/{id}', [WebinarsController::class, 'update'])->name('update');
Route::delete('/{id}', [WebinarsController::class, 'delete'])->name('delete');
Route::put('/restore/{id}', [WebinarsController::class, 'restore'])->name('restore');
Route::get('/changeStaus/{id}', [WebinarsController::class, 'changeStatus'])->name('change-status');