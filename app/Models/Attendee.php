<?php

namespace App\Models;

use App\Http\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attendee extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = Constants::TABLE_ATTENDEES;

    protected $fillable = [
        'event_id',
        'event_type',
        'person_id',
        'person_type',
        'folio',
        'name',
        'email',
        'phone',
        'state', 
        'city',
        'status',
        'price',
        'did_attend'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'did_attend' => 'boolean',
    ];

    protected $appends = [
        'diploma_url',
    ];

    public function event() : MorphTo
    {
        return $this->morphTo(null, 'event_type', 'event_id');
    }

    public function person() : MorphTo
    {
        return $this->morphTo(null, 'person_type', 'person_id');
    }

    public function registermediaCollections(): void
    {
        $this->addMediaCollection('diplomas')
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('public')
            ->singleFile();
    }

    protected function diplomaUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                $media = $this->getFirstMedia('diplomas');
                return $media ? $media->getUrl() : null;
            }
        );
    }
}
