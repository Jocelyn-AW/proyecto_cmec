<?php

namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    protected $table = 'media';

    protected $casts = [
        'manipulations'        => 'array',
        'custom_properties'    => 'array',
        'generated_conversions'=> 'array',
        'responsive_images'    => 'array',
    ];
}
