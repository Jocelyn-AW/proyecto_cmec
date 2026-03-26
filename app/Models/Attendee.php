<?php

namespace App\Models;

use App\Http\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendee extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

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
        'did_attend',
        'specialty',
        'birth_date',
        'special_needs',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'birth_date',
    ];

    protected $casts = [
        'did_attend' => 'boolean',
        'birth_date'  => 'date',
        'specialty'   => 'string',
        'special_needs' => 'string',
    ];

    protected $appends = [
        'diploma_url',
    ];

    protected static function booted()
    {
        static::deleted(function ($model) {
            if ($model->isForceDeleting()) {
                $model->payments()->forceDelete();
                $model->invoiceData()->forceDelete();
            } else {
                $model->payments()->delete();
                $model->invoiceData()->delete();
            }
        });

        static::restored(function ($model) {
            $model->payments()->withTrashed()->restore();
            $model->invoiceData()->withTrashed()->restore();
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

    public function event(): MorphTo
    {
        return $this->morphTo(null, 'event_type', 'event_id');
    }

    public function person(): MorphTo
    {
        return $this->morphTo(null, 'person_type', 'person_id');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'user');
    }

    public function invoiceData(): MorphOne
    {
        return $this->morphOne(InvoiceData::class, 'billable');
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

    public function payment(): MorphOne
    {
        // buscar el pago donde el event_payed apunta al mismo evento del attendee
        return $this->morphOne(
            Payment::class,
            'event_payed',         // event_payed_type, event_payed_id
            'event_payed_type',
            'event_payed_id',
            'event_id'
        )->where('event_payed_type', $this->event_type)
            ->latestOfMany();
    }

    public function memberPayment(): HasOne
    {
        // busca el pago del member para evento especifico
        return $this->hasOne(Payment::class, 'event_payed_id', 'event_id')
            ->where('user_type', 'member')
            ->where('user_id', $this->person_id)
            ->whereColumn('event_payed_type', 'event_type')
            ->latestOfMany('created_at');
    }
}
