<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use Exception;
use Inertia\Inertia;
use Inertia\Response;

use function Psy\debug;

class PublicityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $posts = Post::with('media')
            ->orderBy('order')
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'order' => $post->order,
                    'link' => $post->link,
                    'is_active' => $post->is_active,
                    'name' => $post->media->first()?->name ?? null,
                    'image' => $post->getFirstMediaUrl('posts'),
                ];
            });

        return Inertia::render('Publicity/Publicity', [
            'posts' => $posts
        ]);
    }

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

            $post = Post::create($data);

            if ($request->hasFile('image')) {
                $post->addMediaFromRequest('image')
                    ->toMediaCollection('posts');
            }

            return response()->json([
                'message' => 'success',
                'data' => [
                    'post' => $post
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
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
                'is_active' => 'boolean'
            ]);

            $post = Post::findOrFail($id);

            $post->update($data);

            if ($request->hasFile('image')) {

                $post->clearMediaCollection('posts');

                $post->addMediaFromRequest('image')
                    ->toMediaCollection('posts');
            }

            return response()->json([
                'message' => 'success',
                'data' => [
                    'post' => $post->load('media')
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
            $post = Post::findOrFail($id);

            $post->clearMediaCollection('posts');

            $post->delete();

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

    /**
     * Reorder posts
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'posts' => 'required|array',
            'posts.*.id' => 'required|exists:posts,id',
            'posts.*.order' => 'required|numeric'
        ]);

        foreach ($request->posts as $item) {
            Post::where('id', $item['id'])
                ->update(['order' => $item['order']]);
        }

        return response()->json([
            'message' => 'Orden actualizado'
        ]);
    }
}