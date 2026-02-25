<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\BankDetail;
use Illuminate\Http\Request;
use App\Rules\LuhnValidation;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class BankDetailsController extends Controller
{
    /**
     * Lista todos los registros bancarios.
     */
    public function index()
    {
        return Inertia::render('BankDetails/Index', [
            'bankDetails' => BankDetail::with('updatedBy')->orderBy('created_at', 'desc')->get()
        ]);
    }

    /**
     * Guarda un nuevo registro bancario.
     */
    public function store(Request $request)
    {
        try {
            $messages = [
                'bank.required'           => 'Es obligatorio especificar el nombre de la institución bancaria.',
                'bank.string'             => 'El nombre del banco debe contener caracteres válidos.',
                'bank.max'                => 'El nombre del banco es demasiado extenso.',

                'account_number.required_without' => 'Debe proporcionar el número de cuenta si el campo CLABE está vacío.',
                'account_number.between' => 'La cuenta debe tener 10-11 dígitos o 16 para tarjetas de débito/crédito.',
                'account_number.regex'   => 'El número de cuenta/tarjeta debe contener únicamente números, sin espacios ni guiones.',
                'account_number.not_in' => 'Parece que intentó ingresar una CLABE en el campo de cuenta. La CLABE debe ir en su campo respectivo.',

                'clabe_number.required_without'   => 'Debe proporcionar la clave interbancaria (CLABE) si el número de cuenta está vacío.',
                'clabe_number.regex'              => 'La CLABE debe contener únicamente dígitos numéricos.',
                'clabe_number.size'               => 'La clave interbancaria (CLABE) debe tener exactamente 18 dígitos.',
                'clabe_number.digits'             => 'La CLABE debe ser de exactamente 18 dígitos numéricos.',

                'reference.string'        => 'La referencia debe ser un texto válido.',
                'beneficiary.string'      => 'El nombre del beneficiario debe ser un texto válido.',
                'subsidiary.string'       => 'El nombre de la sucursal debe ser un texto válido.',
            ];

            $data = $request->validate([
                'bank'           => ['required', 'string', 'max:255'],
                'account_number' => [
                    'nullable',
                    'string',
                    'required_without:clabe_number',
                    'regex:/^[0-9]+$/',
                    'between:10,16',
                ],
                'clabe_number'   => [
                    'nullable',
                    'string',
                    'required_without:account_number',
                    'regex:/^[0-9]+$/',
                    'size:18'
                ],
                'reference'      => ['nullable', 'string'],
                'beneficiary'    => ['nullable', 'string'],
                'subsidiary'     => ['nullable', 'string'],
            ], $messages);

            $bankDetail = BankDetail::create($data);

            /* return response()->json([
                'message' => 'Cuenta bancaria creada correctamente',
                'data'    => $bankDetail->load('updatedBy')
            ], 200); */
            return redirect()->route('bankdetails.index');
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
     * Muestra un registro bancario específico.
     */
    public function show(BankDetail $bankDetail): JsonResponse
    {
        try {
            return response()->json([
                'message' => 'success',
                'data'    => $bankDetail->load('updatedBy')
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualiza un registro bancario existente.
     */
    public function edit(Request $request, int $id)
    {
        $bankDetail = BankDetail::findOrFail($id);

        $messages = [
            'bank.required'                   => 'Es obligatorio especificar el nombre de la institución bancaria.',
            'bank.string'                     => 'El nombre del banco debe contener caracteres válidos.',
            'bank.max'                        => 'El nombre del banco es demasiado extenso.',
            'account_number.required_without' => 'Debe proporcionar el número de cuenta o tarjeta si el campo CLABE está vacío.',
            'account_number.regex'            => 'El número de cuenta/tarjeta debe contener únicamente números.',
            'account_number.between'          => 'Para cuenta bancaria use 10-11 dígitos. Para tarjeta de débito/crédito use 16 dígitos.',
            'clabe_number.required_without'   => 'Debe proporcionar la clave interbancaria (CLABE) si el número de cuenta está vacío.',
            'clabe_number.regex'              => 'La CLABE debe contener únicamente dígitos numéricos.',
            'clabe_number.size'               => 'La clave interbancaria (CLABE) debe tener exactamente 18 dígitos.',
            'reference.string'                => 'La referencia debe ser un texto válido.',
            'beneficiary.string'              => 'El nombre del beneficiario debe ser un texto válido.',
            'subsidiary.string'               => 'El nombre de la sucursal debe ser un texto válido.',
        ];

        $data = $request->validate([
            'bank'           => ['required', 'string', 'max:255'],
            'account_number' => [
                'nullable',
                'string',
                'required_without:clabe_number',
                'regex:/^[0-9]+$/',
                'between:10,16',
            ],
            'clabe_number' => [
                'nullable',
                'string',
                'required_without:account_number',
                'regex:/^[0-9]+$/',
                'size:18',
            ],
            'reference'   => ['nullable', 'string'],
            'beneficiary' => ['nullable', 'string'],
            'subsidiary'  => ['nullable', 'string'],
        ], $messages);

        //die($bankDetail);
        $bankDetail->update($data);
        return redirect()->route('bankdetails.index');

        /* return response()->json([
                'message' => 'Cuenta bancaria actualizada correctamente',
                'data'    => $bankDetail->load('updatedBy')
            ], 200); */
    }

    /**
     * Elimina un registro bancario.
     */
    public function delete(int $id)
    {
        try {
            $bankDetail = BankDetail::findOrFail($id);
            $bankDetail->delete();

            /* return response()->json([
                'message' => 'Cuenta bancaria eliminada correctamente'
            ], 200); */
            return redirect()->route('bankdetails.index')->with('success', 'Cuenta eliminada correctamente');
        } catch (Exception $e) {
            /* return response()->json([
                'message' => $e->getMessage(),
            ], 500); */
            return redirect()->route('bankdetails.index')->with('error', $e->getMessage());
        }
    }
}
