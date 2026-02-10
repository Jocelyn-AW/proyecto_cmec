<?php

use App\Http\Controllers\Web\BannersController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::post('/upbanner', [BannersController::class, 'store']);
