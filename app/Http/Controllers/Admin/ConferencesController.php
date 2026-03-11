<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BankDetail;
use App\Models\Conference;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ConferencesController extends Controller
{
    public function index(Request $request)
    {
        $conferences = $this->addFilters($request);

        return Inertia::render('Conferences/Index', [
            'conferences' => $conferences
        ]);
    }

    public function new()
    {
        $bankDetails = BankDetail::select('id', 'bank', 'account_number', 'clabe_number')
                        ->get();
        return Inertia::render('Conferences/CreateConference', [
            'bank_details' => $bankDetails
        ]);
    }

    public function edit(int $id)
    {
        $conference = Conference::findOrFail($id);
        $bankDetails = BankDetail::select('id', 'bank', 'account_number', 'clabe_number')
                        ->get();

        return Inertia::render('Conferences/EditConference', [
            'conference' => $conference->load('sessions'),
            'bank_details' => $bankDetails
        ]);
    }

    private function addFilters(Request $request) : LengthAwarePaginator
    {
        $search = $request->input('search', null);
        $perPage = $request->input('per_page', 10);

        $conferences = Conference::orderBy('created_at', 'desc');

        if (!empty($search)) {
            $conferences->where('name', 'like', "%{$search}%")
                ->orWhere('main_topic', 'like', "%{$search}%")
                ->orWhere('organized_by', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%");
        }

        $conferences->with('sessions');

        return $conferences->paginate($perPage)->withQueryString();

    }

    public function store(Request $request)
    {
        try {
            $this->mergeNullableFields($request);
            $data = $request->validate(
                $this->getValidationRules($request), $this->getValidationMessages()
            );

            $conference = Conference::create($data);
            $conference->sessions()->createMany($request->sessions);

            $this->updateConferenceMedia($conference, $request);
            
            return redirect()
                    ->route('conferences.index')
                    ->with('success', 'Congreso creado exitosamente');
        } catch (ValidationException $e) {
            Log::error($e->getMessage());
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Ocurrió un error al crear el congreso')
                ->withInput();
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $this->mergeNullableFields($request);
            $data = $request->validate(
                $this->getValidationRules($request), $this->getValidationMessages()
            );

            $conference = Conference::findOrFail($id);
            $conference->update($data);

            if (!empty($conference->sessions)) {
                $conference->sessions()->delete();
            }
            $conference->sessions()->createMany($request->sessions);

            $this->updateConferenceMedia($conference, $request);

            return redirect()
                ->route('conferences.index')
                ->with('success', 'Congreso actualizado exitosamente');
        } catch (ValidationException $e) {
            Log::error($e->getMessage());
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Ocurrió un error al actualizar el congreso')
                ->withInput();
        }
    }

    public function delete(int $id)
    {
        try {
            $conference = Conference::findOrFail($id);
            $this->deleteConferenceMedia($conference);
            
            if (!empty($conference->sessions)) {
                $conference->sessions()->delete();
            }

            $conference->delete();

            return redirect()
                ->route('conferences.index')
                ->with('success', 'Congreso eliminado exitosamente');
        } catch (Exception $e) {
            return redirect()
                ->route('conferences.index')
                ->with('success', 'Ocurrió un error al eliminar el congreso');
        }
    }

    public function changeStatus(int $id) {
        try {
            $conference = Conference::findOrFail($id);

            $conference->is_active = !$conference->is_active;
            $conference->update();

            return redirect()->route('conferences.index');

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()
                ->route('conferences.index')
                ->with('error', 'Hubo un error al actualizar el congreso. Intenta de nuevo más tarde.');
        }
    }

    private function mergeNullableFields(Request $request)
    {
        $request->mergeIfMissing([
            'google_coords' => '',
            'date' => now(),
            'specialties' => '',
            'is_active' => true
        ]);
    }

    private function getValidationRules(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'main_topic' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'format' => 'required|string',
            'held_by' => 'required_if:format,in_person,hybrid|nullable|string',
            'address' => 'required_if:format,in_person,hybrid|nullable|string',
            'google_coords' => 'nullable|string',
            'link' => 'required_if:format,online|nullable|url',
            'organized_by' => 'required|string',
            'member_price' => 'required|numeric',
            'resident_price' => 'nullable|numeric',
            'guest_price' => 'required|numeric',
            'surgeon_price' => 'required|numeric',
            'nurse_price' => 'nullable|numeric',
            'is_active' => 'required|boolean',
            'additional_comments' => 'nullable|string',
            'bank_detail_id' => 'required|numeric|exists:bank_details,id',
            //Horarios
            'sessions'         => 'required|array|min:1',
            'sessions.*.date'  => 'required|date',
            'sessions.*.time'  => 'required|date_format:H:i',            
            //Archivos
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'program_pdf' => 'nullable|mimes:pdf',
            //Patocinadores
            'platinum_sponsors.*' => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'golden_sponsors.*' => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'silver_sponsors.*' => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'platinum_delete.*' => 'nullable|numeric',
            'golden_delete.*' => 'nullable|numeric',
            'silver_delete.*' => 'nullable|numeric',
        ];

        if ($request->isMethod('post')) {
            $rules['cover_image'] = 'required|image|mimes:jpeg,png,jpg,webp';
        }   

        return $rules;
    }

    private function getValidationMessages()
    {
        return [
            'cover_image.required' => 'La imagen oficial es obligatoria.',
            'cover_image.image' => 'El archivo debe ser una imagen.',
            'cover_image.mimes' => 'La imagen debe ser un archivo de tipo: jpeg, png, jpg, webp.',
            'program_pdf.mimes' => 'El programa del congreso debe ser un PDF.',
            'bank_detail_id.exists' => 'Seleccione una cuenta válida',
            '*.mimes' => 'La imagen debe ser un archivo de tipo: jpeg, png, jpg, webp.',
            '*.required' => 'Este campo es obligatorio.',
            '*.string' => 'El campo debe ser una cadena de texto.',
            '*.max' => 'El campo no debe exceder los :max caracteres.',
            '*.numeric' => 'El campo debe ser un número.',
            '*.required_if' => 'Este campo es obligatorio.',
            '*.url' => 'El campo debe ser una URL válida.',
            'sessions.*.date' => 'El campo debe ser una fecha válida.',
            'sessions.*.time' => 'Selecciona un horario válido',
        ];
    }

    private function updateConferenceMedia(Conference $conference, Request $request)
    {
        if ($request->hasFile('cover_image')) {
            $conference->clearMediaCollection('conference_covers');
            $conference ->addMediaFromRequest('cover_image')
                        ->toMediaCollection('conference_covers');
        }

        if ($request->hasFile('platinum_sponsors')) {
            foreach ($request->file('platinum_sponsors') as $logo) {
                $conference->addMedia($logo)->toMediaCollection('conference_platinum_sponsors');
            }
        }

        if (!empty($request->input('platinum_delete'))) {
            foreach ($request->get('platinum_delete') as $item) {
                $media = $conference->getMedia('conference_platinum_sponsors')->where('id', $item)->first();
                if ($media) $media->delete();
            }
        }

        //Oro
        if ($request->hasFile('golden_sponsors')) {
            foreach ($request->file('golden_sponsors') as $logo) {
                $conference->addMedia($logo)->toMediaCollection('conference_golden_sponsors');
            }
        }

        if (!empty($request->input('golden_delete'))) {
            foreach ($request->get('golden_delete') as $item) {
                $media = $conference->getMedia('conference_golden_sponsors')->where('id', $item)->first();
                if ($media) $media->delete();
            }
        }

        //Plata
        if ($request->hasFile('silver_sponsors')) {
            foreach ($request->file('silver_sponsors') as $logo) {
                $conference->addMedia($logo)->toMediaCollection('conference_silver_sponsors');
            }
        }

        if (!empty($request->input('silver_delete'))) {
            foreach ($request->get('silver_delete') as $item) {
                $media = $conference->getMedia('conference_silver_sponsors')->where('id', $item)->first();
                if ($media) $media->delete();
            }
        }

        if ($request->hasFile('program_pdf')) {
            $conference->clearMediaCollection('conference_program');
            $conference ->addMediaFromRequest('program_pdf')
                        ->toMediaCollection('conference_program');
        }
    }

    public function gallery(int $id)
    {
        $conference = Conference::findOrFail($id);

        return Inertia::render('Conferences/Gallery', [
            'conference' => $conference->only('id', 'name'),
            'images' => $conference->getMedia('conference_gallery')
            ->map(function ($media) {
                return [
                    'id' => $media->id,
                    'url' => $media->getUrl()
                ];
            }),
        ]);
    }

    public function updateGallery(Request $request, int $id)
    {
        try {
            $conference = Conference::findOrFail($id);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $conference ->addMedia($image)
                                ->toMediaCollection('conference_gallery');
                }
            }

            return redirect()
                ->route('conferences.gallery', $id)
                ->with('success', 'Imagenes subidas exitosamente');

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()
                ->route('conferences.gallery', $id)
                ->with('error', 'Ocurrió un error al subir las imagenes.');
        }
    }

    public function deleteGalleryImage(int $id, int $mediaId)
    {
        try {
            $conference = Conference::findOrFail($id);
            $mediaItem = $conference->getMedia('conference_gallery')
                                    ->where('id', $mediaId)
                                    ->first();
            if ($mediaItem) {
                $mediaItem->delete();
                return redirect()
                    ->route('conferences.gallery', $id)
                    ->with('success', 'Imagen eliminada exitosamente');
            } else {
                throw new ModelNotFoundException('Image Not Found');
            }
        } catch (ModelNotFoundException $e) {
            return redirect()
                    ->route('conferences.gallery', $id)
                    ->with('error', 'Ocurrió un error al eliminar la imagen');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()
                    ->route('conferences.gallery', $id)
                    ->with('error', 'Ocurrió un error al eliminar la imagen');
        }
    }

    private function deleteConferenceMedia(Conference $conference)
    {
        $conference->clearMediaCollection('conference_covers');
        $conference->clearMediaCollection('conference_gallery');
        $conference->clearMediaCollection('conference_platinum_sponsors');
        $conference->clearMediaCollection('conference_golden_sponsors');
        $conference->clearMediaCollection('conference_silver_sponsors');
        $conference->clearMediaCollection('conference_program');
    }
}
