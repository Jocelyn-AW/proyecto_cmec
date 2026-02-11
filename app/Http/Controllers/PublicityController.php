<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use App\Models\Post;
use Exception;
use Illuminate\Database\Eloquent\Casts\Json as CastsJson;
use Inertia\Inertia;
use Nette\Utils\Json;
use Symfony\Component\HttpFoundation\JsonResponse;

class PublicityController extends Controller
{
    public function index() {
        $posts = Post::with('media')->get();

        return response()->json([
            'message' => 'success',
            'data' => $posts
        ], 200);

        // return Inertia::render('Publicity/Index', [
        //     'posts' => $posts
        // ]);

    }

    public function store(Request $request) {
        try {
            //si faltan datos en el request los puedes asignar
            $request->mergeIfMissing([
                'is_active' => true,
                'link' => null
            ]);

            $data = $request->validate([
                'order' => 'required|numeric',
                'link' => 'nullable|string',
                'image' => 'required|image|mimes:jpeg,png,jpg,webp',
                'is_active' => 'boolean'
            ]);
            
            //Nota: si todos los datos estan validados, puedes asignarlo asi
            //en vez de uno por uno
            $post = Post::create($data);            

            // $post = Post::create([
            //     'order' => $data['order'],
            //     'link' => $data['link'],
            //     'is_active' => true
            // ]);

            if ($request->hasFile('image')) {
                $post->addMediaFromRequest('image')->toMediaCollection('posts');
            }

            //test
            return response()->json([
                'message' => 'success',
                'data' => [
                    'post' => $post
                ]
            ], 200);

            // return Inertia::render('Publicity/Index', [
            //     'posts' => $posts
            // ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function edit(Request $request, int $id) {
        $data = $request->validate([
            'order' => 'required|numeric',
            'link' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'is_active' => 'boolean'
        ]);

        $post = Post::findOrFail($id);

        $post->update($data);

        if ($request->hasFile('image')) {
            $post->addMediaFromRequest('image')->toMediaCollection('posts');
        }


        //test
        return response()->json([
            'message' => 'success',
            'data' => [
                'url' => $post->getMedia("*")
            ]
        ], 200);

        // return Inertia::render('Publicity/Index', [
        //     'posts' => $posts
        // ]);
    }

    // public function delete() {

    // }
}
