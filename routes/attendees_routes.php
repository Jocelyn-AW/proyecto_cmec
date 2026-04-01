<?php

use App\Http\Controllers\Admin\AttendeesController;
use Illuminate\Support\Facades\Route;

Route::get('/{event}', [AttendeesController::class, 'index'])->name('index');
Route::post('/new', [AttendeesController::class, 'store'])->name('store');
Route::put('/{id}', [AttendeesController::class, 'update'])->name('update');
Route::delete('/{id}', [AttendeesController::class, 'delete'])->name('delete');
Route::put('/restore/{id}', [AttendeesController::class, 'restore'])->name('restore');
Route::post('/upload-diploma/{id}', [AttendeesController::class, 'uploadDiploma'])->name('upload-diploma');
Route::get('/changeAttend/{id}', [AttendeesController::class, 'changeDidAttend'])->name('change-attend');
Route::get('/by-cmec/{cmecId}', [AttendeesController::class, 'findMemberByCmec'])->name('by-cmec');
Route::get('/getInvoiceData/{cmecId}', [AttendeesController::class, 'getInvoiceData'])->name('get-invoice-data');

//Exports
Route::get('/{event}/export/excel', [AttendeesController::class, 'exportExcel'])->name('export.excel');
Route::get('/{event}/export/pdf', [AttendeesController::class, 'exportPdf'])->name('export.pdf');