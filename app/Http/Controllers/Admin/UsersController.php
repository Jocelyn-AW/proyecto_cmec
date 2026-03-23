<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\MailService;

class UsersController extends Controller
{
    private MailService $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    // ---------------------------------------------
    // CRUD
    // ---------------------------------------------

    public function index(Request $request): Response
    {
        $users = $this->addFilters($request);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'perPage']),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate(
                $this->getValidationRulesStore(),
                $this->getValidationMessages()
            );

            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'role'     => $data['role'] ?? 'miembro',
            ]);

            // correo
            $this->sendWelcomeEmail($user, $data['password']);

            return redirect()
                ->route('users.index')
                ->with('success', 'Usuario creado exitosamente');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            Log::error('Error al crear usuario', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Hubo un error al crear el usuario.')
                ->withInput();
        }
    }

    public function edit(Request $request)
    {
        try {
            $id = $request->id;
            $user = User::findOrFail($id);

            $data = $request->validate(
                $this->getValidationRules($id),
                $this->getValidationMessages()
            );

            $user->name  = $data['name'];
            $user->email = $data['email'];
            $user->role  = $data['role'];

            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();

            return redirect()
                ->route('users.index')
                ->with('success', 'Usuario actualizado exitosamente');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            Log::error('Error al actualizar usuario', ['message' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Error al actualizar el usuario.');
        }
    }

    public function delete(int $id)
    {
        try {
            if (Auth::id() === $id) {
                return redirect()->back()->with('error', 'No puedes eliminar tu propia cuenta.');
            }

            $user = User::findOrFail($id);
            $user->delete();

            return redirect()
                ->route('users.index')
                ->with('success', 'Usuario desactivado exitosamente');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar el usuario.');
        }
    }

    public function statusChange(int $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->is_active = !$user->is_active;
            $user->save();

            return redirect()->route('users.index');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al cambiar el estado del usuario.');
        }
    }

    // ---------------------------------------------
    // PRIVATE: Filters / Queries
    // ---------------------------------------------

    private function addFilters(Request $request)
    {
        $search  = $request->input('search');
        $perPage = $request->input('perPage', 10);

        return User::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%")
                        ->orWhereRaw("
                            CASE 
                                WHEN is_active = 1 THEN 'activo'
                                ELSE 'inactivo'
                            END LIKE ? ", ["%{$search}%"]);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn($user) => [
                'id'                => $user->id,
                'name'              => $user->name,
                'email'             => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'role'              => $user->role,
                'is_active'         => $user->is_active,
                'created_at'        => $user->created_at->format('d/m/Y'),
            ]);
    }

    // ---------------------------------------------
    // PRIVATE: Validation
    // ---------------------------------------------

    private function getValidationRules($id = null): array
    {
        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => $id
                ? ['nullable', 'confirmed', Password::defaults()]
                : ['required', 'confirmed', Password::defaults()],
            'role'     => 'required|in:administrador,miembro',
        ];
    }

    private function getValidationRulesStore(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    private function getValidationMessages(): array
    {
        return [
            'name.required'      => 'El nombre es obligatorio.',
            'email.required'     => 'El correo electrónico es obligatorio.',
            'email.email'        => 'Ingrese un formato de correo válido.',
            'email.unique'       => 'Este correo ya está registrado.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'role.required'      => 'Seleccione un rol para el usuario.',
        ];
    }

    // ---------------------------------------------
    // PRIVATE: Helpers
    // ---------------------------------------------

    private function sendWelcomeEmail(User $user, string $plainPassword): void
    {
        $this->mailService->sendCustomEmail(
            to: $user->email,
            subject: 'Bienvenido al sistema',
            viewName: 'emails.welcome_user',
            viewData: [
                'subject'     => 'Bienvenido',
                'headerTitle' => 'Bienvenido al sistema',
                'name'        => $user->name,
                'email'       => $user->email,
                'password'    => $plainPassword,
                'loginUrl'    => url('/login'),
            ]
        );
    }
}
