<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Helpers\Constants;
use App\Traits\HasStripeProduct;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Conference extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes, HasStripeProduct;

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
        'parent_id',
        'subtype'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'cover_url',
        'gallery_urls',
        'platinum_sponsors_urls',
        'golden_sponsors_urls',
        'silver_sponsors_urls',
        'program_url',
        'albums_count'
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

    protected static function booted()
    {
        static::deleted(function ($model) {
            if ($model->isForceDeleting()) {
                $model->sessions()->forceDelete();
                $model->attendees()->forceDelete();
                $model->children()->each(fn($child) => $child->forceDelete());
            } else {
                $model->sessions()->delete();
                $model->attendees()->delete();
                $model->children()->each(fn($child) => $child->delete());
                $model->updateQuietly(['is_active' => false]);
            }

        });

        static::restored(function ($model) {
            $model->sessions()->withTrashed()->restore();
            $model->attendees()->withTrashed()->restore();
            $model->children()->withTrashed()->each(fn($child) => $child->restore());
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

    //Children
    public function preConferences()
    {
        return $this->hasMany(Conference::class, 'parent_id')
                    ->where('type', Constants::EVENT_PRECONFERENCE);
    }

    public function transConferences()
    {
        return $this->hasMany(Conference::class, 'parent_id')
                    ->where('type', Constants::EVENT_TRANSCONFERENCE);
    }

    public function children()
    {
        return $this->hasMany(Conference::class, 'parent_id');
    }

    //Parent
    public function parent()
    {
        return $this->belongsTo(Conference::class, 'parent_id');
    }

    //Morph Relations
    public function sessions()
    {
        return $this->morphMany(EventSession::class, 'sessionable');
    }

    public function albums()
    {
        return $this->hasMany(Album::class, 'event_id')
                    ->where('event_type', $this->subtype);
    }

    public function attendees()
    {
        return $this->hasMany(Attendee::class, 'event_id')
                    ->where('event_type', $this->subtype ?? 'conference');
    }

    //Media
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('conference_covers')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public')
            ->singleFile();

        $this->addMediaCollection('conference_gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public');

        $this->addMediaCollection('conference_platinum_sponsors')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public');

        $this->addMediaCollection('conference_golden_sponsors')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public');

        $this->addMediaCollection('conference_silver_sponsors')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public');

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

    protected function platinumSponsorsUrls(): Attribute
    {
        return Attribute::make(function () {
            return $this->getMedia('conference_platinum_sponsors')->map(function ($media) {                
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
            return $this->getMedia('conference_golden_sponsors')->map(function ($media) {
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
            return $this->getMedia('conference_silver_sponsors')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'url' =>  $media->getUrl()
                ];
            });
        });
    }

    protected function albumsCount(): Attribute
    {
        return Attribute::make(function () {
            return $this->albums()->count();
        });
    }

    //Stripe
    protected function getStripePriceFields(): array
    {
        return [
            'member_price'   => 'Miembro',
            'guest_price'    => 'Invitado',
            'resident_price' => 'Residente',
            'nurse_price'    => 'Enfermero',
            'surgeon_price'  => 'Cirujano',
        ];
    }

    protected function getStripeName(): string
    {
        return $this->name;
    }
}
