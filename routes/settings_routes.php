<?php

use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SettingsController::class, 'index'])->name('index');
Route::post('/', [SettingsController::class, 'update'])->name('update');