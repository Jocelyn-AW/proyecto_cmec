<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
USE App\Http\Helpers\Constants;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BankDetail extends Model
{
    protected $table = Constants::TABLE_BANK_DETAILS;

    protected $fillable = [
        'bank',
        'account_number',
        'clabe_number',
        'reference',
        'beneficiary',
        'subsidiary',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function event() : MorphTo
    {
        return $this->morphTo();
    }
}
