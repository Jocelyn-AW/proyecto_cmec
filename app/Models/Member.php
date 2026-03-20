<?php

namespace App\Models;

use App\Http\Helpers\Constants;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Member extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = Constants::TABLE_MEMBERS;

    protected $fillable = [
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

    protected $casts = [
        'inscription_date' => 'date',
        'expiration_date'  => 'date',
        'user_id'          => 'integer',
        'invoice_data_id'  => 'integer',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
        'deleted_at'       => 'datetime',
    ];

    protected $appends = [
        'diploma_especialidad_url',
        'titulo_medico_url',
        'diploma_consejo_url',
        'cedula_profesion_url',
        'cedula_especialista_url',
        'constancia_fiscal_url',
        'factura_url',
    ];

    // ---------------------------------------------
    // Booted
    // ---------------------------------------------

    protected static function booted()
    {
        static::deleted(function ($model) {
            if ($model->isForceDeleting()) {
                User::withTrashed()->where('id', $model->user_id)->forceDelete();
                $model->payments()->forceDelete();
            } else {
                $user = User::find($model->user_id);
                $user?->delete();
                $model->payments()->delete();
            }
        });

        static::restored(function ($model) {
            User::withTrashed()->where('id', $model->user_id)->restore();
            $model->payments()->withTrashed()->restore();
        });
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

    // ---------------------------------------------
    // Relations
    // ---------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // public function invoiceData() : HasOne
    // {
    //     return $this->hasOne(InvoiceData::class, 'id', 'invoice_data_id');
    // }

    public function invoiceData() : MorphOne
    {
        return $this->morphOne(InvoiceData::class, 'billable');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'user');
    }

    // ---------------------------------------------
    // Media Collections
    // ---------------------------------------------

    public function registerMediaCollections(): void
    {
        // Documentos: todos PDFs, uno por coleccion
        $collections = [
            'members_diploma_especialidad', // Diploma de especialidad
            'members_titulo_medico',        // Titulo medico general
            'members_diploma_consejo',      // Diploma del consejo 
            'members_cedula_profesion',     // Cedula de profesión
            'members_cedula_especialista',  // Cedula de especialista
            'members_constancia_fiscal',    // Constancia fiscal (RFC)
            'members_factura',              // Factura
        ];

        foreach ($collections as $collection) {
            $this->addMediaCollection($collection)
                ->acceptsMimeTypes(['application/pdf'])
                ->useDisk('public')
                ->singleFile();
        }
    }

    // ---------------------------------------------
    // Media Attributes
    // ---------------------------------------------

    protected function diplomaEspecialidadUrl(): Attribute
    {
        return Attribute::make(function () {
            $media = $this->getFirstMedia('members_diploma_especialidad');
            return $media ? $media->getUrl() : null;
        });
    }

    protected function tituloMedicoUrl(): Attribute
    {
        return Attribute::make(function () {
            $media = $this->getFirstMedia('members_titulo_medico');
            return $media ? $media->getUrl() : null;
        });
    }

    protected function diplomaConsejoUrl(): Attribute
    {
        return Attribute::make(function () {
            $media = $this->getFirstMedia('members_diploma_consejo');
            return $media ? $media->getUrl() : null;
        });
    }

    protected function cedulaProfesionUrl(): Attribute
    {
        return Attribute::make(function () {
            $media = $this->getFirstMedia('members_cedula_profesion');
            return $media ? $media->getUrl() : null;
        });
    }

    protected function cedulaEspecialistaUrl(): Attribute
    {
        return Attribute::make(function () {
            $media = $this->getFirstMedia('members_cedula_especialista');
            return $media ? $media->getUrl() : null;
        });
    }

    protected function constanciaFiscalUrl(): Attribute
    {
        return Attribute::make(function () {
            $media = $this->getFirstMedia('members_constancia_fiscal');
            return $media ? $media->getUrl() : null;
        });
    }

    protected function facturaUrl(): Attribute
    {
        return Attribute::make(function () {
            $media = $this->getFirstMedia('members_factura');
            return $media ? $media->getUrl() : null;
        });
    }
}
