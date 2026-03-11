<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Constants;
use App\Models\Album;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AlbumsController extends Controller
{
    /**
     * agregar cualquier nuevo tipo de evento.
     */
    private array $eventLabels = [
        Constants::EVENT_WEBINAR          => 'Webinar',
        Constants::EVENT_COURSE           => 'Curso',
        Constants::EVENT_ACADEMIC_SESSION => 'Sesión académica',
        Constants::EVENT_CONFERENCE       => 'Congreso',
        Constants::EVENT_PRECONFERENCE    => 'Pre-congreso',
    ];

    /**
     *  boton "Volver" desde la galeria
     */
    private array $eventRoutes = [
        Constants::EVENT_WEBINAR          => 'webinars.index',
        Constants::EVENT_COURSE           => 'courses.index',
        Constants::EVENT_ACADEMIC_SESSION => 'academic-sessions.index',
        Constants::EVENT_CONFERENCE       => 'conferences.index',
        Constants::EVENT_PRECONFERENCE    => 'conferences.index',
    ];


    /**
     *  albumes de todo tipo de evento
     *  uso en WebinarsController:
     *  return redirect()->route('albums.index', ['event_type' => 'webinar', 'event_id' => $webinar->id]);
     */
    public function index(Request $request, string $event_type, int $event_id)
    {
        $albums = Album::where('event_type', $event_type)
            ->where('event_id', $event_id)
            ->withCount('media')          // mostrar cantidad
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($album) => [
                'id'          => $album->id,
                'title'       => $album->title,
                'description' => $album->description,
                'cover_url'   => $album->cover_url,
                'photos_count' => $album->media_count,
            ]);

        return Inertia::render('Albums/Index', [
            'albums'     => $albums,
            'event_type' => $event_type,
            'event_id'   => $event_id,
            'event_label' => $this->eventLabels[$event_type] ?? 'Evento',
            'back_route' => $this->eventRoutes[$event_type] ?? null,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title'       => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'event_type'  => 'required|string',
                'event_id'    => 'required|integer',
            ]);

            Album::create($data);

            return redirect()->route('albums.index', [
                'event_type' => $data['event_type'],
                'event_id'   => $data['event_id'],
            ])->with('success', 'Álbum creado exitosamente');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al crear el álbum.')->withInput();
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $album = Album::findOrFail($id);

            $data = $request->validate([
                'title'       => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);

            $album->update($data);

            return redirect()->route('albums.index', [
                'event_type' => $album->event_type,
                'event_id'   => $album->event_id,
            ])->with('success', 'Álbum actualizado exitosamente');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al actualizar el álbum.');
        }
    }

    public function delete(int $id)
    {
        try {
            $album = Album::findOrFail($id);

            $event_type = $album->event_type;
            $event_id   = $album->event_id;

            $album->clearMediaCollection('album_photos');
            $album->delete();

            return redirect()->route('albums.index', [
                'event_type' => $event_type,
                'event_id'   => $event_id,
            ])->with('success', 'Álbum eliminado exitosamente');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar el álbum.');
        }
    }

    public function photos(int $id)
    {
        $album = Album::findOrFail($id);

        $photos = $album->getMedia('album_photos')->map(fn($media) => [
            'id'    => $media->id,
            'url'   => $media->getUrl(),
            'thumb' => $media->getUrl('thumb'),
        ]);

        return Inertia::render('Albums/Photos', [
            'album'      => [
                'id'          => $album->id,
                'title'       => $album->title,
                'description' => $album->description,
                'event_type'  => $album->event_type,
                'event_id'    => $album->event_id,
            ],
            'photos'     => $photos,
            'event_label' => $this->eventLabels[$album->event_type] ?? 'Evento',
        ]);
    }

    public function uploadPhotos(Request $request, int $id)
    {
        try {
            $album = Album::findOrFail($id);

            $request->validate([
                'images'   => 'required|array|min:1',
                'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            foreach ($request->file('images') as $image) {
                $album->addMedia($image)->toMediaCollection('album_photos');
            }

            return redirect()->route('albums.photos', $id)
                ->with('success', 'Fotos subidas exitosamente');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al subir las fotos.');
        }
    }

    public function deletePhoto(int $id, int $mediaId)
    {
        try {
            $album = Album::findOrFail($id);
            $media = $album->getMedia('album_photos')->firstWhere('id', $mediaId);

            if ($media) {
                $media->delete();
                return redirect()->route('albums.photos', $id)
                    ->with('success', 'Foto eliminada exitosamente');
            }

            return redirect()->back()->with('error', 'Foto no encontrada.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar la foto.');
        }
    }
}
