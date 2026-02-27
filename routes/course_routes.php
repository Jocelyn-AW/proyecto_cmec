<?php

use App\Http\Controllers\Admin\CoursesController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CoursesController::class, 'index'])->name('index');
Route::get('/new', [CoursesController::class, 'new'])->name('new');
Route::post('/new', [CoursesController::class, 'store'])->name('store');
Route::get('/{id}', [CoursesController::class, 'edit'])->name('edit');
Route::put('/{id}', [CoursesController::class, 'update'])->name('update');
Route::delete('/{id}', [CoursesController::class, 'delete'])->name('delete');
Route::get('/changeStaus/{id}', [CoursesController::class, 'changeStatus'])->name('change-status');

Route::get('/{id}/gallery', [CoursesController::class, 'gallery'])->name('gallery');
Route::post('/{id}/gallery', [CoursesController::class, 'updateGallery'])->name('gallery.update');
Route::delete('/{id}/gallery/{mediaId}', [CoursesController::class, 'deleteGalleryImage'])->name('gallery.delete');