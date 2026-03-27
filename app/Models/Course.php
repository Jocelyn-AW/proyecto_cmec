<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Helpers\Constants;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\EventSession;
use App\Traits\HasStripeProduct;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes, HasStripeProduct;

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
        'bank_detail_id',
        'is_active',
        'format',
        'address',
        'additional_info',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'date',
    ];

    protected $appends = [
        'cover_url',
        'cover_preview_url',
        'gallery_urls',
        'platinum_sponsors_urls',
        'golden_sponsors_urls',
        'silver_sponsors_urls',
        'program_url',
        'albums_count'
    ];

    protected $casts = [
        'resident_price' => 'decimal:2',
        'guest_price' => 'decimal:2',
        'member_price' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    
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

    public function scopeWithTrashFilter($query, $filter)
    {
        return match ($filter) {
            'all' => $query->withTrashed(),      // Todos
            'trashed' => $query->onlyTrashed(),  // Solo Inactivos
            default => $query,                   // Solo Activos
        };
    }

    //Relations
    public function attendees()
    {
        return $this->morphMany(Attendee::class, 'event');
    }

    public function sessions(): MorphMany
    {
        return $this->morphMany(EventSession::class, 'sessionable');
    }

    public function albums()
    {
        return $this->morphMany(Album::class, 'event');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'event_payed');
    }

    public function bankDetails()
    {
        return $this->morphOne(BankDetail::class, 'event');
    }

    //Media Collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('courses_covers')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public')
            ->singleFile();

        $this->addMediaCollection('courses_previews')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public')
            ->singleFile();

        $this->addMediaCollection('courses_gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public');

        $this->addMediaCollection('courses_platinum_sponsors')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public');

        $this->addMediaCollection('courses_golden_sponsors')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public');

        $this->addMediaCollection('courses_silver_sponsors')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public');

        $this->addMediaCollection('courses_program')
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('public');
    }


    //Media Attributes
    protected function coverUrl(): Attribute
    {
        return Attribute::make(function () {
            return $this->getFirstMediaUrl('courses_covers');
        });
    }

    protected function coverPreviewUrl(): Attribute
    {
        return Attribute::make(function () {
            return $this->getFirstMediaUrl('courses_previews');
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

    protected function platinumSponsorsUrls(): Attribute
    {
        return Attribute::make(function () {
            return $this->getMedia('courses_platinum_sponsors')->map(function ($media) {                
                return [
                    'id' => $media->id,
                    'url' =>  $media->getUrl()
                ];
            });
        });
    }

    protected function goldenSponsorsUrls(): Attribute
    {
        return Attribute::make(function () {
            return $this->getMedia('courses_golden_sponsors')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'url' =>  $media->getUrl()
                ];
            });
        });
    }

    protected function silverSponsorsUrls(): Attribute
    {
        return Attribute::make(function () {
            return $this->getMedia('courses_silver_sponsors')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'url' =>  $media->getUrl()
                ];
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
    
    protected function albumsCount(): Attribute
    {
        return Attribute::make(function () {
            return $this->albums()->whereHas('media')->count();
        });
    }

    // ---------------------------------------------
    // Stripe
    //----------------------------------------------

    protected function getStripePriceFields(): array
    {
        return [
            'member_price'   => 'Miembro',
            'guest_price'    => 'Invitado',
            'resident_price' => 'Residente'
        ];
    }

    protected function getStripeName(): string
    {
        return $this->topic;
    }
}
