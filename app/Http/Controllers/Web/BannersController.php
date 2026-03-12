<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\TestMail;
use App\Models\Banner;
use App\Models\Media;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Controllers\Mail\MailsController;
use App\Http\Helpers\Constants;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\EventSession;
use App\Models\Webinar;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;

class BannersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        //Filtros
        $eventType = $request->get('event_type', 'home');
        $status    = $request->get('status', '');
        $search    = $request->get('search', '');
        $dateFrom  = $request->get('date_from', '');
        $dateTo    = $request->get('date_to', '');

        $query = Banner::with('media')
            ->where('event_type', $eventType)
            ->orderBy('order');

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        // filtro de fecha (solo para tipos con event_id)
        if ($eventType !== 'home' && ($dateFrom || $dateTo)) {
            $query->whereIn('event_id', function ($sub) use ($eventType, $dateFrom, $dateTo) {
                $sub->select('sessionable_id')
                    ->from('event_sessions')
                    ->where('sessionable_type', $eventType); // directo gracias al morphMap

                if ($dateFrom) $sub->where('date', '>=', $dateFrom);
                if ($dateTo)   $sub->where('date', '<=', $dateTo);
            });
        }

        // solo una consulta para traer las sesiones de todos los banners
        $eventIds = $query->get()->pluck('event_id')->filter()->unique()->values();

        $sessionsByEventId = EventSession::whereIn('sessionable_id', $eventIds)
            ->where('sessionable_type', $eventType)
            ->orderBy('date')
            ->get()
            ->groupBy('sessionable_id');

        $banners = $query->get()->map(function ($banner) use ($sessionsByEventId) {
            $dates = [];
            if ($banner->event_id) {
                $dates = ($sessionsByEventId[$banner->event_id] ?? collect())
                    ->map(fn($s) => Carbon::parse($s->date)->format('d/m/Y'))
                    ->toArray();
            }

            return [
                'id'         => $banner->id,
                'title'      => $banner->title,
                'order'      => $banner->order,
                'link'       => $banner->link,
                'is_active'  => $banner->is_active,
                'event_id'   => $banner->event_id,
                'event_type' => $banner->event_type,
                'name'       => $banner->media->first()?->name ?? null,
                'image'      => $banner->getFirstMediaUrl('banners'),
                'dates'      => $dates,
            ];
        });

        // draggin de los diferentes tipos de eventos en la db, home default
        $eventTypes = collect([
            'home',
            Constants::EVENT_WEBINAR,
            Constants::EVENT_ACADEMIC_SESSION,
            Constants::EVENT_COURSE,
            Constants::EVENT_CONFERENCE,
            Constants::EVENT_PRECONFERENCE,
        ])->values();

        return Inertia::render('Banners/Index', [
            'banners'    => $banners,
            'eventTypes' => $eventTypes,
            'filters'    => [
                'event_type' => $eventType,
                'status'     => $status,
                'search'     => $search,
                'date_from'  => $dateFrom,
                'date_to'    => $dateTo,
            ],
        ]);
    }

    /* public function index(Request $request)
    {
        try {

            $query = Banner::with('media');

            $banners = $query->get();

            return response()->json([
                'message' => 'success',
                'data' => $banners
            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    } */

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->mergeIfMissing([
                'is_active'  => true,
                'link'       => null,
                'event_type' => 'home',
            ]);

            $data = $request->validate([
                'title'      => 'required|string|max:255',
                'order'      => 'required|numeric',
                'link'       => 'nullable|string|max:255|url:http,https',
                'image'      => 'required|image|mimes:jpeg,png,jpg,webp|max:1024',
                'is_active'  => 'boolean',
                'event_id'   => 'nullable|numeric',
                'event_type' => 'required|string|max:255',
            ]);

            if (empty($data['event_id'])) {
                $data['event_id'] = null;
                if (empty($data['event_type'])) {
                    $data['event_type'] = 'home';
                }
            }

            $banner = Banner::create($data);

            if ($request->hasFile('image')) {
                $banner->addMediaFromRequest('image')
                    ->toMediaCollection('banners');
            }

            return redirect()->route('banners.index', [
                'event_type' => $data['event_type']
            ]);
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['message' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource.
     */
    public function edit(Request $request, int $id)
    {
        try {
            $request->mergeIfMissing([
                'is_active'  => true,
                'link'       => null,
                'event_type' => 'home',
            ]);

            $data = $request->validate([
                'title'      => 'required|string|max:255',
                'order'      => 'required|numeric',
                'link'       => 'nullable|string|max:255|url:http,https',
                'image'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
                'is_active'  => 'boolean',
                'event_id'   => 'nullable|numeric',
                'event_type' => 'required|string|max:255',
            ]);

            if (empty($data['event_id'])) {
                $data['event_id'] = null;
                if (empty($data['event_type'])) {
                    $data['event_type'] = 'home';
                }
            }

            $banner = Banner::findOrFail($id);
            $banner->update($data);

            if ($request->hasFile('image')) {
                $banner->clearMediaCollection('banners');
                $banner->addMediaFromRequest('image')
                    ->toMediaCollection('banners');
            }

            return redirect()->route('banners.index', [
                'event_type' => $request->get('event_type', 'home')
            ]);
            /* return response()->json([
                'message' => 'success',
                'data' => [
                    'banner' => $banner->load('media')
                ]
            ], 200); */
        } catch (Exception $e) {

            /* return response()->json([
                'message' => $e->getMessage(),
            ], 500); */
            return redirect()->back()->withErrors([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request, int $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $banner->clearMediaCollection('banners');
            $banner->delete();

            return redirect()->route('banners.index', [
                'event_type' => $request->get('event_type', 'home')
            ]);
        } catch (Exception $e) {
            return redirect()->back()->withErrors([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Reorder banners.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'banners'         => 'required|array',
            'banners.*.id'    => 'required|exists:banners,id',
            'banners.*.order' => 'required|numeric',
            'event_type'      => 'nullable|string',
        ]);

        foreach ($request->banners as $item) {
            Banner::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return redirect()->route('banners.index', [
            'event_type' => $request->get('event_type', 'home')
        ]);
    }

    public function statusChange(Request $request, int $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $banner->is_active = !$banner->is_active;
            $banner->save();
            return redirect()->route('banners.index', [
                'event_type' => $request->get('event_type', 'home')
            ]);
        } catch (Exception $e) {
            return redirect()->route('banners.index')->with('error', $e->getMessage());
        }
    }


    public static function createFromEvent(
        string $title,
        ?UploadedFile $image = null,
        ?string $imagePath = null,
        ?string $link = null,
        ?int $eventId = null,
        ?string $eventType = null
    ): Banner {
        $resolvedType = $eventId ? $eventType : 'home';

        // orden propio por event_type, no global
        $maxOrder = Banner::where('event_type', $resolvedType)->max('order') ?? -1;

        $banner = Banner::create([
            'title'      => $title,
            'link'       => $link,
            'order'      => $maxOrder + 1,
            'is_active'  => true,
            'event_id'   => $eventId,
            'event_type' => $resolvedType,
        ]);

        if ($image !== null) {
            $banner->addMedia($image)->toMediaCollection('banners');
        } elseif ($imagePath && file_exists($imagePath)) {
            $banner->addMedia($imagePath)
                ->preservingOriginal()
                ->toMediaCollection('banners');
        }

        return $banner;
    }

    public static function updateFromEvent(
        string $title,
        ?UploadedFile $image = null,
        ?string $imagePath = null,   // path fisico, no URL
        ?string $link = null,
        int $eventId,
        string $eventType
    ): ?Banner {
        $banner = Banner::where('event_id', $eventId)
            ->where('event_type', $eventType)
            ->first();

        if (!$banner) {
            if ($image === null && (!$imagePath || !file_exists($imagePath))) {
                return null;
            }

            return static::createFromEvent($title, $image, $imagePath, $link, $eventId, $eventType);
        }

        $banner->update([
            'title' => $title,
            'link'  => $link,
        ]);

        if ($image !== null) {
            $banner->clearMediaCollection('banners');
            $banner->addMedia($image)->toMediaCollection('banners');
        } elseif ($imagePath && file_exists($imagePath) && !$banner->hasMedia('banners')) {
            $banner->addMedia($imagePath)
                ->preservingOriginal()
                ->toMediaCollection('banners');
        }

        return $banner;
    }

    public static function deleteFromEvent(int $eventId, string $eventType): void
    {
        $banner = Banner::where('event_id', $eventId)
            ->where('event_type', $eventType)
            ->first();

        if ($banner) {
            $banner->clearMediaCollection('banners');
            $banner->delete();
        }
    }
}
