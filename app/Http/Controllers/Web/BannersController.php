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
use Illuminate\Http\UploadedFile;

class BannersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {

        /* $mailsController = new MailsController();
        $mailsController->testMail(); */

        $banners = Banner::with('media')
            ->orderBy('order')
            ->get()
            ->map(function ($banner) {
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
                ];
            });

        // return Inertia::render('Banner/Banner', [
        //     'banners' => $banners
        // ]);
        return Inertia::render('Banners/Index', [
            'banners' => $banners
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

            // si viene event_id, event_type es obligatorio (ya lo garantiza el or defecto 'home')
            // si NO viene event_id, se limpia event_type para que quede 'home'
            if (empty($data['event_id'])) {
                $data['event_type'] = 'home';
                $data['event_id']   = null;
            }

            $banner = Banner::create($data);

            if ($request->hasFile('image')) {
                $banner->addMediaFromRequest('image')
                    ->toMediaCollection('banners');
            }

            return redirect()->route('banners.index');
            // return response()->json([
            //     'message' => 'success',
            //     'data' => [
            //         'banner' => $banner
            //     ]
            // ], 200);
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
                $data['event_type'] = 'home';
                $data['event_id']   = null;
            }

            $banner = Banner::findOrFail($id);
            $banner->update($data);

            if ($request->hasFile('image')) {
                $banner->clearMediaCollection('banners');
                $banner->addMediaFromRequest('image')
                    ->toMediaCollection('banners');
            }

            return redirect()->route('banners.index');
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
    public function delete(int $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $banner->clearMediaCollection('banners');
            $banner->delete();

            return redirect()->route('banners.index');
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
     * Reorder banners.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'banners'          => 'required|array',
            'banners.*.id'     => 'required|exists:banners,id',
            'banners.*.order'  => 'required|numeric'
        ]);

        foreach ($request->banners as $item) {
            Banner::where('id', $item['id'])
                ->update(['order' => $item['order']]);
        }

        return redirect()->route('banners.index');
    }

    public function statusChange(int $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $banner->is_active = !$banner->is_active;
            $banner->save();
            return redirect()->route('banners.index');
        } catch (Exception $e) {
            return redirect()->route('banners.index')->with('error', $e->getMessage());
        }
    }


    public static function createFromEvent(
        string $title,
        UploadedFile $image,
        ?string $link = null,
        ?int $eventId = null,
        ?string $eventType = null
    ): Banner {
        $banner = Banner::create([
            'title'      => $title,
            'link'       => $link,
            'order'      => (Banner::max('order') ?? 0) + 1,
            'is_active'  => true,
            'event_id'   => $eventId,
            'event_type' => $eventId ? $eventType : 'home',
        ]);

        $banner->addMedia($image)
            ->toMediaCollection('banners');

        return $banner;
    }

    public static function updateFromEvent(
        string $title,
        ?UploadedFile $image = null,
        ?string $link = null,
        int $eventId,
        string $eventType
    ): Banner {
        $banner = Banner::where('event_id', $eventId)
            ->where('event_type', $eventType)
            ->first();

        if (!$banner) {
            return static::createFromEvent($title, $image, $link, $eventId, $eventType);
        }

        $banner->update([
            'title' => $title,
            'link'  => $link,
        ]);

        if ($image !== null) {
            $banner->clearMediaCollection('banners');
            $banner->addMedia($image)
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
