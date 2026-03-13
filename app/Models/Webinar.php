<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Helpers\Constants;
use App\Traits\HasSponsorMedia;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Webinar extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes, HasSponsorMedia;

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
        'duration',
        'organized_by',
        'sponsored_by',
        'link',
        'guest_price',
        'resident_price',
        'member_price',
        'bank_detail_id',
        'is_active',
        'format',
        'address',
        'additional_info',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'cover_url',
        'cover_preview_url',
        'gallery_urls',
        'program_url',
        'platinum_sponsors_urls', //
        'golden_sponsors_urls', //  <- sponsor
        'silver_sponsors_urls', //
    ];

    protected $casts = [
        'resident_price' => 'decimal:2',
        'guest_price' => 'decimal:2',
        'member_price' => 'decimal:2',
        'is_active' => 'boolean',
        'format' => 'string',
        'address' => 'string',
        'additional_info' => 'string',
    ];

    public function attendees()
    {
        return $this->morphMany(Attendee::class, 'event');
    }

    public function sessions(): MorphMany
    {
        return $this->morphMany(EventSession::class, 'sessionable');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'event_payed');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('webinars_covers')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public')
            ->singleFile()
            ->withResponsiveImages();

        $this->addMediaCollection('webinars_previews')
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

        $this->registerSponsorMediaCollections(); // <----- sponsor
    }

    protected function coverUrl(): Attribute
    {
        return Attribute::make(function () {
            return $this->getFirstMediaUrl('webinars_covers');
        });
    }

    protected function coverPreviewUrl(): Attribute
    {
        return Attribute::make(function () {
            return $this->getFirstMediaUrl('webinars_previews');
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

    public function bankDetails()
    {
        return $this->morphOne(BankDetail::class, 'event');
    }

    // sponsor
    public function sponsorCollectionPrefix(): string
    {
        return 'webinars';
    }
}
