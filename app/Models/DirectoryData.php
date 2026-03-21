<?php

namespace App\Models;

use App\Http\Helpers\Constants;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DirectoryData extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = Constants::TABLE_DIRECTORY_DATA;

    protected $fillable = [
        'member_id',
        'name',
        'specialty',
        'state',
        'city',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $appends = [
        'profile_url',
        'cv_url'
    ];

    //----------------------------------
    //Relations
    //----------------------------------

    public function member() : HasOne
    {
        return $this->hasOne(Member::class);
    }

    //----------------------------------
    //Media
    //----------------------------------

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('directory_profile')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public');

        $this->addMediaCollection('directory_cv')
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('public');
    }

    //----------------------------------
    //Attributes
    //----------------------------------

    protected function profileUrl() : Attribute
    {
        return Attribute::make(function () {
            return $this->getFirstMediaUrl('directory_profile');
        });
    }

    protected function cvUrl(): Attribute
    {
        return Attribute::make(function () {
            $media = $this->getFirstMedia('directory_cv');
            return $media ? $media->getUrl() : null;
        });
    }
}
