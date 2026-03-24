<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Http\Helpers\Constants;

class MembershipPrice extends Model
{
    protected $table = Constants::TABLE_MEMBERSHIP_PRICES;

    protected $fillable = [
        'membership_id',
        'start_date',
        'end_date',
        'amount_general',
        'amount_preferential',
    ];

    protected $casts = [
        'start_date'          => 'date:Y-m-d',
        'end_date'            => 'date:Y-m-d',
        'amount_general'      => 'decimal:2',
        'amount_preferential' => 'decimal:2',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
        'deleted_at'          => 'datetime',
    ];

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class, 'membership_id');
    }
}
