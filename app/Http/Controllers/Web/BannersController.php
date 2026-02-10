<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

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
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'order' => 'required|integer',
            'image' => 'required|image|max:5120',
            'link'  => 'nullable|url|max:255',
        ]);

        $collectionName = 'banner';

        $banner = Banner::create([
            'link'      => $request->link,
            'order'     => $request->order,
            'is_active' => true,
        ]);

        $media = $banner
            ->addMediaFromRequest('image')
            ->toMediaCollection($collectionName);

        return response()->json([
            'success' => true,
            'banner' => [
                'id'        => $banner->id,
                'order'     => $banner->order,
                'image'     => [
                    'id'       => $media->id,
                    'url'      => $media->getUrl(),
                    'filename' => $media->file_name,
                    'collection_name' => $media->collection_name,
                ],
            ],
        ], 201);
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
