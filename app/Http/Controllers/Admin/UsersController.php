<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use App\Services\MailService;

class UsersController extends Controller
{
    private MailService $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $users = User::orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'role' => $user->role,
                    'is_active' => $user->is_active,
                    'created_at' => $user->created_at->format('d/m/Y'),
                ];
            });

        return Inertia::render('Users/Index', [
            'users' => $users
        ]);
    }

    /* public function index(Request $request)
    {
        try {

            $query = User::orderBy('created_at', 'desc');
            $users = $query->get();

            return response()->json([
                'message' => 'success',
                'data' => $users
            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    } */

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            // Enviar correo
            $this->mailService->sendCustomEmail(
                to: $user->email,
                subject: 'Bienvenido al sistema',
                viewName: 'emails.welcome_user',
                viewData: [
                    'subject' => 'Bienvenido',
                    'headerTitle' => 'Bienvenido al sistema',
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $data['password'],
                    'loginUrl' => url('/login'),
                ]
            );

            /* return response()->json([
                'message' => 'success',
                'data' => [
                    'user' => $user
                ]
            ], 200); */
            return redirect()->route('users.index');
        } catch (Exception $e) {
            /* return response()->json([
                'message' => $e->getMessage(),
            ], 500); */
            return redirect()->back()->withErrors([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, int $id)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $id,
                'password' => ['nullable', 'confirmed', Password::defaults()],
                'role' => 'required|in:administrador,miembro'
            ]);

            $user = User::findOrFail($id);

            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->role = $data['role'];

            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();

            /* return response()->json([
                'message' => 'success',
                'data' => [
                    'user' => $user
                ]
            ], 200); */
            return redirect()->route('users.index');
        } catch (Exception $e) {
            /* return response()->json([
                'message' => $e->getMessage(),
            ], 500); */
            return redirect()->back()->withErrors([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(int $id)
    {
        try {
            if (Auth::id() === $id) {
                return redirect()->back()->withErrors([
                    'message' => 'No puedes eliminar tu propio usuario'
                ]);
            }

            $user = User::findOrFail($id);
            $user->delete();

            return redirect()->route('users.index');
        } catch (Exception $e) {
            /* return response()->json([
                'message' => $e->getMessage(),
            ], 500); */
            return redirect()->back()->withErrors([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cambia el estado activo/inactivo del usuario.
     */
    public function statusChange(int $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->is_active = !$user->is_active;
            $user->save();

            return redirect()->route('users.index');
            /* return response()->json([
                'message' => 'success',
                'data' => [
                    'user' => $user
                ]
            ], 200); */
        } catch (Exception $e) {
            /* return response()->json([
                'message' => $e->getMessage(),
            ], 500); */
            return redirect()->back()->withErrors([
                'message' => $e->getMessage()
            ]);
        }
    }
}
