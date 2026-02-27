<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use Exception;
use Inertia\Inertia;
use Inertia\Response;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = News::with('media')->orderBy('created_at', 'desc');

        /* FILTROS */
        // tipo
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // estado
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // texto (titulo y link)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('link', 'like', "%{$search}%");
            });
        }

        // imagen
        if ($request->filled('has_image')) {
            $query->whereHas('media', function ($q) {
                $q->where('collection_name', 'news_images');
            }, $request->has_image === 'yes' ? '>=' : '<', 1);
        }

        // PDF
        if ($request->filled('has_pdf')) {
            $query->whereHas('media', function ($q) {
                $q->where('collection_name', 'news_pdfs');
            }, $request->has_pdf === 'yes' ? '>=' : '<', 1);
        }

        // url
        if ($request->filled('has_link')) {
            if ($request->has_link === 'yes') {
                $query->whereNotNull('link')->where('link', '!=', '');
            } else {
                $query->where(function ($q) {
                    $q->whereNull('link')->orWhere('link', '');
                });
            }
        }

        $paginator = $query->paginate(9)->withQueryString();

        $news = $paginator->getCollection()->map(function ($item) {
            return [
                'id'         => $item->id,
                'title'      => $item->title,
                'content'    => $item->content,
                'extract'    => $item->extract,
                'link'       => $item->link,
                'type'       => $item->type,
                'is_active'  => $item->is_active,
                'image'      => $item->getFirstMediaUrl('news_images') ?: null,
                'pdf'        => $item->getFirstMediaUrl('news_pdfs') ?: null,
                'updated_at' => $item->updated_at->format('d/m/Y'),
            ];
        });

        return Inertia::render('News/Index', [
            'news' => $news,
            'pagination' => [
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem() ?? 0,
                'to'           => $paginator->lastItem() ?? 0,
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
            ],
            'filters' => $request->only(['search', 'type', 'status', 'has_image', 'has_pdf', 'has_link'])
        ]);
    }


    /* public function index()
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
    } */

    // NewsController
    public function create(): Response
    {
        return Inertia::render('News/NewsCreate');
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->mergeIfMissing([
            'is_active' => true,
            'link'      => null,
            'extract'   => null,
        ]);

        $messages = [
            'title.required'    => 'El campo de título es obligatorio para el registro.',
            'title.max'         => 'El título excede la longitud permitida (máximo 255 caracteres).',
            'content.required'  => 'Debe ingresar la descripción o el cuerpo del contenido.',
            'content.max'       => 'El contenido ha superado el límite de caracteres permitido.',
            'extract.max'       => 'El resumen es demasiado extenso; por favor redúzcalo.',
            'link.url'          => 'El formato del enlace es inválido. Debe incluir http:// o https://',
            'type.required'     => 'Debe seleccionar una categoría: Sesión o Noticia.',
            'type.in'           => 'La opción seleccionada no es válida dentro de las categorías permitidas.',
            'is_active.boolean' => 'El estado de activación solo admite valores lógicos (verdadero/falso).',
            'image.image'       => 'El archivo seleccionado no es una imagen válida.',
            'image.mimes'       => 'Solo se permiten formatos de imagen institucionales: JPG, PNG o WebP.',
            'image.max'         => 'La imagen excede el límite de tamaño permitido (1MB).',
            'pdf.mimes'         => 'El archivo adjunto debe ser estrictamente en formato PDF.',
            'pdf.max'           => 'El documento PDF excede el peso máximo permitido de 10MB.',
        ];

        $data = $request->validate([
            'title'     => 'required|string|max:255',
            'content'   => 'required|string|max:2000',
            'extract'   => 'nullable|string|max:500',
            'link'      => 'nullable|string|max:255|url:http,https',
            'type'      => 'required|in:sesion,noticia',
            'is_active' => 'boolean',
            'image'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
            'pdf'       => 'nullable|mimes:pdf|max:10240',
        ], $messages);

        $news = News::create($data);

        if ($request->hasFile('image')) {
            $news->addMediaFromRequest('image')
                ->toMediaCollection('news_images');
        }

        if ($request->hasFile('pdf')) {
            $news->addMediaFromRequest('pdf')
                ->toMediaCollection('news_pdfs');
        }

        /* return response()->json([
                'message' => 'success',
                'data'    => [
                    'news' => $news->load('media')
                ]
            ], 200); */
        return to_route('news.index')->with('success', 'Noticia creada correctamente');
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
                'content'  => 'required|string|max:2000',
                'extract'  => 'nullable|string|max:500',
                'link'     => 'nullable|string|max:255|url:http,https',
                'type'     => 'required|in:sesion,noticia',
                'is_active' => 'boolean',
                'image'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
                'pdf'      => 'nullable|mimes:pdf|max:10240',
            ]);

            $news = News::findOrFail($id);
            $news->update($data);

            // imagen
            if ($request->hasFile('image')) {
                $news->clearMediaCollection('news_images');
                $news->addMediaFromRequest('image')
                    ->toMediaCollection('news_images');
            } elseif ($request->input('remove_image') == '1') {
                $news->clearMediaCollection('news_images');
            }

            // PDF
            if ($request->hasFile('pdf')) {
                $news->clearMediaCollection('news_pdfs');
                $news->addMediaFromRequest('pdf')
                    ->toMediaCollection('news_pdfs');
            } elseif ($request->input('remove_pdf') == '1') {
                $news->clearMediaCollection('news_pdfs');
            }

            /* return response()->json([
                'message' => 'success',
                'data'    => [
                    'news' => $news->load('media')
                ]
            ], 200); */
            return redirect()->route('news.index');
        } catch (Exception $e) {
            /* return response()->json([
                'message' => $e->getMessage(),
            ], 500); */
            return redirect()->route('news.index')->with('error', $e->getMessage());
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

            /* return response()->json([
                'message' => 'success',
                'data'    => [
                    'deleted_id' => $id
                ]
            ], 200); */
            return redirect()->route('news.index')->with('success', 'Noticia eliminada correctamente');
        } catch (Exception $e) {
            /* return response()->json([
                'message' => $e->getMessage(),
            ], 500); */
            return redirect()->route('news.index')->with('error', $e->getMessage());
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

            /* return response()->json([
                'message' => 'success',
                'data'    => [
                    'news' => $news
                ]
            ], 200); */
            return redirect()->route('news.index');
        } catch (Exception $e) {
            /* return response()->json([
                'message' => $e->getMessage(),
            ], 500); */
            return redirect()->route('news.index')->with('error', $e->getMessage());
        }
    }
}
