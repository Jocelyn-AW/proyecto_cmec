<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Clinic extends Model
{
    use SoftDeletes;

    protected $fillable =  [
        'member_id',
        'hospital_name',
        'address',
        'phone',
        'schedule'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    //---------------------
    //Relations
    //---------------------

    public function member() : HasOne
    {
        return $this->hasOne(Member::class);
    }
}
