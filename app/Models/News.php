<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class News extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'news';

    protected $fillable = [
        'title',
        'content',
        'link',
        'extract',
        'type',
        'is_active',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Register the media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        // imagenes
        $this->addMediaCollection('news_images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public')
            ->singleFile()
            ->withResponsiveImages();

        // PDF'S
        $this->addMediaCollection('news_pdfs')
            ->acceptsMimeTypes([
                'application/pdf',
                'application/octet-stream',
                'application/x-pdf',
            ])
            ->useDisk('public')
            ->singleFile();
    }
}
