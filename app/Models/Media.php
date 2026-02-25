<?php

namespace App\Models;

use App\Http\Helpers\Constants;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;
use Illuminate\Support\Facades\Storage;

class Media extends BaseMedia
{
    protected $table = Constants::TABLE_MEDIA;

    protected $casts = [
        'manipulations'        => 'array',
        'custom_properties'    => 'array',
        'generated_conversions' => 'array',
        'responsive_images'    => 'array',
    ];

    protected static function booted()
    {
        static::deleting(function ($media) {

            $disk = $media->disk;

            //  banners/{model_id}/{media_id}/
            $mediaFolder = "{$media->collection_name}/{$media->model_id}/{$media->id}";
            $modelFolder = "{$media->collection_name}/{$media->model_id}";

            // eliminar carpeta del media_id
            Storage::disk($disk)->deleteDirectory($mediaFolder);

            // eliminar carpeta del model_id si quedo sola
            if (
                Storage::disk($disk)->exists($modelFolder) &&
                empty(Storage::disk($disk)->files($modelFolder)) &&
                empty(Storage::disk($disk)->directories($modelFolder))
            ) {
                Storage::disk($disk)->deleteDirectory($modelFolder);
            }
        });
    }
}
