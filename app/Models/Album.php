<?php

namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Http\Helpers\Constants;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Album extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = Constants::TABLE_ALBUMS;

    protected $fillable = [
        'title',
        'description',
        'event_id',
        'event_type',
    ];

    protected $appends = [
        'cover_url',
        'photos_count',
    ];

    // relacion polimorfica con evento
    // cualquier tipo registrado en el morphMap del AppServiceProvider
    public function event(): MorphTo
    {
        return $this->morphTo();
    }

    // media collection 
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('album_photos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public')
            ->withResponsiveImages();
    }

    // accessors
    protected function coverUrl(): Attribute
    {
        return Attribute::make(function () {
            return $this->getFirstMediaUrl('album_photos');
        });
    }

    protected function photosCount(): Attribute
    {
        return Attribute::make(function () {
            return $this->getMedia('album_photos')->count();
        });
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(400)
            ->sharpen(10)
            //->nonQueued()
        ;
    }
}
