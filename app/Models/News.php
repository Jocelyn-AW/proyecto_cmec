<?php

namespace App\Models;

use App\Http\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class News extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = Constants::TABLE_NEWS;

    protected $fillable = [
        'title',
        'content',
        'link',
        'extract',
        'type',
        'is_active',
        'reading_time',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'reading_time' => 'integer',
        'views_number' => 'integer',
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
            ->singleFile();

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
