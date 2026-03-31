<?php

use App\Http\Controllers\Admin\MembershipsController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\HistoryController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\AttendeeRegistrationController;
use App\Http\Controllers\Web\MemberInvoiceController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [AuthenticatedSessionController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile',          [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',        [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',       [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/avatar',  [ProfileController::class, 'updateAvatar'])->name('profile.avatar');

    Route::get('/directory', [DirectoryController::class, 'index'])->name('directory');
    Route::post('/directory', [DirectoryController::class, 'saveChanges'])->name('directory.save');
    Route::post('/directory/profile/{id}', [DirectoryController::class, 'uploadProfile'])->name('directory.profile');
    Route::delete('/{id}', [DirectoryController::class, 'delete'])->name('directory.delete');
    Route::put('restore/{id}', [DirectoryController::class, 'restore'])->name('directory.restore');

    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');

    Route::post('/membership/checkout', [MembershipsController::class, 'checkout'])->name('membership.checkout');
    Route::get('/membership/success', [MembershipsController::class, 'success'])->name('membership.success');


    Route::get('/datos-fiscales',  [MemberInvoiceController::class, 'edit'])->name('invoice-data.show');
    Route::post('/datos-fiscales', [MemberInvoiceController::class, 'store'])->name('invoice-data.store');
});

//Registro al Evento y Pago con Stripe
Route::prefix('eventos')->group(function () {
    Route::get('/{eventType}/{eventId}/registro',        [AttendeeRegistrationController::class, 'show'])->name('public.event.register');
    Route::post('/{eventType}/{eventId}/registro',       [AttendeeRegistrationController::class, 'store'])->name('public.event.store');
    Route::post('/validate-member',                      [AttendeeRegistrationController::class, 'validateMember'])->name('public.event.validate-member');
    Route::get('/payment/success/{eventType}/{eventId}', [AttendeeRegistrationController::class, 'success'])->name('payment.success');
    Route::get('/payment/cancel/{eventType}/{eventId}',  [AttendeeRegistrationController::class, 'cancel'])->name('payment.cancel');
});

require __DIR__ . '/auth.php';
