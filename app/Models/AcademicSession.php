<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Helpers\Constants;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class AcademicSession extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * tabla asociada al modelo 
     */
    protected $table = Constants::TABLE_ACADEMIC_SESSIONS;

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

    protected $appends = [
        'cover_url',
        'gallery_urls',
        'program_url',
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

    // relaciones polimorficas

    public function attendees(): MorphMany
    {
        return $this->morphMany(Attendee::class, 'event');
    }

    public function sessions(): MorphMany
    {
        return $this->morphMany(EventSession::class, 'sessionable');
    }

    public function bankDetails(): MorphOne
    {
        return $this->morphOne(BankDetail::class, 'event');
    }

    // media spatie

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('academic_sessions_covers')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public')
            ->singleFile()
            ->withResponsiveImages();

        $this->addMediaCollection('academic_sessions_gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public')
            ->withResponsiveImages();

        $this->addMediaCollection('academic_sessions_program')
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('public');
    }

    //atributos

    protected function coverUrl(): Attribute
    {
        return Attribute::make(get: fn () => $this->getFirstMediaUrl('academic_sessions_covers'));
    }

    protected function galleryUrls(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->getMedia('academic_sessions_gallery')->map(fn ($media) => $media->getUrl());
        });
    }

    protected function programUrl(): Attribute
    {
        return Attribute::make(get: function () {
            $media = $this->getFirstMedia('academic_sessions_program');
            return $media ? $media->getUrl() : null;
        });
    }
}