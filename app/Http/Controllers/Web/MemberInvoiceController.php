<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InvoiceData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Exception;

class MemberInvoiceController extends Controller
{
    // ---------------------------------------------
    // CRUD
    // ---------------------------------------------

    public function edit()
    {
        $user = Auth::user();
        $member = $user->member;

        $invoiceData = $member
            ? InvoiceData::where('billable_type', 'member')
            ->where('billable_id', $member->id)
            ->first()
            : null;

        return Inertia::render('Members/User/InvoiceData', [
            'invoiceData' => $invoiceData,
            'member' => $member,
        ]);
    }

    public function store(Request $request)
    {
        $member = $this->getAuthenticatedMember();
        
        try {
            $data = $request->validate(
                $this->getValidationArray(),
                $this->getValidationMessages()
            );

            $this->saveInvoiceData($member, $data);

            return redirect()
                ->route('invoice-data.show')
                ->with('success', 'Datos de facturación guardados exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            Log::error('Error al guardar datos fiscales', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Ocurrió un error al guardar los datos. Inténtalo de nuevo.')
                ->withInput();
        }
    }

    // ---------------------------------------------
    // PRIVATE: Validation
    // ---------------------------------------------

    private function getValidationArray(): array
    {
        return [
            'rfc'             => 'required|string|uppercase|min:12|max:13',
            'tax_name'        => 'required|string|min:3|max:190',
            'address'         => 'required|string|min:3|max:190',
            'postal_code'     => 'required|string|digits:5',
            'tax_regime'      => 'required|string|digits:3',
            'cfdi_use'        => 'required|string|between:3,4',
            'tax_person_type' => 'required|in:fisica,moral',
        ];
    }

    private function getValidationMessages(): array
    {
        return [
            'rfc.required'             => 'El RFC es obligatorio.',
            'rfc.min'                  => 'El RFC debe tener al menos 12 caracteres.',
            'rfc.max'                  => 'El RFC no debe exceder 13 caracteres.',
            'tax_name.required'        => 'La razón social es obligatoria.',
            'address.required'         => 'El domicilio fiscal es obligatorio.',
            'postal_code.required'     => 'El código postal es obligatorio.',
            'postal_code.digits'       => 'El código postal debe tener 5 dígitos.',
            'tax_regime.required'      => 'El régimen fiscal es obligatorio.',
            'cfdi_use.required'        => 'El uso de CFDI es obligatorio.',
            'tax_person_type.required' => 'El tipo de persona es obligatorio.',
        ];
    }

    // ---------------------------------------------
    // PRIVATE: Guards
    // ---------------------------------------------

    private function getAuthenticatedMember()
    {
        $member = Auth::user()->member;

        if (!$member) {
            abort(403, 'No se encontró un miembro asociado a tu cuenta.');
        }

        return $member;
    }

    // ---------------------------------------------
    // PRIVATE: Helpers
    // ---------------------------------------------

    private function saveInvoiceData($member, array $data): void
    {
        $attributes = [
            'billable_type' => 'member',
            'billable_id'   => $member->id,
        ];

        $values = [
            'rfc'         => $data['rfc'],
            'name'        => $data['tax_name'],
            'email'       => $member->email,
            'postal_code' => $data['postal_code'],
            'person_type' => $data['tax_person_type'],
            'tax_regime'  => $data['tax_regime'],
            'cfdi_use'    => $data['cfdi_use'],
            'address'     => $data['address'],
        ];

        InvoiceData::updateOrCreate($attributes, $values);
    }
}
