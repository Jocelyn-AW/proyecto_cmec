<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankDetail;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Course;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function Illuminate\Log\log;

class CoursesController extends Controller
{
    public function index(Request $request)
    {
        $courses = $this->addFilters($request);

        return Inertia::render('Courses/Index', [
            'courses' => $courses,
        ]);
    }

    private function addFilters(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', null);
        $status = $request->input('status', '');

        $course = Course::orderBy('created_at', 'desc');

        if (!empty($search)) {
            $course->where('topic', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('organized_by', 'like', "%{$search}%");
        }

        $course->with('sessions');

        return $course
            ->withTrashFilter($status)    
            ->paginate($perPage)
            ->withQueryString();
    }

    public function new()
    {
        $bankDetails = BankDetail::select('id', 'bank', 'account_number', 'clabe_number')
                        ->get();

        return Inertia::render('Courses/CourseCreate', [
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
            $course = Course::create($data);

            $course->sessions()->createMany($request->sessions);

            $this->updateCourseMedia($course, $request);

            return redirect()
                ->route('courses.index')
                ->with('success', 'Curso creado exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            log('e', array($e->getMessage()));
            return redirect()
            ->back()
            ->with('error', 'Hubo un error al crear el curso. Por favor intenta de nuevo.')
            ->withInput();
        }
    }

    public function edit($id)
    {
        $course = Course::withTrashed()->findOrFail($id);
        $bankDetails = BankDetail::select('id', 'bank', 'account_number', 'clabe_number')
                        ->get();

        return Inertia::render('Courses/CourseEdit', [
            'course' => $course->load('sessions'),
            'bank_details' => $bankDetails
        ]);
    }

    public function update(Request $request) {
        try {
            DB::beginTransaction();       
            $this->mergeNullableFields($request);

            $validationRules = $this->getValidationArray();
            $data = $request->validate($validationRules, $this->getValidatonMessages());

            $course = Course::findOrFail($request->id);
            $course->update($data);

            if (!empty($course->sessions)) {
                $course->sessions()->delete();
            }
            $course->sessions()->createMany($request->sessions);

            $this->updateCourseMedia($course, $request);

            DB::commit();

            return redirect()
                ->route('courses.index')
                ->with('success', 'Curso actualizado exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            log('e', array($e->getMessage()));
            DB::rollBack();
            return redirect()
            ->back()
            ->with('error', 'Hubo un error al actualizar el curso. Intenta de nuevo más tarde.')
            ->withInput();
        }
    }

    public function delete($id)
    {
        try {
            $course = Course::findOrFail($id);
            $course->delete();

            return redirect()
                ->route('courses.index')
                ->with('success', 'Curso desactivado exitosamente');

        } catch (\Exception $e) {

            return redirect()
                ->route('courses.index')
                ->with('error', 'Hubo un error al desactivar el curso. Intenta de nuevo más tarde.');
        }
    }

    public function restore(int $id)
    {
        try {
            $course = Course::withTrashed()->findOrFail($id);
            $course->restore();

            return redirect()
                ->route('courses.index')
                ->with('success', 'Curso activado exitosamente');

        } catch (Exception $e) {

            return redirect()
                ->route('courses.index')
                ->with('error', 'Hubo un error al activar el curso. Intenta de nuevo más tarde.');
        }
    }

    public function changeStatus ($id) {
        try {
            $course = Course::findOrFail($id);

            $course->is_active = !$course->is_active;
            $course->update();

            return redirect()->route('courses.index');

        } catch (\Exception $e) {
            return redirect()
                ->route('courses.index')
                ->with('error', 'Hubo un error al actualizar el curso. Intenta de nuevo más tarde.');
        }
    }


    // GALERIA COMENTADA
    /* public function gallery($id)
    {
        $course = Course::findOrFail($id);

        return Inertia::render('Courses/CourseGallery', [
            'course' => $course->only('id', 'topic'),
            'images' => $course->getMedia('courses_gallery')->map(function ($media) {
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
            $course = Course::findOrFail($id);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $course->addMedia($image)->toMediaCollection('courses_gallery');
                }
            }

            return redirect()
                ->route('courses.gallery', $id)
                ->with('success', 'Galería actualizada exitosamente');

        } catch (\Exception $e) {

            return redirect()
                ->route('courses.gallery', $id)
                ->with('error', 'Hubo un error al actualizar la galería. Intenta de nuevo más tarde.');
        }
    }

    public function deleteGalleryImage($id, $mediaId)
    {
        try {
            $course = Course::findOrFail($id);
            $mediaItem = $course->getMedia('courses_gallery')->where('id', $mediaId)->first();

            if ($mediaItem) {
                $mediaItem->delete();
                return redirect()
                    ->route('courses.gallery', $id)
                    ->with('success', 'Imagen eliminada exitosamente');
            } else {
                return response()->json(['success' => false, 'message' => 'Imagen no encontrada.'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar la imagen.'], 500);
        }
    } */

    private function deleteCourseMedia(Course $course)
    {
        $course->clearMediaCollection('courses_covers');
        $course->clearMediaCollection('courses_previews');
        $course->clearMediaCollection('courses_gallery');
        $course->clearMediaCollection('courses_platinum_sponsors');
        $course->clearMediaCollection('courses_golden_sponsors');
        $course->clearMediaCollection('courses_silver_sponsors');
        $course->clearMediaCollection('courses_program');
    }

    private function updateCourseMedia(Course $course, Request $request)
    {
        if ($request->hasFile('cover_image')) {
            $course->clearMediaCollection('courses_covers');
            $course->addMediaFromRequest('cover_image')->toMediaCollection('courses_covers');
        }

        if ($request->hasFile('cover_preview_image')) {
            $course->clearMediaCollection('courses_previews');
            $course->addMediaFromRequest('cover_preview_image')->toMediaCollection('courses_previews');
        }

        //Platino
        if ($request->hasFile('platinum_sponsors')) {
            foreach ($request->file('platinum_sponsors') as $logo) {
                $course->addMedia($logo)->toMediaCollection('courses_platinum_sponsors');
            }
        }

        if (!empty($request->input('platinum_delete'))) {
            foreach ($request->get('platinum_delete') as $item) {
                $media = $course->getMedia('courses_platinum_sponsors')->where('id', $item)->first();
                if ($media) $media->delete();
            }
        }

        //Oro
        if ($request->hasFile('golden_sponsors')) {
            foreach ($request->file('golden_sponsors') as $logo) {
                $course->addMedia($logo)->toMediaCollection('courses_golden_sponsors');
            }
        }

        if (!empty($request->input('golden_delete'))) {
            foreach ($request->get('golden_delete') as $item) {
                $media = $course->getMedia('courses_golden_sponsors')->where('id', $item)->first();
                if ($media) $media->delete();
            }
        }


        //Plata
        if ($request->hasFile('silver_sponsors')) {
            foreach ($request->file('silver_sponsors') as $logo) {
                $course->addMedia($logo)->toMediaCollection('courses_silver_sponsors');
            }
        }

        if (!empty($request->input('silver_delete'))) {
            foreach ($request->get('silver_delete') as $item) {
                $media = $course->getMedia('courses_silver_sponsors')->where('id', $item)->first();
                if ($media) $media->delete();
            }
        }

        if ($request->hasFile('program_pdf')) {
            $course->clearMediaCollection('courses_program');
            $course->addMediaFromRequest('program_pdf')->toMediaCollection('courses_program');
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
            'guest_price' => 'required|numeric',
            'resident_price' => 'required|numeric',
            'format' => 'required|string',
            'link' => 'required_if:format,online|nullable|url',
            'address' => 'required_if:format,in_person,hybrid|nullable|string',
            'additional_info' => 'nullable|string',

            'bank_detail_id' => 'required|numeric|exists:bank_details,id',
            //Horarios
            'sessions'         => 'required|array|min:1',
            'sessions.*.date'  => 'required|date',
            'sessions.*.time'  => 'required|date_format:H:i',            
            //Archivos
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'cover_preview_image' => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'program_pdf' => 'nullable|mimes:pdf',
            'platinum_sponsors.*' => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'golden_sponsors.*' => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'silver_sponsors.*' => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'platinum_delete.*' => 'nullable|numeric',
            'golden_delete.*' => 'nullable|numeric',
            'silver_delete.*' => 'nullable|numeric',
        ];
    }

    private function getValidatonMessages()
    {
        return [
            'cover_image.required' => 'La imagen de portada es obligatoria.',
            'cover_image.image' => 'El archivo debe ser una imagen.',
            'program_pdf.mimes' => 'El archivo del programa debe ser un PDF.',
            '*.mimes' => 'La imagen debe ser un archivo de tipo: jpeg, png, jpg, webp.',
            '*.required' => 'Este campo es obligatorio.',
            '*.string' => 'El campo debe ser una cadena de texto.',
            '*.max' => 'El campo no debe exceder los :max caracteres.',
            '*.numeric' => 'El campo debe ser un número.',
            'sessions.*.date' => 'El campo debe ser una fecha válida.',
            'sessions.*.time' => 'Selecciona un horario válido',
            '*.url' => 'El campo debe ser una URL válida.',
            'bank_detail_id.exists' => 'Seleccione una cuenta válida',
            '*.required_if' => 'Este campo es obligatorio.',
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
