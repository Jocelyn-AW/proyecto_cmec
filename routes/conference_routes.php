<?php

use App\Http\Controllers\Admin\ConferencesController;
use Illuminate\Support\Facades\Route;

Route::get('/{subtype}', [ConferencesController::class, 'index'])->name('index');
Route::get('/{subtype}/new', [ConferencesController::class, 'new'])->name('new');
Route::get('/{subtype}/{id}', [ConferencesController::class, 'edit'])->name('edit');

Route::post('/new', [ConferencesController::class, 'store'])->name('store');
Route::put('/{id}', [ConferencesController::class, 'update'])->name('update');
Route::delete('/{id}', [ConferencesController::class, 'delete'])->name('delete');
Route::put('restore/{id}', [ConferencesController::class, 'restore'])->name('restore');

Route::get('/changeStaus/{id}', [ConferencesController::class, 'changeStatus'])->name('change-status');

//rutas especificas para albumes
Route::get('/main', [ConferencesController::class, 'index'])->name('index');
Route::get('/pre', [ConferencesController::class, 'index'])->name('pre.index');
Route::get('/trans', [ConferencesController::class, 'index'])->name('trans.index');