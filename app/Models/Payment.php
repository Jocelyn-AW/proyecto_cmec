<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Helpers\Constants;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Payment extends Model
{
    use SoftDeletes;
    
    protected $table = Constants::TABLE_PAYMENTS;

    protected $fillable = [
        'user_type',
        'user_id',
        'event_payed_id',
        'event_payed_type',
        'payer_name',
        'payer_email',
        'payer_phone',
        'payment_method',
        'amount',
        'reference',
        'status',
        'payment_date',
        // 'created_by'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'payment_date'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'created_by' => 'integer',
    ];


    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    public function eventPayed(): MorphTo
    {
        return $this->morphTo('event_payed');
    }

    protected static function booted()
    {
        static::saving(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
