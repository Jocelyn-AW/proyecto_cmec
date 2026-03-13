<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait HandlesSponsorMedia
{
    /**
     * agrega logos nuevos y elimina los marcados
     */
    protected function updateSponsorMedia($model, Request $request): void
    {
        $prefix = $model->sponsorCollectionPrefix();

        foreach (['platinum', 'golden', 'silver'] as $tier) {

            // agregar nuevos
            if ($request->hasFile("{$tier}_sponsors")) {
                foreach ($request->file("{$tier}_sponsors") as $logo) {
                    $model->addMedia($logo)
                        ->toMediaCollection("{$prefix}_{$tier}_sponsors");
                }
            }

            // eliminar los marcados para borrar
            $deleteIds = $request->input("{$tier}_delete", []);
            if (!empty($deleteIds)) {
                foreach ($deleteIds as $id) {
                    $media = $model->getMedia("{$prefix}_{$tier}_sponsors")
                        ->where('id', $id)
                        ->first();
                    if ($media) {
                        $media->delete();
                    }
                }
            }
        }
    }

    protected function deleteSponsorMedia($model): void
    {
        $model->clearSponsorCollections();
    }

    protected function sponsorValidationRules(): array
    {
        $mimes = 'nullable|image|mimes:jpeg,png,jpg,webp';

        return [
            'platinum_sponsors.*' => $mimes,
            'golden_sponsors.*'   => $mimes,
            'silver_sponsors.*'   => $mimes,
            'platinum_delete.*'   => 'nullable|integer',
            'golden_delete.*'     => 'nullable|integer',
            'silver_delete.*'     => 'nullable|integer',
        ];
    }
}
