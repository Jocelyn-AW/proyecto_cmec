<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Helpers\Constants;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Webinar extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = Constants::TABLE_WEBINARS;

    protected $fillable = [
        'topic',
        'description',
        'objectives',
        'date',
        'duration',
        'organized_by',
        'sponsored_by',
        'link',
        'guest_price',
        'resident_price',
        'member_price',
        'bank_detail_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'date',
    ];

    protected $appends = [
        'cover_url',
        'gallery_urls',
        'sponsors_logos_urls',
        'program_url',
    ];

    public function attendees()
    {
        return $this->morphMany(Attendee::class, 'event');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('webinars_covers')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public')
            ->singleFile()
            ->withResponsiveImages();

        $this->addMediaCollection('webinars_gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public')
            ->withResponsiveImages();

        $this->addMediaCollection('webinars_sponsors_logos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public')
            ->withResponsiveImages();

        $this->addMediaCollection('webinars_program')
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('public');
    }

    protected function coverUrl(): Attribute
    {
        return Attribute::make(function () {
            return $this->getFirstMediaUrl('webinars_covers');
        });
    }

    protected function galleryUrls(): Attribute
    {
        return Attribute::make(function () {
            return $this->getMedia('webinars_gallery')->map(function ($media) {
                return $media->getUrl();
            });
        });
    }

    protected function sponsorsLogosUrls(): Attribute
    {
        return Attribute::make(function () {
            return $this->getMedia('webinars_sponsors_logos')->map(function ($media) {
                return $media->getUrl();
            });
        });
    }

    protected function programUrl(): Attribute
    {
        return Attribute::make(function () {
            $media = $this->getFirstMedia('webinars_program');
            return $media ? $media->getUrl() : null;
        });
    }
}
