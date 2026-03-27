<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Member;
use App\Models\News;
use App\Models\Webinar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function dashboard()
    {
        $membersCount = Member::all()->count();
        $newsCount = News::all()->count();

        $now = Carbon::now();

        $cursos   = Course::whereYear('created_at', $now->year)
                        ->whereMonth('created_at', $now->month)
                        ->count();

        $sesiones = AcademicSession::whereYear('created_at', $now->year)
                        ->whereMonth('created_at', $now->month)
                        ->count();

        $webinars = Webinar::whereYear('created_at', $now->year)
                        ->whereMonth('created_at', $now->month)
                        ->count();

        $totalEvents = $cursos + $sesiones + $webinars;

        $data = [
            'members_count' => $membersCount,
            'news_count' => $newsCount,
            'events_count' => $totalEvents,
        ];


        return Inertia::render('Dashboard', [
            'data' => $data
        ]);
    }
}
