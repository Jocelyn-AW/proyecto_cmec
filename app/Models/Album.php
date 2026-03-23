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
    use InteractsWithMedia;

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

    // ---------------------------------------------
    // nombre de coleccion dinamica por tipo de evento
    // ej: webinar_album_photos, academic_session_album_photos
    // ---------------------------------------------

    public function collectionName(): string
    {
        return ($this->event_type ?? 'default') . '_album_photos';
    }

    // ---------------------------------------------
    // relacion polimorfica con evento
    // cualquier tipo registrado en el morphMap del AppServiceProvider
    public function event(): MorphTo
    {
        return $this->morphTo();
    }

    // ---------------------------------------------
    // media collection (usa el nombre dinamico)
    // ---------------------------------------------

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection($this->collectionName())
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public');
    }

    // ---------------------------------------------
    // Accessors (usan el nombre dinamico)
    // ---------------------------------------------

    protected function coverUrl(): Attribute
    {
        return Attribute::make(function () {
            return $this->getFirstMediaUrl($this->collectionName());
        });
    }

    protected function photosCount(): Attribute
    {
        return Attribute::make(function () {
            return $this->getMedia($this->collectionName())->count();
        });
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(400)
            ->sharpen(10);
    }
}