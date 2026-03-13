<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Http\Helpers\Constants;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventSession extends Model
{
    use SoftDeletes;

    protected $table = Constants::TABLE_EVENT_SESSIONS;

    protected $fillable = [
        // 'sessionable_id',
        // 'sessionable_type',
        'date', 
        'time'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function sessionable(): MorphTo
    {
        return $this->morphTo();
    }
}
