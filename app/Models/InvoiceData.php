<?php

namespace App\Models;

use App\Http\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceData extends Model
{
    use SoftDeletes;

    protected $table = Constants::TABLE_INVOICE_DATA;

    protected $fillable = [
        'id',
        'rfc',
        'name',
        'email',
        'postal_code',
        'tax_regime',
        'cfdi_use',
        'address',
        
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function scopeWithTrashFilter($query, $filter)
    {
        return match ($filter) {
            'all' => $query->withTrashed(),
            'trashed' => $query->onlyTrashed(),
            default => $query,
        };
    }
}
