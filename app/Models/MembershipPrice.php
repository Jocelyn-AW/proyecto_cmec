<?php

namespace App\Models;

use App\Http\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipPrice extends Model
{
    use SoftDeletes;

    protected $table = Constants::TABLE_MEMBERSHIP_PRICES;

    protected $fillable = [
        'id',
        'membership_id',
        'start_date',
        'end_date',
        'amount',
        
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'amount' => 'decimal:2',
    ];
}
