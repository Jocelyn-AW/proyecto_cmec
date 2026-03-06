<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Helpers\Constants;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Conference extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = Constants::TABLE_CONFERENCES;

    protected $fillable = [
        'name',
        'main_topic',
        'description',
        'format',
        'held_by',
        'address',
        'google_coords',
        'link',
        'organized_by',
        'member_price',
        'resident_price',
        'guest_price',
        'nurse_price',
        'surgeon_price',
        'is_active',
        'bank_detail_id',
        'additional_comments',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'cover_url',
        'gallery_urls',
        'sponsors_logos_urls',
        'program_url',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'bank_detail_id' => 'integer',
        'resident_price' => 'decimal:2',
        'guest_price' => 'decimal:2',
        'member_price' => 'decimal:2',
        'nurse_price' => 'decimal:2',
        'surgeon_price' => 'decimal:2',
    ];

    //Morph Relations
    public function speakers()
    {
        return $this->morphMany(EventSpeaker::class, 'event');
    }
    
    public function sessions()
    {
        return $this->morphMany(EventSession::class, 'sessionable');
    }

    //Media
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('conference_covers')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public')
            ->singleFile()
            ->withResponsiveImages();

        $this->addMediaCollection('conference_gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public')
            ->withResponsiveImages();

        $this->addMediaCollection('conference_sponsor_logos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public')
            ->withResponsiveImages();

        $this->addMediaCollection('conference_program')
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('public');
    }

    //Atributos
    protected function coverUrl(): Attribute
    {
        return Attribute::make(function () {
            return $this->getFirstMediaUrl('conference_covers');
        });
    }

    protected function galleryUrls(): Attribute
    {
        return Attribute::make(function () {
            return $this->getMedia('conference_gallery')->map(function ($media) {
                return $media->getUrl();
            });
        });
    }

    protected function sponsorsLogosUrls(): Attribute
    {
        return Attribute::make(function () {
            return $this->getMedia('conference_sponsor_logos')->map(function ($media) {
                return $media->getUrl();
            });
        });
    }

    protected function programUrl(): Attribute
    {
        return Attribute::make(function () {
            $media = $this->getFirstMedia('conference_program');
            return $media ? $media->getUrl() : null;
        });
    }
}
