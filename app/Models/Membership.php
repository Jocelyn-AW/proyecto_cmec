<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Http\Helpers\Constants;

class Membership extends Model
{
    use SoftDeletes;

    protected $table = Constants::TABLE_MEMBERSHIPS;

    protected $fillable = [
        'name',
        'description',
        'benefits',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ---------------------------------------------
    // Relations
    // ---------------------------------------------

    public function prices(): HasMany
    {
        return $this->hasMany(MembershipPrice::class, 'membership_id')
            ->orderBy('start_date', 'asc');
    }

    // ---------------------------------------------
    // Scopes
    // ---------------------------------------------

    public function scopeWithTrashFilter($query, $filter)
    {
        return match ($filter) {
            'all'     => $query->withTrashed(),
            'trashed' => $query->onlyTrashed(),
            default   => $query,
        };
    }
}
