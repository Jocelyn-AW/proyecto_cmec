<?php

use App\Http\Controllers\Admin\AcademicSessionsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AcademicSessionsController::class, 'index'])->name('index');
Route::get('/new', [AcademicSessionsController::class, 'new'])->name('new');
Route::post('/new', [AcademicSessionsController::class, 'store'])->name('store');
Route::get('/{id}', [AcademicSessionsController::class, 'edit'])->name('edit');
Route::put('/{id}', [AcademicSessionsController::class, 'update'])->name('update');
Route::delete('/{id}', [AcademicSessionsController::class, 'delete'])->name('delete');
Route::put('/restore/{id}', [AcademicSessionsController::class, 'restore'])->name('restore');
Route::get('/changeStaus/{id}', [AcademicSessionsController::class, 'changeStatus'])->name('change-status');