<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Membership;
use App\Models\Member;
use App\Models\User;
use App\Services\MailService;
use Inertia\Inertia;
use Exception;

class MembersController extends Controller
{

    private MailService $mailService;
    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }
    // ---------------------------------------------
    // CRUD
    // ---------------------------------------------

    public function index(Request $request)
    {
        $members = $this->addFilters($request);

        return Inertia::render('Members/Index', [
            'members' => $members,
        ]);
    }

    public function new()
    {
        $membership = Membership::with('prices')->first();

        return Inertia::render('Members/MemberCreate', [
            'membership' => $membership,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate(
                $this->getValidationArray(),
                $this->getValidationMessages()
            );

            DB::beginTransaction();

            $userId = null;

            if ($request->boolean('create_user', true)) {
                // Crear usuario vinculado
                $password = Str::random(12);
                $user = User::create([
                    'name'     => $data['name'] . ' ' . $data['last_name'],
                    'email'    => $data['email'],
                    'password' => Hash::make($password),
                ]);

                $this->mailService->sendCustomEmail(
                    to: $user->email,
                    subject: 'Bienvenido al sistema',
                    viewName: 'emails.welcome_user',
                    viewData: [
                        'subject' => 'Bienvenido',
                        'headerTitle' => 'Bienvenido al sistema',
                        'name' => $user->name,
                        'email' => $user->email,
                        'password' => $password,
                        'loginUrl' => url('/login'),
                    ]
                );

                $userId = $user->id;
            }

            $member = Member::create([
                'cmec_member_id'   => $this->generateCmecMemberId(),
                'name'             => $data['name'],
                'last_name'        => $data['last_name'],
                'phone'            => $data['phone'],
                'email'            => $data['email'],
                'city'             => $data['city'],
                'state'            => $data['state']             ?? null,
                'hospital'         => $data['hospital']          ?? null,
                'inscription_date' => $data['inscription_date'],
                'expiration_date'  => $data['expiration_date'],
                'user_id'          => $userId,
            ]);

            if (!empty($data['amount'])) {
                $membership = Membership::first();
                $member->payments()->create([
                    'user_type'        => 'member',
                    'event_payed_type' => $membership ? 'membership' : null,
                    'event_payed_id'   => $membership?->id,
                    'payer_name'       => $member->name . ' ' . $member->last_name,
                    'payer_email'      => $member->email,
                    'payer_phone'      => $member->phone,
                    'payment_method'   => $data['payment_method'],
                    'amount'           => $data['amount'],
                    'payment_date'     => $data['payment_date'],
                    'reference'        => $data['reference'] ?? null,
                    'status'           => 'paid',
                ]);
            }

            $this->updateMemberMedia($member, $request);

            DB::commit();

            return redirect()
                ->route('members.index')
                ->with('success', 'Miembro registrado exitosamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al crear miembro', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Hubo un error al registrar el miembro. Por favor intenta de nuevo.')
                ->withInput();
        }
    }

    public function edit(int $id)
    {
        $member = Member::withTrashed()
            ->with('payments')
            ->findOrFail($id);
        $membership = Membership::with('prices')
            ->first();

        return Inertia::render('Members/MemberEdit', [
            'member'     => $member,
            'membership' => $membership,
        ]);
    }

    public function update(Request $request, int $id)
    {
        try {
            DB::beginTransaction();

            $member = Member::with('user')->findOrFail($id);
            $this->ensureNotTrashed($member);

            $data = $request->validate(
                $this->getValidationArray(isUpdate: true),
                $this->getValidationMessages()
            );

            $member->update([
                'cmec_member_id'   => $data['cmec_member_id']   ?? null,
                'name'             => $data['name'],
                'last_name'        => $data['last_name'],
                'phone'            => $data['phone'],
                'email'            => $data['email'],
                'city'             => $data['city'],
                'state'            => $data['state']             ?? null,
                'hospital'         => $data['hospital']          ?? null,
                'inscription_date' => $data['inscription_date'],
                'expiration_date'  => $data['expiration_date'],
            ]);

            // sync usuario vinculado
            if ($member->user) {
                $member->user->update([
                    'name'  => $data['name'] . ' ' . $data['last_name'],
                    'email' => $data['email'],
                ]);
            } elseif ($request->boolean('create_user', false)) {
                // si no tiene usuario, lo creamos
                $password = Str::random(12);
                $user = User::create([
                    'name'     => $data['name'] . ' ' . $data['last_name'],
                    'email'    => $data['email'],
                    'password' => Hash::make($password),
                ]);
                $member->update(['user_id' => $user->id]);

                $this->mailService->sendCustomEmail(
                    to: $user->email,
                    subject: 'Bienvenido al sistema',
                    viewName: 'emails.welcome_user',
                    viewData: [
                        'subject' => 'Bienvenido',
                        'headerTitle' => 'Bienvenido al sistema',
                        'name' => $user->name,
                        'email' => $user->email,
                        'password' => $password,
                        'loginUrl' => url('/login'),
                    ]
                );
            }

            if (!empty($data['amount'])) {
                $membership  = Membership::first();
                $payment     = $member->payments()->latest()->first();
                $paymentData = [
                    'payer_name'     => $member->name . ' ' . $member->last_name,
                    'payer_email'    => $member->email,
                    'payer_phone'    => $member->phone,
                    'payment_method' => $data['payment_method'],
                    'amount'         => $data['amount'],
                    'payment_date'   => $data['payment_date'],
                    'reference'      => $data['reference'],
                    'status'         => 'paid',
                ];

                if ($payment) {
                    $payment->update($paymentData);
                } else {
                    $member->payments()->create(array_merge($paymentData, [
                        'user_type'        => 'member',
                        'event_payed_type' => $membership ? 'membership' : null,
                        'event_payed_id'   => $membership?->id,
                    ]));
                }
            }

            $this->updateMemberMedia($member, $request);

            DB::commit();

            return redirect()
                ->route('members.index')
                ->with('success', 'Miembro actualizado exitosamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar miembro', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Hubo un error al actualizar el miembro. Intenta de nuevo más tarde.')
                ->withInput();
        }
    }

    public function delete(int $id)
    {
        try {
            $member = Member::findOrFail($id);
            $member->delete();

            return redirect()
                ->route('members.index')
                ->with('success', 'Miembro eliminado exitosamente');
        } catch (Exception $e) {
            return redirect()
                ->route('members.index')
                ->with('error', 'Hubo un error al eliminar el miembro. Intenta de nuevo más tarde.');
        }
    }

    public function restore(int $id)
    {
        try {
            $member = Member::withTrashed()->findOrFail($id);
            $member->restore();

            return redirect()
                ->route('members.index')
                ->with('success', 'Miembro restaurado exitosamente');
        } catch (Exception $e) {
            return redirect()
                ->route('members.index')
                ->with('error', 'Hubo un error al restaurar el miembro. Intenta de nuevo más tarde.');
        }
    }

    // ---------------------------------------------
    // PRIVATE: Filters / Queries
    // ---------------------------------------------

    private function addFilters(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search  = $request->get('search', null);
        $status  = $request->input('status', '');

        $query = Member::orderBy('created_at', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('cmec_member_id', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $query->with('payments');

        return $query
            ->withTrashFilter($status)
            ->paginate($perPage)
            ->withQueryString();
    }

    // ---------------------------------------------
    // PRIVATE: Media
    // ---------------------------------------------

    private function updateMemberMedia(Member $member, Request $request): void
    {
        $collections = [
            'diploma_especialidad' => 'members_diploma_especialidad',
            'titulo_medico'        => 'members_titulo_medico',
            'diploma_consejo'      => 'members_diploma_consejo',
            'cedula_profesion'     => 'members_cedula_profesion',
            'cedula_especialista'  => 'members_cedula_especialista',
            'constancia_fiscal'    => 'members_constancia_fiscal',
            'factura'              => 'members_factura',
            'comprobante_pago'     => 'members_comprobante_pago',
        ];

        foreach ($collections as $field => $collection) {
            if ($request->hasFile($field)) {
                $member->clearMediaCollection($collection);
                $member->addMediaFromRequest($field)->toMediaCollection($collection);
            }
        }
    }

    // ---------------------------------------------
    // PRIVATE: Validation
    // ---------------------------------------------

    private function getValidationArray(bool $isUpdate = false): array
    {
        $pdfRule = 'nullable|mimes:pdf|max:10240';

        return [
            'name'                 => 'required|string|max:191',
            'last_name'            => 'required|string|max:191',
            'phone'                => 'required|string|max:191',
            'email'                => 'required|email|max:191',
            'city'                 => 'required|string|max:191',
            'state'                => 'nullable|string|max:191',
            'hospital'             => 'nullable|string',
            'inscription_date'     => 'required|date',
            'expiration_date'      => 'required|date|after_or_equal:inscription_date',
            // Pago
            'amount'               => 'nullable|numeric|min:0',
            'payment_method'       => 'required_with:amount|nullable|string',
            'payment_date'         => 'required_with:amount|nullable|date',
            'reference'            => 'nullable|string|max:191',
            // Documentos
            'diploma_especialidad' => $pdfRule,
            'titulo_medico'        => $pdfRule,
            'diploma_consejo'      => $pdfRule,
            'cedula_profesion'     => $pdfRule,
            'cedula_especialista'  => $pdfRule,
            'constancia_fiscal'    => $pdfRule,
            'factura'              => $pdfRule,
            'comprobante_pago'     => $pdfRule,
        ];
    }

    private function getValidationMessages(): array
    {
        return [
            'name.required'                    => 'El nombre es obligatorio.',
            'last_name.required'               => 'Los apellidos son obligatorios.',
            'phone.required'                   => 'El teléfono es obligatorio.',
            'email.required'                   => 'El correo electrónico es obligatorio.',
            'email.email'                      => 'El correo electrónico no es válido.',
            'city.required'                    => 'La ciudad es obligatoria.',
            'inscription_date.required'        => 'La fecha de inscripción es obligatoria.',
            'expiration_date.required'         => 'La fecha de vencimiento es obligatoria.',
            'expiration_date.after_or_equal'   => 'La fecha de vencimiento debe ser igual o posterior a la inscripción.',
            'amount.numeric'                   => 'El monto debe ser un número.',
            'amount.min'                       => 'El monto no puede ser negativo.',
            'payment_method.required_with'     => 'El método de pago es obligatorio cuando se indica un monto.',
            'payment_date.required_with'       => 'La fecha de pago es obligatoria cuando se indica un monto.',
            '*.mimes'                          => 'El archivo debe ser un PDF.',
            '*.max'                            => 'El archivo no debe exceder 10MB.',
        ];
    }

    // ---------------------------------------------
    // PRIVATE: Guards
    // ---------------------------------------------

    private function ensureNotTrashed(Member $member): void
    {
        if ($member->trashed()) {
            abort(403, 'No puedes modificar un miembro eliminado.');
        }
    }

    // ---------------------------------------------
    // PRIVATE: Generate CMEC ID
    // ---------------------------------------------

    private function generateCmecMemberId(): string
    {
        $maxAttempts = 5;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $last = Member::withTrashed()
                ->whereNotNull('cmec_member_id')
                ->where('cmec_member_id', 'like', 'CMEC-%')
                ->orderByRaw("CAST(SUBSTRING(cmec_member_id, 6) AS UNSIGNED) DESC")
                ->value('cmec_member_id');

            $next = $last ? ((int) substr($last, 5)) + 1 : 1;
            $candidate = 'CMEC-' . str_pad($next, 4, '0', STR_PAD_LEFT);

            if (!Member::withTrashed()->where('cmec_member_id', $candidate)->exists()) {
                return $candidate;
            }
        }
        return 'CMEC-' . now()->format('ymdHis');
    }
}
