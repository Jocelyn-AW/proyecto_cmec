<?php

use App\Http\Controllers\Admin\AlbumsController;
use Illuminate\Support\Facades\Route;


Route::post('/', [AlbumsController::class, 'store'])->name('store');
Route::get('/{id}/photos', [AlbumsController::class, 'photos'])->name('photos')->whereNumber('id');
// subir fotos a un album
Route::post('/{id}/photos', [AlbumsController::class, 'uploadPhotos'])->name('photos.upload')->whereNumber('id');
// eliminar una foto de un album
Route::delete('/{id}/photos/{mediaId}', [AlbumsController::class, 'deletePhoto'])->name('photos.delete')->whereNumber(['id', 'mediaId']);
// actualizar album
Route::patch('/{id}', [AlbumsController::class, 'update'])->name('update')->whereNumber('id');
// eliminar album completo
Route::delete('/{id}', [AlbumsController::class, 'delete'])->name('delete')->whereNumber('id');


// Ruta  AL FINAL para no estorbar las anteriores
// listar albumes de un evento
Route::get('/{event_type}/{event_id}', [AlbumsController::class, 'index'])->name('index')->whereNumber('event_id');
