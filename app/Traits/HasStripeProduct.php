<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;

trait HasStripeProduct
{
    public static function bootHasStripeProduct(): void
    {
        static::created(function ($model) {
            $model->createStripeProduct();
        });

        static::updated(function ($model) {
            $model->updateStripePrices();
        });

        static::deleted(function ($model) {
            $model->archiveStripeProduct();
        });
    }

    protected function getStripe(): StripeClient
    {
        return new StripeClient(config('cashier.secret'));
    }

    protected function createStripeProduct(): void
    {
        $stripe  = $this->getStripe();
        $product = $stripe->products->create([
            'name'     => $this->getStripeName(),
            'metadata' => [
                'event_type' => $this->getStripeEventType(),
                'event_id'   => $this->id,
            ],
        ]);

        $prices = ['stripe_product_id' => $product->id];

        foreach ($this->getStripePriceFields() as $field => $nickname) {
            if (!empty($this->$field)) {
                $prices[$this->getStripePriceIdField($field)] = $this->createStripePrice(
                    $product->id,
                    $this->$field,
                    $nickname
                );
            }
        }

        DB::table($this->getTable())
            ->where('id', $this->id)
            ->update($prices);
    }

    protected function updateStripePrices(): void
    {
        $stripe    = $this->getStripe();
        $newPrices = [];

        foreach ($this->getStripePriceFields() as $field => $nickname) {
            if ($this->wasChanged($field) && !empty($this->$field)) {
                $stripeIdField = $this->getStripePriceIdField($field);

                // Archivar precio anterior
                if ($oldPriceId = $this->getOriginal($stripeIdField)) {
                    $stripe->prices->update($oldPriceId, ['active' => false]);
                }

                // Crear nuevo precio
                $newPrices[$stripeIdField] = $this->createStripePrice(
                    $this->stripe_product_id,
                    $this->$field,
                    $nickname
                );
            }
        }

        if (!empty($newPrices)) {
            DB::table($this->getTable())
                ->where('id', $this->id)
                ->update($newPrices);
        }
    }

    protected function archiveStripeProduct(): void
    {
        if ($this->stripe_product_id) {
            $this->getStripe()->products->update($this->stripe_product_id, [
                'active' => false,
            ]);
        }
    }

    protected function createStripePrice(string $productId, float $amount, string $nickname): string
    {
        $price = $this->getStripe()->prices->create([
            'product'     => $productId,
            'unit_amount' => (int) ($amount * 100),
            'currency'    => 'mxn',
            'nickname'    => $nickname,
        ]);

        return $price->id;
    }

    // Convierte 'member_price' en 'stripe_price_member_id'
    protected function getStripePriceIdField(string $priceField): string
    {
        return 'stripe_price_' . str_replace('_price', '_id', $priceField);
    }

    // Métodos que cada modelo puede sobreescribir
    protected function getStripeName(): string
    {
        return $this->name ?? $this->topic ?? 'Evento';
    }

    protected function getStripeEventType(): string
    {
        return class_basename($this);
    }

    // Cada modelo define sus campos de precio
    abstract protected function getStripePriceFields(): array;
}