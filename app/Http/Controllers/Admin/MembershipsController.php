<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Membership;
use App\Models\MembershipPrice;
use Inertia\Inertia;
use Exception;

class MembershipsController extends Controller
{
    // ---------------------------------------------
    // CRUD
    // ---------------------------------------------

    public function index()
    {
        $membership = Membership::with('prices')->first();

        return Inertia::render('Memberships/Index', [
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

            $membership = Membership::create([
                'name'        => $data['name'],
                'description' => $data['description'],
                'benefits'    => $data['benefits'],
            ]);

            $membership->prices()->createMany($data['prices']);

            DB::commit();

            return redirect()
                ->route('memberships.index')
                ->with('success', 'Membresía creada exitosamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al crear membresía', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Hubo un error al crear la membresía. Por favor intenta de nuevo.')
                ->withInput();
        }
    }

    public function update(Request $request)
    {
        try {
            DB::beginTransaction();

            $membership = Membership::findOrFail($request->id);
            $this->ensureNotTrashed($membership);

            $data = $request->validate(
                $this->getValidationArray(),
                $this->getValidationMessages()
            );

            $membership->update([
                'name'        => $data['name'],
                'description' => $data['description'],
                'benefits'    => $data['benefits'],
            ]);

            $membership->prices()->delete();
            $membership->prices()->createMany($data['prices']);

            DB::commit();

            return redirect()
                ->route('memberships.index')
                ->with('success', 'Membresía actualizada exitosamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar membresía', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Hubo un error al actualizar la membresía. Intenta de nuevo más tarde.')
                ->withInput();
        }
    }

    public function delete($id)
    {
        try {
            $membership = Membership::findOrFail($id);
            $membership->delete();

            return redirect()
                ->route('memberships.index')
                ->with('success', 'Membresía eliminada exitosamente');
        } catch (Exception $e) {
            return redirect()
                ->route('memberships.index')
                ->with('error', 'Hubo un error al eliminar la membresía. Intenta de nuevo más tarde.');
        }
    }

    public function restore(int $id)
    {
        try {
            $membership = Membership::withTrashed()->findOrFail($id);
            $membership->restore();

            return redirect()
                ->route('memberships.index')
                ->with('success', 'Membresía restaurada exitosamente');
        } catch (Exception $e) {
            return redirect()
                ->route('memberships.index')
                ->with('error', 'Hubo un error al restaurar la membresía. Intenta de nuevo más tarde.');
        }
    }

    // ---------------------------------------------
    // PRIVATE: Validation
    // ---------------------------------------------

    private function getValidationArray(): array
    {
        return [
            'name'                         => 'required|string|max:191',
            'description'                  => 'required|string|max:191',
            'benefits'                     => 'nullable|string',
            // Precios
            'prices'                       => 'required|array|min:1|max:12',
            'prices.*.start_date'          => 'required|date',
            'prices.*.end_date'            => 'required|date|after_or_equal:prices.*.start_date',
            'prices.*.amount_general'      => 'required|numeric|min:0',
            'prices.*.amount_preferential' => 'required|numeric|min:0',
        ];
    }

    private function getValidationMessages(): array
    {
        return [
            'name.required'                    => 'El nombre es obligatorio.',
            'name.max'                         => 'El nombre no debe exceder 191 caracteres.',
            'description.required'             => 'La descripción es obligatoria.',
            'description.max'                  => 'La descripción no debe exceder 191 caracteres.',
            'prices.required'                  => 'Debes agregar al menos un período de precio.',
            'prices.max'                       => 'No puedes agregar más de 12 períodos de precio.',
            'prices.*.start_date.required'     => 'La fecha de inicio es obligatoria.',
            'prices.*.start_date.date'         => 'La fecha de inicio no es válida.',
            'prices.*.end_date.required'       => 'La fecha de fin es obligatoria.',
            'prices.*.end_date.date'           => 'La fecha de fin no es válida.',
            'prices.*.end_date.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            //TYPES OF PRICES
            'prices.*.amount_general.required'          => 'El monto general es obligatorio.',
            'prices.*.amount_general.numeric'           => 'El monto general debe ser un número.',
            'prices.*.amount_general.min'               => 'El monto general no puede ser negativo.',
            'prices.*.amount_preferential.required'     => 'El monto preferencial es obligatorio.',
            'prices.*.amount_preferential.numeric'      => 'El monto preferencial debe ser un número.',
            'prices.*.amount_preferential.min'          => 'El monto preferencial no puede ser negativo.',
        ];
    }

    // ---------------------------------------------
    // PRIVATE: Guards
    // ---------------------------------------------

    private function ensureNotTrashed(Membership $membership): void
    {
        if ($membership->trashed()) {
            abort(403, 'No puedes modificar una membresía eliminada.');
        }
    }

    // ---------------------------------------------
    // PRIVATE: Stripe
    // ---------------------------------------------
    public function checkout(Request $request)
    {
        $member     = $request->user()->member;
        $membership = Membership::first();

        $membershipPrice = MembershipPrice::where('membership_id', $membership->id)
            ->where('start_date', '<=', now())
            ->where('end_date',   '>=', now())
            ->firstOrFail();

        $esNuevo = empty($member->inscription_date);

        $priceId = $esNuevo
            ? $membershipPrice->stripe_price_general_id
            : $membershipPrice->stripe_price_preferential_id;

        $amount = $esNuevo
            ? $membershipPrice->amount_general
            : $membershipPrice->amount_preferential;

        return Inertia::location($member->checkout($priceId, [
            'success_url' => route('membership.success'),
            'cancel_url'  => route('profile.edit'),
            'metadata'    => [
                'member_id'           => $member->id,
                'membership_price_id' => $membershipPrice->id,
                'amount'              => $amount,
            ],
        ])->url);
    }

    public function success()
    {
        return Inertia::render('Memberships/PaymentSuccesful');
    }

}
