<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            $middleware = env('ROUTES_USE_MIDDLEWARE')
                ? ['web', 'auth', 'role:administrador']
                : [];

            Route::middleware($middleware)->group(function () {
                Route::prefix('banners')->name('banners.')
                    ->group(base_path('routes/banner_routes.php'));
                Route::prefix('publicity')->name('publicity.')
                    ->group(base_path('routes/publicity_routes.php'));
                Route::prefix('users')->name('users.')
                    ->group(base_path('routes/user_routes.php'));
                Route::prefix('news')->name('news.')
                    ->group(base_path('routes/news_routes.php'));
                Route::prefix('bankdetails')->name('bankdetails.')
                    ->group(base_path('routes/bankdetails_routes.php'));
                Route::prefix('courses')->name('courses.')
                    ->group(base_path('routes/course_routes.php'));
                Route::prefix('attendees')->name('attendees.')
                    ->group(base_path('routes/attendees_routes.php'));
                Route::prefix('webinars')->name('webinars.')
                    ->group(base_path('routes/webinar_routes.php'));
            });
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
