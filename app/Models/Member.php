<?php

namespace App\Models;

use App\Http\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Member extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = Constants::TABLE_MEMBERS;

    protected $fillable = [
        'id',
        'cmec_member_id',
        'name',
        'last_name',
        'phone',
        'email',
        'city',
        'state',
        'hospital',
        'inscription_date',
        'expiration_date',
        'user_id',
        'invoice_data_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $appends = [];

    protected $casts = [
        'inscription_date' => 'date',
        'expiration_date' => 'date',
        'user_id' => 'integer',
        'invoice_data_id' => 'integer',
    ];

    protected static function booted()
    {
        static::deleted(function ($model) {
            if ($model->isForceDeleting()) {
                $model->user()->forceDelete();
                $model->invoiceData()->forceDelete();
                $model->payments()->forceDelete();
            } else {
                $model->user()->delete();
                $model->invoiceData()->delete();
                $model->payments()->delete();
            }
        });

        static::restored(function ($model) {
            $model->user()->withTrashed()->restore();
            $model->invoiceData()->withTrashed()->restore();
            $model->payments()->withTrashed()->restore();
        });
    }

    public function scopeWithTrashFilter($query, $filter)
    {
        return match ($filter) {
            'all' => $query->withTrashed(),
            'trashed' => $query->onlyTrashed(),
            default => $query,
        };
    }

    //Relations
    public function user() : HasOne
    {
        return $this->hasOne(User::class);
    }

    // public function invoiceData() : HasOne
    // {
    //     return $this->hasOne(InvoiceData::class, 'id', 'invoice_data_id');
    // }

    public function invoiceData() : MorphOne
    {
        return $this->morphOne(InvoiceData::class, 'billable');
    }

    public function payments() : MorphMany
    {
        return $this->morphMany(Payment::class, 'user');
    }

}
