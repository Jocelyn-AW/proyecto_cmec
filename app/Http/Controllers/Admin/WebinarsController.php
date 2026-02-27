<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use function Illuminate\Log\log;
use Illuminate\Http\Request;
use App\Models\BankDetail;
use App\Models\Webinar;
use Inertia\Inertia;

class WebinarsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', null);

        $webinars = Webinar::orderBy('created_at', 'desc')
            ->when($search, function ($query, $search) {
                $query->where('topic', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            })
            ->paginate($perPage)
            ->withqueryString();

        return Inertia::render('Webinars/Index', [
            'webinars' => $webinars,
        ]);
    }

    public function new()
    {
        $bankDetails = BankDetail::select('id', 'bank', 'account_number', 'clabe_number')
            ->get();
        return Inertia::render('Webinars/WebinarCreate', [
            'bank_details' => $bankDetails
        ]);
    }

    public function store(Request $request)
    {
        try {
            //todo: add payment_methods
            $this->mergeNullableFields($request);

            $validationRules = $this->getValidationArray();
            $validationRules['cover_image'] = 'required|image|mimes:jpeg,png,jpg,webp';

            $data = $request->validate($validationRules, $this->getValidatonMessages());

            $data['date'] = $this->formatDateTime($data['date'], $data['time']);
            unset($data['time']);

            $webinar = Webinar::create($data);

            $this->updateWebinarMedia($webinar, $request);

            return redirect()
                ->route('webinars.index')
                ->with('success', 'Webinar creado exitosamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {

            return redirect()
                ->back()
                ->with('error', 'Hubo un error al crear el webinar. Por favor intenta de nuevo.')
                ->withInput();
        }
    }

    public function edit($id)
    {
        $webinar = Webinar::findOrFail($id);
        //todo: load payment_methods
        $bankDetails = BankDetail::select('id', 'bank', 'account_number', 'clabe_number')
            ->get();

        return Inertia::render('Webinars/WebinarEdit', [
            'webinar' => $webinar,
            'bank_details' => $bankDetails
        ]);
    }

    public function update(Request $request)
    {
        try {
            $this->mergeNullableFields($request);

            $validationRules = $this->getValidationArray();
            $data = $request->validate($validationRules, $this->getValidatonMessages());

            $data['date'] = $this->formatDateTime($data['date'], $data['time']);
            unset($data['time']);

            $webinar = Webinar::findOrFail($request->id);
            $webinar->update($data);

            $this->updateWebinarMedia($webinar, $request);

            return redirect()
                ->route('webinars.index')
                ->with('success', 'Webinar actualizado exitosamente');
        } catch (\Illuminate\Validation\ValidationException $e) {

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {

            return redirect()
                ->back()
                ->with('error', 'Hubo un error al actualizar el webinar. Intenta de nuevo más tarde.')
                ->withInput();
        }
    }

    public function delete($id)
    {
        try {
            $webinar = Webinar::findOrFail($id);
            $this->deleteWebinarMedia($webinar);
            $webinar->delete();

            return redirect()
                ->route('webinars.index')
                ->with('success', 'Webinar eliminado exitosamente');
        } catch (\Exception $e) {

            return redirect()
                ->route('webinars.index')
                ->with('error', 'Hubo un error al eliminar el webinar. Intenta de nuevo más tarde.');
        }
    }

    public function gallery($id)
    {
        $webinar = Webinar::findOrFail($id);

        return Inertia::render('Webinars/WebinarGallery', [
            'webinar' => $webinar->only('id', 'topic'),
            'images' => $webinar->getMedia('webinars_gallery')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'url' => $media->getUrl()
                ];
            }),
        ]);
    }

    public function updateGallery(Request $request, $id)
    {
        try {
            $webinar = Webinar::findOrFail($id);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $webinar->addMedia($image)->toMediaCollection('webinars_gallery');
                }
            }

            return redirect()
                ->route('webinars.gallery', $id)
                ->with('success', 'Galería actualizada exitosamente');
        } catch (\Exception $e) {

            return redirect()
                ->route('webinars.gallery', $id)
                ->with('error', 'Hubo un error al actualizar la galería. Intenta de nuevo más tarde.');
        }
    }

    public function deleteGalleryImage($id, $mediaId)
    {
        try {
            $webinar = Webinar::findOrFail($id);
            $mediaItem = $webinar->getMedia('webinars_gallery')->where('id', $mediaId)->first();

            if ($mediaItem) {
                $mediaItem->delete();
                return redirect()
                    ->route('webinars.gallery', $id)
                    ->with('success', 'Imagen eliminada exitosamente');
            } else {
                return response()->json(['success' => false, 'message' => 'Imagen no encontrada.'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar la imagen.'], 500);
        }
    }

    private function deleteWebinarMedia(Webinar $webinar)
    {
        $webinar->clearMediaCollection('webinars_covers');
        $webinar->clearMediaCollection('webinars_gallery');
        $webinar->clearMediaCollection('webinars_sponsors_logos');
        $webinar->clearMediaCollection('webinars_program');
    }

    private function updateWebinarMedia(Webinar $webinar, Request $request)
    {
        if ($request->hasFile('cover_image')) {
            $webinar->clearMediaCollection('webinars_covers');
            $webinar->addMediaFromRequest('cover_image')->toMediaCollection('webinars_covers');
        }

        if ($request->hasFile('sponsor_logos')) {
            $webinar->clearMediaCollection('webinars_sponsors_logos');
            foreach ($request->file('sponsor_logos') as $logo) {
                $webinar->addMedia($logo)->toMediaCollection('webinars_sponsors_logos');
            }
        }

        if ($request->hasFile('program_pdf')) {
            $webinar->clearMediaCollection('webinars_program');
            $webinar->addMediaFromRequest('program_pdf')->toMediaCollection('webinars_program');
        }
    }

    private function getValidationArray()
    {
        return [
            'topic' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'objectives' => 'nullable|string|max:1000',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'duration' => 'required|numeric|max:255',
            'organized_by' => 'required|string|max:255',
            'sponsored_by' => 'nullable|string|max:255',
            'member_price' => 'required|numeric',
            'guest_price' => 'nullable|numeric',
            'resident_price' => 'nullable|numeric',
            'link' => 'nullable|url',
            'bank_detail_id' => 'required|numeric|exists:bank_details,id',
            //Archivos
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'program_pdf' => 'nullable|mimes:pdf',
            'sponsor_logos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp'
        ];
    }

    private function getValidatonMessages()
    {
        return [
            'cover_image.required' => 'La imagen de portada es obligatoria.',
            'cover_image.image' => 'El archivo debe ser una imagen.',
            'cover_image.mimes' => 'La imagen debe ser un archivo de tipo: jpeg, png, jpg, webp.',
            'program_pdf.mimes' => 'El archivo del programa debe ser un PDF.',
            '*.required' => 'El campo es obligatorio.',
            '*.string' => 'El campo debe ser una cadena de texto.',
            '*.max' => 'El campo no debe exceder los :max caracteres.',
            '*.numeric' => 'El campo debe ser un número.',
            '*.date' => 'El campo debe ser una fecha válida.',
            'bank_detail_id.exists' => 'Seleccione una cuenta válida',
            '*.url' => 'El campo debe ser una URL válida.',
        ];
    }

    private function mergeNullableFields(Request $request)
    {
        $request->mergeIfMissing([
            'description' => 'No disponible',
            'objectives' => null,
            'sponsored_by' => null,
            'guest_price' => 0,
            'resident_price' => 0,
            'link' => null
        ]);
    }

    private function formatDateTime($date, $time)
    {
        $date = date('Y-m-d', strtotime($date));
        return date('Y-m-d H:i:s', strtotime("$date $time"));
    }
}
