<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Helpers\Constants;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Course extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = Constants::TABLE_COURSES;

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
        $this->addMediaCollection('courses_covers')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public')
            ->singleFile()
            ->withResponsiveImages();

        $this->addMediaCollection('courses_gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public')
            ->withResponsiveImages();

        $this->addMediaCollection('courses_sponsors_logos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public')
            ->withResponsiveImages();

        $this->addMediaCollection('courses_program')
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('public');
    }

    protected function coverUrl(): Attribute
    {
        return Attribute::make(function () {
            return $this->getFirstMediaUrl('courses_covers');
        });
    }

    protected function galleryUrls(): Attribute
    {
        return Attribute::make(function () {
            return $this->getMedia('courses_gallery')->map(function ($media) {
                return $media->getUrl();
            });
        });
    }

    protected function sponsorsLogosUrls(): Attribute
    {
        return Attribute::make(function () {
            return $this->getMedia('courses_sponsors_logos')->map(function ($media) {
                return $media->getUrl();
            });
        });
    }

    protected function programUrl(): Attribute
    {
        return Attribute::make(function () {
            $media = $this->getFirstMedia('courses_program');
            return $media ? $media->getUrl() : null;
        });
    }

    public function bankDetails()
    {
        return $this->morphOne(BankDetail::class, 'event');
    }

    
}
