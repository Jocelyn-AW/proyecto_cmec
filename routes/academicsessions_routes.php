<?php

use App\Http\Controllers\Admin\AcademicSessionsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AcademicSessionsController::class, 'index'])->name('index');
Route::get('/new', [AcademicSessionsController::class, 'new'])->name('new');
Route::post('/new', [AcademicSessionsController::class, 'store'])->name('store');
Route::get('/{id}', [AcademicSessionsController::class, 'edit'])->name('edit');
Route::put('/{id}', [AcademicSessionsController::class, 'update'])->name('update');
Route::delete('/{id}', [AcademicSessionsController::class, 'delete'])->name('delete');
Route::patch('/{id}/status', [AcademicSessionsController::class, 'statusChange'])->name('statusChange');

Route::get('/{id}/gallery', [AcademicSessionsController::class, 'gallery'])->name('gallery');
Route::post('/{id}/gallery', [AcademicSessionsController::class, 'updateGallery'])->name('gallery.update');
Route::delete('/{id}/gallery/{mediaId}', [AcademicSessionsController::class, 'deleteGalleryImage'])->name('gallery.delete');