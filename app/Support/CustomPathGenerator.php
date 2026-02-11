<?php

namespace App\Support;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class CustomPathGenerator implements PathGenerator
{
    /* Path para el archivo original */
    public function getPath(Media $media): string
    {
        // Ejemplo: 'usuarios/15/1/'
        return "{$media->collection_name}/{$media->model_id}/{$media->id}/";
    }

    /* Path para las conversiones (thumbnails, etc) */
    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media) . 'conversions/';
    }

    /* Path para imágenes responsivas */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'responsive/';
    }
}