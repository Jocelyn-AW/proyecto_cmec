<?php

use App\Http\Controllers\Admin\MembersController;
use Illuminate\Support\Facades\Route;

Route::get('/',              [MembersController::class, 'index'])->name('index');
Route::get('/new',           [MembersController::class, 'new'])->name('new');
Route::post('/new',          [MembersController::class, 'store'])->name('store');
Route::get('/{id}/edit',     [MembersController::class, 'edit'])->name('edit');
Route::put('/{id}',          [MembersController::class, 'update'])->name('update');
Route::delete('/{id}',       [MembersController::class, 'delete'])->name('delete');
Route::put('/restore/{id}',  [MembersController::class, 'restore'])->name('restore');