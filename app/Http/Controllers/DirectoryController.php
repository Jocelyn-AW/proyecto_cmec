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
    public function index (Request $request)
    {
        try {
            $user = Auth::user();

            if ($user->role == 'administrador') {
                return Inertia::render('Directory/Admin/Index', [
                    'members' => $this->getAdminData($request)
                ]); 

            } else {

                $member = Member::where('user_id', $user->id)->first()->id;
                $directory = DirectoryData::where('member_id', $member)->first();
                $clinics = Clinic::where('member_id', $member)->get();

                return Inertia::render('Directory/Member/Index', [
                    'member' => $member,
                    'directory' => $directory,
                    'clinics' => $clinics
                ]);
            }
            
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    private function getAdminData(Request $request)
    {
        $search = $request->input('search', null);
        $perPage = $request->input('per_page', 10);
        $status = $request->input('status', '');

        $members = Member::with([
            'directory' => function ($q) {
                $q->withTrashed();
            }, 
            'clinics' => function ($q) {
                $q->withTrashed();
            }]);

        if (!empty($search)) {
            $members->where(function ($query) use ($search){
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('cmec_member_id', 'like', "%{$search}%")
                    ->orWhere('state', 'like', "%{$search}%");
            });   
        }

        if ($status === 'trashed') {
            $members->whereHas('directory', function ($query) {
                $query->onlyTrashed();
            });
        } elseif ($status === '') {
            $members->whereHas('directory'); 
        }


        return $members->withTrashFilter($status)    
            ->paginate($perPage)->through(function ($member) {
                if ($member->directory?->trashed()) {
                    $member->deleted_at = $member->directory->deleted_at;
                }

                return $member;
            })
            ->withQueryString();
    }

    public function delete(int $member_id) {
        try {
            $directory = DirectoryData::where('member_id', $member_id);
            $directory->delete();

            $clinics = Clinic::where('member_id', $member_id);
            $clinics->delete();

            return redirect()
                ->back()
                ->with('success', 'Directorio desaactivado correctamente');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Ocurrio un error al desactivar el directorio');
        }
    }

    public function restore(int $member_id) {
        try {
            $directory = DirectoryData::withTrashed()->where('member_id', $member_id);
            $directory->restore();

            $clinics = Clinic::withTrashed()->where('member_id', $member_id);
            $clinics->restore();

            return redirect()
                ->back()
                ->with('success', 'Directorio activado correctamente');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Ocurrio un error al activar el directorio');
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
