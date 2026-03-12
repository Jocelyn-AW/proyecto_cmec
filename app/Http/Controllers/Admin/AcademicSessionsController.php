<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Web\BannersController;
use App\Http\Controllers\Controller;
use function Illuminate\Log\log;
use App\Models\AcademicSession;
use Illuminate\Http\Request;
use App\Models\BankDetail;
use Inertia\Inertia;

class AcademicSessionsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 9);
        $academicSessions = AcademicSession::with('sessions')
            ->orderBy('created_at', 'desc')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('topic', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('date'), function ($query) use ($request) {
                $query->whereHas('sessions', function ($q) use ($request) {
                    $q->whereDate('date', $request->date);
                });
            })
            ->when($request->filled('organized_by'), function ($query) use ($request) {
                $query->where('organized_by', 'like', '%' . $request->organized_by . '%');
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->status === 'active');
            })
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('AcademicSessions/Index', [
            'academicSessions' => $academicSessions,
            'filters'  => $request->only(['search', 'date', 'organized_by', 'status']),
        ]);
    }

    public function new()
    {
        $bankDetails = BankDetail::select('id', 'bank', 'account_number', 'clabe_number')->get();
        return Inertia::render('AcademicSessions/AcademicSessionCreate', [
            'bank_details' => $bankDetails
        ]);
    }

    public function store(Request $request)
    {
        try {
            $this->mergeNullableFields($request);

            $validationRules = $this->getValidationArray();
            $validationRules['cover_image'] = 'required|image|mimes:jpeg,png,jpg,webp';

            $data = $request->validate($validationRules, $this->getValidatonMessages());

            $sessions = $data['sessions'];
            unset($data['sessions']);

            $academicSession = AcademicSession::create($data);

            foreach ($sessions as $session) {
                $academicSession->sessions()->create([
                    'date' => $this->formatDateTime($session['date'], $session['time']),
                    'time' => $session['time'],
                ]);
            }

            $this->updateAcademicSessionMedia($academicSession, $request);

            /* if ($request->input('create_banner') === '1' && $request->hasFile('banner_image')) {
                BannersController::createFromEvent(
                    title: $request->input('banner_title', $academicSession->topic),
                    image: $request->file('banner_image'),
                    link: $request->input('banner_link') ?: null,
                    eventId: $academicSession->id,
                    eventType: 'academic_session'
                );
            } */

            return redirect()
                ->route('academicsessions.index')
                ->with('success', 'Sesión académica creada exitosamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Hubo un error al crear la sesión académica.')
                ->withInput();
        }
    }

    public function edit($id)
    {
        $academicSession = AcademicSession::with('sessions')->findOrFail($id);
        $bankDetails = BankDetail::select('id', 'bank', 'account_number', 'clabe_number')->get();

        return Inertia::render('AcademicSessions/AcademicSessionEdit', [
            'academicSession' => $academicSession,
            'bank_details' => $bankDetails
        ]);
    }

    public function update(Request $request)
    {
        try {
            $this->mergeNullableFields($request);
            $validationRules = $this->getValidationArray();
            $data = $request->validate($validationRules, $this->getValidatonMessages());

            $academicSession = AcademicSession::findOrFail($request->id);

            $sessions = $data['sessions'];
            unset($data['sessions']);

            $academicSession->update($data);
            $academicSession->sessions()->delete();

            foreach ($sessions as $session) {
                $academicSession->sessions()->create([
                    'date' => $this->formatDateTime($session['date'], $session['time']),
                    'time' => $session['time'],
                ]);
            }

            $this->updateAcademicSessionMedia($academicSession, $request);

            /* if ($request->input('update_banner') === '1') {

                $bannerImage = $request->hasFile('banner_image') ? $request->file('banner_image') : null;

                // se uso el 'path' fisico de la imagen, la url truena
                $bannerImagePath = null;
                if (!$bannerImage) {
                    $coverMedia = $academicSession->getFirstMedia('academic_sessions_covers');
                    $bannerImagePath = $coverMedia?->getPath();
                }

                $banner = BannersController::updateFromEvent(
                    title: $request->input('banner_title', $academicSession->topic),
                    image: $bannerImage,
                    imagePath: $bannerImagePath,
                    link: $request->input('banner_link') ?: null,
                    eventId: $academicSession->id,
                    eventType: 'academic_session'
                );

                if ($banner === null) {
                    return redirect()
                        ->route('academicsessions.index')
                        ->with('success', 'Sesión actualizada, pero no se pudo crear el banner porque no hay imagen de portada.');
                }
            } */

            return redirect()
                ->route('academicsessions.index')
                ->with('success', 'Sesión académica actualizada exitosamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Hubo un error al actualizar la sesión académica.')
                ->withInput();
        }
    }

    public function delete($id)
    {
        try {
            $academicSession = AcademicSession::findOrFail($id);
            $this->deleteAcademicSessionMedia($academicSession);
            BannersController::deleteFromEvent(eventId: $id, eventType: 'academic_session');
            $academicSession->sessions()->delete();
            $academicSession->delete();

            return redirect()
                ->route('academicsessions.index')
                ->with('success', 'Sesión académica eliminada exitosamente');
        } catch (\Exception $e) {
            return redirect()->route('academicsessions.index')
                ->with('error', 'Hubo un error al eliminar la sesión académica.');
        }
    }

    private function deleteAcademicSessionMedia(AcademicSession $academicSession)
    {
        $academicSession->clearMediaCollection('academic_sessions_covers');
        $academicSession->clearMediaCollection('academic_sessions_gallery');
        $academicSession->clearMediaCollection('academic_sessions_program');
    }

    private function updateAcademicSessionMedia(AcademicSession $academicSession, Request $request)
    {
        if ($request->hasFile('cover_image')) {
            $academicSession->clearMediaCollection('academic_sessions_covers');
            $academicSession->addMediaFromRequest('cover_image')->toMediaCollection('academic_sessions_covers');
        }

        if ($request->hasFile('program_pdf')) {
            $academicSession->clearMediaCollection('academic_sessions_program');
            $academicSession->addMediaFromRequest('program_pdf')->toMediaCollection('academic_sessions_program');
        }
    }

    private function getValidationArray()
    {
        return [
            'topic' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'objectives' => 'nullable|string|max:2000',
            'duration' => 'required|numeric',
            'organized_by' => 'required|string|max:255',
            'sponsored_by' => 'nullable|string|max:255',
            'member_price' => 'required|numeric',
            'guest_price' => 'nullable|numeric',
            'resident_price' => 'nullable|numeric',
            'format' => 'required|string',
            'link' => 'required_if:format,online|nullable|url',
            'address' => 'required_if:format,in_person,hybrid|nullable|string',
            'additional_info' => 'nullable|string',
            'bank_detail_id' => 'required|numeric|exists:bank_details,id',
            'is_active' => 'boolean',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'program_pdf' => 'nullable|mimes:pdf',
            'sessions' => 'required|array|min:1',
            'sessions.*.date' => 'required|date',
            'sessions.*.time' => 'required|date_format:H:i',
        ];
    }

    private function getValidatonMessages()
    {
        return [
            'cover_image.required' => 'La imagen de portada es obligatoria.',
            'program_pdf.mimes' => 'El archivo del programa debe ser un PDF.',
            '*.required' => 'Este campo es obligatorio.',
            'bank_detail_id.exists' => 'Seleccione una cuenta válida.',
        ];
    }

    private function mergeNullableFields(Request $request)
    {
        $request->mergeIfMissing([
            'description' => 'No disponible',
            'is_active' => true,
            'guest_price' => 0,
            'resident_price' => 0,
        ]);
    }

    private function formatDateTime($date, $time)
    {
        $date = date('Y-m-d', strtotime($date));
        return date('Y-m-d H:i:s', strtotime("$date $time"));
    }

    public function statusChange($id)
    {
        $academicSession = AcademicSession::findOrFail($id);
        $academicSession->is_active = !$academicSession->is_active;
        $academicSession->save();

        return redirect()->route('academicsessions.index');
    }
}
