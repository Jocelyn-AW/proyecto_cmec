<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Web\BannersController;
use App\Traits\HandlesSponsorMedia;
use App\Http\Controllers\Controller;
use function Illuminate\Log\log;
use Illuminate\Http\Request;
use App\Models\BankDetail;
use App\Models\Webinar;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class WebinarsController extends Controller
{
    use HandlesSponsorMedia;

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $webinars = Webinar::with('sessions')
            ->orderBy('created_at', 'desc')
            // busqueda por topic
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('topic', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })

            // FILTROS
            // fecha exacta (YYYY-MM-DD)
            ->when($request->filled('date'), function ($query) use ($request) {
                $query->whereHas('sessions', function ($q) use ($request) {
                    $q->whereDate('date', $request->date);
                });
            })

            // organized_by
            ->when($request->filled('organized_by'), function ($query) use ($request) {
                $query->where('organized_by', 'like', '%' . $request->organized_by . '%');
            })

            // estado activo/inactivo
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->status === 'active');
            })

            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Webinars/Index', [
            'webinars' => $webinars,
            'filters'  => $request->only(['search', 'date', 'organized_by', 'status']),
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
            //add payment_methods
            $this->mergeNullableFields($request);

            $validationRules = $this->getValidationArray();
            $validationRules['cover_image'] = 'required|image|mimes:jpeg,png,jpg,webp';

            $data = $request->validate($validationRules, $this->getValidatonMessages());

            $sessions = $data['sessions'];
            unset($data['sessions']);

            $webinar = Webinar::create($data);

            foreach ($sessions as $session) {
                $webinar->sessions()->create([
                    'date' => $this->formatDateTime($session['date'], $session['time']),
                    'time' => $session['time'],
                ]);
            }

            $this->updateWebinarMedia($webinar, $request);
            $this->updateSponsorMedia($webinar, $request);

            /* if ($request->input('create_banner') === '1' && $request->hasFile('banner_image')) {
                BannersController::createFromEvent(
                    title: $request->input('banner_title', $webinar->topic),
                    image: $request->file('banner_image'),
                    link: $request->input('banner_link') ?: null,
                    eventId: $webinar->id,
                    eventType: 'webinar'
                );
            } */

            return redirect()
                ->route('webinars.index')
                ->with('success', 'Webinar creado exitosamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error al crear webinar', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Hubo un error al crear el webinar. Por favor intenta de nuevo.')
                ->withInput();
        }
    }

    public function edit($id)
    {
        $webinar = Webinar::with('sessions')->findOrFail($id);
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

            $webinar = Webinar::findOrFail($request->id);

            $sessions = $data['sessions'];
            unset($data['sessions']);

            $webinar->update($data);

            $webinar->sessions()->delete();

            foreach ($sessions as $session) {
                $webinar->sessions()->create([
                    'date' => $this->formatDateTime($session['date'], $session['time']),
                    'time' => $session['time'],
                ]);
            }

            $this->updateWebinarMedia($webinar, $request);
            $this->updateSponsorMedia($webinar, $request);

            /* if ($request->input('update_banner') === '1') {

                $bannerImage = $request->hasFile('banner_image') ? $request->file('banner_image') : null;

                $bannerImagePath = null;
                if (!$bannerImage) {
                    $coverMedia = $webinar->getFirstMedia('webinars_covers');
                    $bannerImagePath = $coverMedia?->getPath();
                }

                $banner = BannersController::updateFromEvent(
                    title: $request->input('banner_title', $webinar->topic),
                    image: $bannerImage,
                    imagePath: $bannerImagePath,
                    link: $request->input('banner_link') ?: null,
                    eventId: $webinar->id,
                    eventType: 'webinar'
                );

                if ($banner === null) {
                    return redirect()
                        ->route('webinars.index')
                        ->with('success', 'Webinar actualizado, pero no se pudo crear el banner porque no hay imagen de portada.');
                }
            } */

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
            $this->deleteSponsorMedia($webinar);
            BannersController::deleteFromEvent(eventId: $id, eventType: 'webinar');
            $webinar->sessions()->delete();
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
            'description' => 'required|string|max:5000',
            'objectives' => 'nullable|string|max:2000',
            'duration' => 'required|numeric|max:255',
            'organized_by' => 'required|string|max:255',
            'sponsored_by' => 'nullable|string|max:255',
            'member_price' => 'required|numeric',
            'guest_price' => 'nullable|numeric',
            'resident_price' => 'nullable|numeric',
            'format'          => 'required|string',
            'link'            => 'required_if:format,online|nullable|url',
            'address'         => 'required_if:format,in_person,hybrid|nullable|string',
            'additional_info' => 'nullable|string',
            'bank_detail_id' => 'required|numeric|exists:bank_details,id',
            'is_active' => 'boolean',
            //Archivos
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'program_pdf' => 'nullable|mimes:pdf',
            'sponsor_logos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp',
            //Tiempo
            'sessions' => 'required|array|min:1',
            'sessions.*.date' => 'required|date',
            'sessions.*.time' => 'required|date_format:H:i',
            // sponsors
            $this->sponsorValidationRules(),
        ];
    }

    private function getValidatonMessages()
    {
        return [
            'cover_image.required'  => 'La imagen de portada es obligatoria.',
            'cover_image.image'     => 'El archivo debe ser una imagen.',
            'cover_image.mimes'     => 'La imagen debe ser un archivo de tipo: jpeg, png, jpg, webp.',
            'program_pdf.mimes'     => 'El archivo del programa debe ser un PDF.',
            '*.required'            => 'Este campo es obligatorio.',
            '*.required_if'         => 'Este campo es obligatorio.',
            '*.string'              => 'El campo debe ser una cadena de texto.',
            '*.max'                 => 'El campo no debe exceder los :max caracteres.',
            '*.numeric'             => 'El campo debe ser un número.',
            '*.url'                 => 'El campo debe ser una URL válida.',
            'sessions.*.date'       => 'El campo debe ser una fecha válida.',
            'sessions.*.time'       => 'Selecciona un horario válido.',
            'bank_detail_id.exists' => 'Seleccione una cuenta válida.',
            'is_active.boolean'     => 'El estado de activación solo admite verdadero/falso.',
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
            'link' => null,
            'address' => null,
            'additional_info' => null,
            'is_active' => true,
        ]);
    }

    private function formatDateTime($date, $time)
    {
        $date = date('Y-m-d', strtotime($date));
        return date('Y-m-d H:i:s', strtotime("$date $time"));
    }

    public function statusChange($id)
    {
        try {
            $webinar = Webinar::findOrFail($id);

            $webinar->is_active = !$webinar->is_active;
            $webinar->update();

            return redirect()->route('webinars.index');
        } catch (\Exception $e) {
            return redirect()
                ->route('webinars.index')
                ->with('error', 'Hubo un error al actualizar el webinar. Intenta de nuevo más tarde.');
        }
    }
}
