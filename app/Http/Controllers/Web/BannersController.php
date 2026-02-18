<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use App\Mail\TestMail;
use App\Models\Banner;
use App\Models\Media;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Controllers\Mail\MailsController;
use function Psy\debug;

class BannersController extends Controller
{

    /**
     * Display the login view.
     */
    /* public function display(): Response
    {
        return Inertia::render('Banner/Banners', []);
    } */



    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {

        /* $mailsController = new MailsController();
        $mailsController->testMail(); */

        $banners = Banner::with('media')
            ->orderBy('order')
            ->get()
            ->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'order' => $banner->order,
                    'link' => $banner->link,
                    'is_active' => $banner->is_active,
                    'name' => $banner->media->first()?->name ?? null,
                    'image' => $banner->getFirstMediaUrl('banners'),
                ];
            });

        return Inertia::render('Banner/Banner', [
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
                'is_active' => true,
                'link' => null
            ]);

            $data = $request->validate([
                'order' => 'required|numeric',
                'link' => 'nullable|string|max:255|url:http,https',
                'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:1024',
                'is_active' => 'boolean'
            ]);

            $banner = Banner::create($data);

            if ($request->hasFile('image')) {
                $banner->addMediaFromRequest('image')
                    ->toMediaCollection('banners');
            }

            return response()->json([
                'message' => 'success',
                'data' => [
                    'banner' => $banner
                ]
            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, int $id)
    {
        try {

            $request->mergeIfMissing([
                'is_active' => true,
                'link' => null
            ]);

            $data = $request->validate([
                'order' => 'required|numeric',
                'link' => 'nullable|string|max:255|url:http,https',
                'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:1024',
                'is_active' => 'boolean'
            ]);

            $banner = Banner::findOrFail($id);

            $banner->update($data);

            if ($request->hasFile('image')) {

                $banner->clearMediaCollection('banners');

                $banner->addMediaFromRequest('image')
                    ->toMediaCollection('banners');
            }

            return response()->json([
                'message' => 'success',
                'data' => [
                    'banner' => $banner->load('media')
                ]
            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
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

            return response()->json([
                'message' => 'success',
                'data' => [
                    'deleted_id' => $id
                ]
            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'banners' => 'required|array',
            'banners.*.id' => 'required|exists:banners,id',
            'banners.*.order' => 'required|numeric'
        ]);

        foreach ($request->banners as $item) {
            Banner::where('id', $item['id'])
                ->update(['order' => $item['order']]);
        }

        return response()->json([
            'message' => 'Orden actualizado'
        ]);
    }
}
