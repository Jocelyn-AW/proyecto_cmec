<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class BankDetail extends Model
{
    use HasFactory;

    protected $table = 'bank_details';

    protected $fillable = [
        'bank',
        'account_number',
        'clabe_number',
        'reference',
        'beneficiary',
        'subsidiary',
        'updated_by',
    ];

    protected $casts = [
        'updated_by' => 'integer',
    ];

    /*
    |-----------------------
    | relaciones
    |-----------------------
    */

    /**
     * usuario que realizo la ultima actualizacion
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |-----------------------
    | eventos del modelo
    |-----------------------
    */

    /**
     * asignar automaticamente el usuario autenticado
     * al crear o actualizar el registro
     */
    /* protected static function booted()
    {
        static::saving(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    } */
    protected static function booted()
    {
        static::saving(function ($model) {
            $model->updated_by = Auth::check() ? Auth::id() : 1;
        });
    }

    /*
    |-----------------------
    | accesibilidad
    |-----------------------
    */

    /**
     * scope para asegurar que tenga al menos
     * account_number o clabe_number
     */
    public function scopeWithValidAccount($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('account_number')
                ->orWhereNotNull('clabe_number');
        });
    }

    /*
    |-----------------------
    | accesorios
    |-----------------------
    */

    /**
     * devuelve el numero principal disponible
     */
    public function getMainAccountAttribute()
    {
        return $this->account_number ?? $this->clabe_number;
    }
}
