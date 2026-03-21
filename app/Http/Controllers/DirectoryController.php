<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\DirectoryData;
use App\Models\Member;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class DirectoryController extends Controller
{
    public function index ()
    {
        try {
            $user = Auth::user();

            if ($user->role == 'administrador') {
                $view = 'Directory/Admin/Index';
                $members = Member::with('directory', 'clinics')->get();

                return Inertia::render($view, [
                    'members' => $members
                ]);
            } else {
                $member = Member::where('user_id', $user->id)->first()->id;
                $directory = DirectoryData::where('member_id', $member)->first();
                $clinics = Clinic::where('member_id', $member)->get();
                $view = 'Directory/Member/Index';

                return Inertia::render($view, [
                    'member' => $member,
                    'directory' => $directory,
                    'clinics' => $clinics
                ]);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function saveChanges(Request $request)
    {
        try {
            $id = $request->input('id');

            $data = $request->validate(
                $this->getValidationRules(), $this->getValidationMsg()
            );

            DirectoryData::updateOrCreate(['id' => $id], $data);

            foreach ($data['clinics'] as $clinic) {
                Clinic::updateOrCreate(['id' => $id], $clinic);
            }
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }

    }

    private function getValidationRules () 
    {
        $rules = [
            'member_id' => 'required|string|exists:members,id',
            'name' => 'required|string',
            'specialty' => 'required|string',
            'state' => 'required|string',
            'city' => 'required|string',

            'clinics' => 'required|array|min:1',
            'clinics.*.hospital_name' => 'nullable|string',
            'clinics.*.address' => 'required|string',
            'clinics.*.phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9|max:15',
            'clinics.*.schedule' => 'required|string',
        ];

        return $rules;
    }

    private function getValidationMsg () 
    {
        return [
            'required' => 'Este campo es obligatorio',
            '*.exists' => 'Este diretorio no coincide con un miembro activo',
            'string' => 'El campo debe ser una cadena de texto.',
            'regex' => 'Ingrese un número válido.',
            'max' => 'Has excedido el numero de caracteres',
        ];
    }

    public function uploadProfile(Request $request, int $id)
    {
        $request->validate([
            'profile' => 'required|image|mimes:jpeg,png,jpg,webp|max:1024',
        ]);

        $model = DirectoryData::findOrFail($id);

        $model->clearMediaCollection('directory_profile');
        $model->addMediaFromRequest('profile')->toMediaCollection('directory_profile');

        return redirect()->route('directory');
    }
}
