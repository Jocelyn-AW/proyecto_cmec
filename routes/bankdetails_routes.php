<?php

use App\Http\Controllers\Admin\BankDetailsController;
use Illuminate\Support\Facades\Route;


Route::get('/', [BankDetailsController::class, 'index'])->name('index');
Route::post('/', [BankDetailsController::class, 'store'])->name('store');
Route::put('/{id}', [BankDetailsController::class, 'edit'])->name('edit');
Route::delete('/{id}', [BankDetailsController::class, 'delete'])->name('delete');