<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\HandlesSponsorMedia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicSession;
use Illuminate\Http\Request;
use App\Models\BankDetail;
use Inertia\Inertia;
use Exception;

class AcademicSessionsController extends Controller
{
    use HandlesSponsorMedia;

    // ---------------------------------------------
    // CRUD
    // ---------------------------------------------

    public function index(Request $request)
    {
        $academicSessions = $this->addFilters($request);

        return Inertia::render('AcademicSessions/Index', [
            'academicSessions' => $academicSessions,
        ]);
    }

    public function new()
    {
        $bankDetails = BankDetail::select('id', 'bank', 'account_number', 'clabe_number')
            ->get();

        return Inertia::render('AcademicSessions/AcademicSessionCreate', [
            'bank_details' => $bankDetails,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $this->mergeNullableFields($request);

            $validationRules = $this->getValidationArray();
            $validationRules['cover_image'] = 'required|image|mimes:jpeg,png,jpg,webp';

            $data = $request->validate($validationRules, $this->getValidationMessages());

            $academicSession = AcademicSession::create($data);

            $academicSession->sessions()->createMany(
                $this->formatSessions($request->sessions)
            );

            $this->updateAcademicSessionMedia($academicSession, $request);
            $this->updateSponsorMedia($academicSession, $request);

            return redirect()
                ->route('academicsessions.index')
                ->with('success', 'Sesión académica creada exitosamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            Log::error('Error al crear sesión académica', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Hubo un error al crear la sesión académica. Por favor intenta de nuevo.')
                ->withInput();
        }
    }

    public function edit($id)
    {
        $academicSession = AcademicSession::withTrashed()->findOrFail($id);

        $bankDetails = BankDetail::select('id', 'bank', 'account_number', 'clabe_number')
            ->get();

        return Inertia::render('AcademicSessions/AcademicSessionEdit', [
            'academicSession' => $academicSession->load('sessions'),
            'bank_details'    => $bankDetails,
        ]);
    }

    public function update(Request $request)
    {
        try {
            DB::beginTransaction();

            $academicSession = AcademicSession::findOrFail($request->id);
            $this->ensureNotTrashed($academicSession);

            $this->mergeNullableFields($request);

            $data = $request->validate(
                $this->getValidationArray(),
                $this->getValidationMessages()
            );

            $academicSession = AcademicSession::findOrFail($request->id);
            $academicSession->update($data);

            $academicSession->sessions()->delete();
            $academicSession->sessions()->createMany(
                $this->formatSessions($request->sessions)
            );

            $this->updateAcademicSessionMedia($academicSession, $request);
            $this->updateSponsorMedia($academicSession, $request);

            DB::commit();

            return redirect()
                ->route('academicsessions.index')
                ->with('success', 'Sesión académica actualizada exitosamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar sesión académica', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Hubo un error al actualizar la sesión académica. Intenta de nuevo más tarde.')
                ->withInput();
        }
    }

    public function delete($id)
    {
        try {
            $academicSession = AcademicSession::findOrFail($id);
            $academicSession->delete();

            return redirect()
                ->route('academicsessions.index')
                ->with('success', 'Sesión académica eliminada exitosamente');
        } catch (Exception $e) {
            return redirect()
                ->route('academicsessions.index')
                ->with('error', 'Hubo un error al eliminar la sesión académica. Intenta de nuevo más tarde.');
        }
    }

    public function restore(int $id)
    {
        try {
            $academicSession = AcademicSession::withTrashed()->findOrFail($id);
            $academicSession->restore();

            return redirect()
                ->route('academicsessions.index')
                ->with('success', 'Sesión académica restaurada exitosamente');
        } catch (Exception $e) {
            return redirect()
                ->route('academicsessions.index')
                ->with('error', 'Hubo un error al restaurar la sesión académica. Intenta de nuevo más tarde.');
        }
    }

    public function changeStatus($id)
    {
        try {
            $academicSession = AcademicSession::findOrFail($id);
            $this->ensureNotTrashed($academicSession);
            
            $academicSession->is_active = !$academicSession->is_active;
            $academicSession->save();

            return redirect()->route('academicsessions.index');
        } catch (Exception $e) {
            return redirect()
                ->route('academicsessions.index')
                ->with('error', 'Hubo un error al actualizar la sesión académica. Intenta de nuevo más tarde.');
        }
    }

    // ---------------------------------------------
    // PRIVATE: Filters / Queries
    // ---------------------------------------------

    private function addFilters(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search  = $request->get('search', null);
        $status  = $request->input('status', '');

        $query = AcademicSession::orderBy('created_at', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('topic', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('organized_by', 'like', "%{$search}%");
            });
        }

        $query->with('sessions');

        return $query
            ->withTrashFilter($status)
            ->paginate($perPage)
            ->withQueryString();
    }

    // ---------------------------------------------
    // PRIVATE: Media
    // ---------------------------------------------

    private function updateAcademicSessionMedia(AcademicSession $academicSession, Request $request): void
    {
        if ($request->hasFile('cover_image')) {
            $academicSession->clearMediaCollection('academic_sessions_covers');
            $academicSession->addMediaFromRequest('cover_image')->toMediaCollection('academic_sessions_covers');
        }

        if ($request->hasFile('cover_preview_image')) {
            $academicSession->clearMediaCollection('academic_sessions_previews');
            $academicSession->addMediaFromRequest('cover_preview_image')->toMediaCollection('academic_sessions_previews');
        }

        if ($request->hasFile('program_pdf')) {
            $academicSession->clearMediaCollection('academic_sessions_program');
            $academicSession->addMediaFromRequest('program_pdf')->toMediaCollection('academic_sessions_program');
        }
    }

    // ---------------------------------------------
    // PRIVATE: Validation
    // ---------------------------------------------

    private function getValidationArray(): array
    {
        return array_merge([
            'topic'           => 'required|string|max:255',
            'description'     => 'required|string|max:5000',
            'objectives'      => 'nullable|string|max:2000',
            'duration'        => 'required|numeric|max:255',
            'organized_by'    => 'required|string|max:255',
            'sponsored_by'    => 'nullable|string|max:255',
            'member_price'    => 'required|numeric',
            'guest_price'     => 'nullable|numeric',
            'resident_price'  => 'nullable|numeric',
            'format'          => 'required|string',
            'link'            => 'required_if:format,online|nullable|url',
            'address'         => 'required_if:format,in_person,hybrid|nullable|string',
            'additional_info' => 'nullable|string',
            'bank_detail_id'  => 'required|numeric|exists:bank_details,id',
            'is_active'       => 'boolean',
            // Sesiones
            'sessions'        => 'required|array|min:1',
            'sessions.*.date' => 'required|date',
            'sessions.*.time' => 'required|date_format:H:i',
            // Archivos
            'cover_image'         => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'cover_preview_image' => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'program_pdf'         => 'nullable|mimes:pdf',
        ], $this->sponsorValidationRules());
    }

    private function getValidationMessages(): array
    {
        return [
            'cover_image.required'  => 'La imagen de portada es obligatoria.',
            'cover_image.image'     => 'El archivo debe ser una imagen.',
            '*.mimes'               => 'La imagen debe ser un archivo de tipo: jpeg, png, jpg, webp.',
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

    private function mergeNullableFields(Request $request): void
    {
        $request->mergeIfMissing([
            'description'     => 'No disponible',
            'objectives'      => null,
            'sponsored_by'    => null,
            'guest_price'     => 0,
            'resident_price'  => 0,
            'link'            => null,
            'address'         => null,
            'additional_info' => null,
            'is_active'       => true,
        ]);
    }

    // ---------------------------------------------
    // PRIVATE: Helpers
    // ---------------------------------------------

    private function formatSessions(array $sessions): array
    {
        return array_map(fn($s) => [
            'date' => $this->formatDateTime($s['date'], $s['time']),
            'time' => $s['time'],
        ], $sessions);
    }

    private function formatDateTime(string $date, string $time): string
    {
        $date = date('Y-m-d', strtotime($date));
        return date('Y-m-d H:i:s', strtotime("$date $time"));
    }

    // ---------------------------------------------
    // PRIVATE: Guards
    // ---------------------------------------------

    private function ensureNotTrashed(AcademicSession $academicSession): void
    {
        if ($academicSession->trashed()) {
            abort(403, 'No puedes modificar una sesión academica eliminada.');
        }
    }
}
