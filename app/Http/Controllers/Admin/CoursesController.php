<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Course;

class CoursesController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', null);

        $courses = Course::orderBy('created_at', 'desc')
            ->when($search, function ($query, $search) {
                $query->where('topic', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            })
            ->paginate($perPage)
            ->withqueryString();

        return Inertia::render('Courses/Index', [
            'courses' => $courses,
        ]);
    }

    public function new()
    {
        return Inertia::render('Courses/CourseCreate');
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

            $course = Course::create($data);

            $this->updateCourseMedia($course, $request);

            return redirect()
                ->route('courses.index')
                ->with('success', 'Curso creado exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {

            return redirect()
            ->back()
            ->with('error', 'Hubo un error al crear el curso. Por favor intenta de nuevo.')
            ->withInput();
        }
    }

    public function edit($id)
    {
        $course = Course::findOrFail($id);
        //todo: load payment_methods

        return Inertia::render('Courses/CourseEdit', [
            'course' => $course
        ]);
    }

    public function update(Request $request) {
        try {            
            $this->mergeNullableFields($request);

            $validationRules = $this->getValidationArray();
            $data = $request->validate($validationRules, $this->getValidatonMessages());

            $data['date'] = $this->formatDateTime($data['date'], $data['time']);
            unset($data['time']);

            $course = Course::findOrFail($request->id);
            $course->update($data);

            $this->updateCourseMedia($course, $request);

            return redirect()
                ->route('courses.index')
                ->with('success', 'Curso actualizado exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {

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
            $this->deleteCourseMedia($course);
            $course->delete();

            return redirect()
                ->route('courses.index')
                ->with('success', 'Curso eliminado exitosamente');

        } catch (\Exception $e) {

            return redirect()
                ->route('courses.index')
                ->with('error', 'Hubo un error al eliminar el curso. Intenta de nuevo más tarde.');
        }
    }

    public function gallery($id)
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
    }

    private function deleteCourseMedia(Course $course)
    {
        $course->clearMediaCollection('courses_covers');
        $course->clearMediaCollection('courses_gallery');
        $course->clearMediaCollection('courses_sponsors_logos');
        $course->clearMediaCollection('courses_program');
    }

    private function updateCourseMedia(Course $course, Request $request)
    {
        if ($request->hasFile('cover_image')) {
            $course->clearMediaCollection('courses_covers');
            $course->addMediaFromRequest('cover_image')->toMediaCollection('courses_covers');
        }

        if ($request->hasFile('sponsor_logos')) {
            $course->clearMediaCollection('courses_sponsors_logos');
            foreach ($request->file('sponsor_logos') as $logo) {
                $course->addMedia($logo)->toMediaCollection('courses_sponsors_logos');
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
            'description' => 'required|string',
            'objectives' => 'nullable|string',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'duration' => 'required|numeric|max:255',
            'organized_by' => 'required|string|max:255',
            'sponsored_by' => 'nullable|string|max:255',
            'member_price' => 'required|numeric',
            'guest_price' => 'nullable|numeric',
            'resident_price' => 'nullable|numeric',
            'link' => 'nullable|url',
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
            '*.url' => 'El campo debe ser una URL válida.',
        ];
    }

    private function mergeNullableFields(Request $request)
    {
        $request->mergeIfMissing([
            'description' => 'No disponible',
            'objectives' => null,
            'sponsored_by' => null,
            'guest_price' => null,
            'resident_price' => null,
            'link' => null
        ]);
    }

    private function formatDateTime($date, $time)
    {
        return date('Y-m-d H:i:s', strtotime("$date $time"));
    }

    
}
