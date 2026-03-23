<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasSponsorMedia
{
    /**
     * cada vez que un modelo use este trait debe definir este metodo
     * ej. return 'webinars'; -> colecciones: webinars_platinum_sponsors para separacion
     */
    public function sponsorCollectionPrefix(): string
    {
        throw new \RuntimeException(
            static::class . ' debe implementar sponsorCollectionPrefix()'
        );
    }

    public function registerSponsorMediaCollections(): void
    {
        $prefix = $this->sponsorCollectionPrefix();
        $mimes  = ['image/jpeg', 'image/png', 'image/webp'];

        foreach (['platinum', 'golden', 'silver'] as $tier) {
            $this->addMediaCollection("{$prefix}_{$tier}_sponsors")
                ->acceptsMimeTypes($mimes)
                ->useDisk('public');
        }
    }

    public function getSponsorTierUrls(string $tier): \Illuminate\Support\Collection
    {
        $prefix = $this->sponsorCollectionPrefix();

        return $this->getMedia("{$prefix}_{$tier}_sponsors")
            ->map(fn($media) => [
                'id'  => $media->id,
                'url' => $media->getUrl(),
            ]);
    }

    public function clearSponsorCollections(): void
    {
        $prefix = $this->sponsorCollectionPrefix();

        foreach (['platinum', 'golden', 'silver'] as $tier) {
            $this->clearMediaCollection("{$prefix}_{$tier}_sponsors");
        }
    }

    // atributos dinmicos ($appends del modelo)
    protected function platinumSponsorsUrls(): Attribute
    {
        return Attribute::make(fn() => $this->getSponsorTierUrls('platinum'));
    }

    protected function goldenSponsorsUrls(): Attribute
    {
        return Attribute::make(fn() => $this->getSponsorTierUrls('golden'));
    }

    protected function silverSponsorsUrls(): Attribute
    {
        return Attribute::make(fn() => $this->getSponsorTierUrls('silver'));
    }
}
