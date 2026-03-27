<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all();
        
        return Inertia::render('Settings/Index', [
            'settings' => $settings
        ]);
    }

    public function update(Request $request)
    {
        try {
            $rules = [
                'stripe_key'            => 'required|string',
                'stripe_secret'         => 'required|string',
                'stripe_webhook_secret' => 'required|string',
            ];

            $messages =  [
                'required' => 'Este campo es obligatorio'
            ];

            $request->validate($rules, $messages);

            Setting::set('stripe_key',            $request->stripe_key);
            Setting::set('stripe_secret',         $request->stripe_secret);
            Setting::set('stripe_webhook_secret', $request->stripe_webhook_secret);

            return back()->with('success', 'Configuración de Stripe actualizada correctamente.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Ocurrió un error al actualizar la configuración')
                ->withInput();
        }
    }
}
