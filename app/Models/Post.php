<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Translation\Loader\FileLoader;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

class Post extends Model implements HasMedia
{
    use InteractsWithMedia;
    
    //cuando se usa esto, los registros no se eliminan solo se marcan con deleted_at
    //use SoftDeletes;

    protected $table = 'posts';

    protected $fillable =  [
        'link',
        'order',
        'is_active'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $appends = [
        'image_url'
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('posts')
        ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
        ->useDisk('public')
        ->singleFile()
        ->withResponsiveImages();
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::make( function() {
            return $this->getFirstMediaUrl('posts');
        });
    }
}
