<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use App\Http\Helpers\Constants;
use App\Traits\HasSponsorMedia;

class Webinar extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes, HasSponsorMedia;

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
        'program_url',
        'platinum_sponsors_urls',
        'golden_sponsors_urls',
        'silver_sponsors_urls',
        'albums_with_photos_count',
    ];

    protected $casts = [
        'resident_price'  => 'decimal:2',
        'guest_price'     => 'decimal:2',
        'member_price'    => 'decimal:2',
        'is_active'       => 'boolean',
        'format'          => 'string',
        'address'         => 'string',
        'additional_info' => 'string',
    ];

    // ---------------------------------------------
    // Booted
    // ---------------------------------------------

    protected static function booted()
    {
        static::deleted(function ($model) {
            if ($model->isForceDeleting()) {
                $model->sessions()->forceDelete();
                $model->attendees()->forceDelete();
            } else {
                $model->sessions()->delete();
                $model->attendees()->delete();
                $model->updateQuietly(['is_active' => false]);
            }
        });

        static::restored(function ($model) {
            $model->sessions()->withTrashed()->restore();
            $model->attendees()->withTrashed()->restore();
            $model->updateQuietly(['is_active' => true]);
        });
    }

    // ---------------------------------------------
    // Scopes
    // ---------------------------------------------

    public function scopeWithTrashFilter($query, $filter)
    {
        return match ($filter) {
            'all'     => $query->withTrashed(),
            'trashed' => $query->onlyTrashed(),
            default   => $query,
        };
    }

    // ---------------------------------------------
    // Relations
    // ---------------------------------------------

    public function attendees(): MorphMany
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

    public function bankDetails()
    {
        return $this->morphOne(BankDetail::class, 'event');
    }

    public function albums(): MorphMany
    {
        return $this->morphMany(Album::class, 'event');
    }

    // ---------------------------------------------
    // Media Collections
    // ---------------------------------------------

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('webinars_covers')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public')
            ->singleFile();

        $this->addMediaCollection('webinars_previews')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public')
            ->singleFile();

        $this->addMediaCollection('webinars_program')
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('public');

        $this->registerSponsorMediaCollections();
    }

    // ---------------------------------------------
    // Media Attributes
    // ---------------------------------------------

    protected function coverUrl(): Attribute
    {
        return Attribute::make(fn() => $this->getFirstMediaUrl('webinars_covers'));
    }

    protected function coverPreviewUrl(): Attribute
    {
        return Attribute::make(fn() => $this->getFirstMediaUrl('webinars_previews'));
    }

    protected function programUrl(): Attribute
    {
        return Attribute::make(function () {
            $media = $this->getFirstMedia('webinars_program');
            return $media ? $media->getUrl() : null;
        });
    }

    protected function albumsWithPhotosCount(): Attribute
    {
        return Attribute::make(function () {
            return $this->albums()
                ->whereHas('media')
                ->count();
        });
    }

    // ---------------------------------------------
    // Sponsor
    // ---------------------------------------------

    public function sponsorCollectionPrefix(): string
    {
        return 'webinars';
    }
}
