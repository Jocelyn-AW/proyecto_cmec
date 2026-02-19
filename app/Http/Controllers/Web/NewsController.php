<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use Exception;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $news = News::with('media')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($news) {
                    return [
                        'id'         => $news->id,
                        'title'      => $news->title,
                        'content'    => $news->content,
                        'extract'    => $news->extract,
                        'link'       => $news->link,
                        'type'       => $news->type,
                        'is_active'  => $news->is_active,
                        'image'      => $news->getFirstMediaUrl('news_images'),
                        'pdf'        => $news->getFirstMediaUrl('news_pdfs'),
                        'created_at' => $news->created_at->format('d/m/Y'),
                    ];
                });

            return response()->json([
                'message' => 'success',
                'data' => $news
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->mergeIfMissing([
                'is_active' => true,
                'link'      => null,
                'extract'   => null,
            ]);

            $data = $request->validate([
                'title'    => 'required|string|max:255',
                'content'  => 'required|string|max:500',
                'extract'  => 'nullable|string|max:500',
                'link'     => 'nullable|string|max:255|url:http,https',
                'type'     => 'required|in:sesion,noticia',
                'is_active' => 'boolean',
                'image'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'pdf'      => 'nullable|mimes:pdf|max:10240', // max 10MB
            ]);

            $news = News::create($data);

            if ($request->hasFile('image')) {
                $news->addMediaFromRequest('image')
                    ->toMediaCollection('news_images');
            }

            if ($request->hasFile('pdf')) {
                $news->addMediaFromRequest('pdf')
                    ->toMediaCollection('news_pdfs');
            }

            return response()->json([
                'message' => 'success',
                'data'    => [
                    'news' => $news->load('media')
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
    public function show(int $id)
    {
        try {
            $news = News::with('media')->findOrFail($id);

            return response()->json([
                'message' => 'success',
                'data'    => [
                    'id'         => $news->id,
                    'title'      => $news->title,
                    'content'    => $news->content,
                    'extract'    => $news->extract,
                    'link'       => $news->link,
                    'type'       => $news->type,
                    'is_active'  => $news->is_active,
                    'image'      => $news->getFirstMediaUrl('news_images'),
                    'pdf'        => $news->getFirstMediaUrl('news_pdfs'),
                    'created_at' => $news->created_at->format('d/m/Y'),
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function edit(Request $request, int $id)
    {
        try {
            $request->mergeIfMissing([
                'is_active' => true,
                'link'      => null,
                'extract'   => null,
            ]);

            $data = $request->validate([
                'title'    => 'required|string|max:255',
                'content'  => 'required|string',
                'extract'  => 'nullable|string|max:500',
                'link'     => 'nullable|string|max:255|url:http,https',
                'type'     => 'required|in:sesion,noticia',
                'is_active' => 'boolean',
                'image'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'pdf'      => 'nullable|mimes:pdf|max:10240',
            ]);

            $news = News::findOrFail($id);
            $news->update($data);

            if ($request->hasFile('image')) {
                $news->clearMediaCollection('news_images');
                $news->addMediaFromRequest('image')
                    ->toMediaCollection('news_images');
            }

            if ($request->hasFile('pdf')) {
                $news->clearMediaCollection('news_pdfs');
                $news->addMediaFromRequest('pdf')
                    ->toMediaCollection('news_pdfs');
            }

            return response()->json([
                'message' => 'success',
                'data'    => [
                    'news' => $news->load('media')
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
            $news = News::findOrFail($id);

            $news->clearMediaCollection('news_images');
            $news->clearMediaCollection('news_pdfs');
            $news->delete();

            return response()->json([
                'message' => 'success',
                'data'    => [
                    'deleted_id' => $id
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle active status.
     */
    public function statusChange(int $id)
    {
        try {
            $news = News::findOrFail($id);
            $news->is_active = !$news->is_active;
            $news->save();

            return response()->json([
                'message' => 'success',
                'data'    => [
                    'news' => $news
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
