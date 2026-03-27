<?php

namespace App\Observers;

use App\Models\MembershipPrice;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;

class MembershipPriceObserver
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('cashier.secret'));
    }
    /**
     * Handle the MembershipPrice "created" event.
     */
    public function created(MembershipPrice $membershipPrice): void
    {
        $membership = $membershipPrice->membership;

        if (!$membership->stripe_product_id) return;

        $generalPrice = $this->stripe->prices->create([
            'product'     => $membership->stripe_product_id,
            'unit_amount' => (int) ($membershipPrice->amount_general * 100),
            'currency'    => 'mxn',
            'nickname'    => 'General ' . $membershipPrice->start_date . ' - ' . $membershipPrice->end_date,
        ]);

        $preferentialPrice = $this->stripe->prices->create([
            'product'     => $membership->stripe_product_id,
            'unit_amount' => (int) ($membershipPrice->amount_preferential * 100),
            'currency'    => 'mxn',
            'nickname'    => 'Preferencial ' . $membershipPrice->start_date . ' - ' . $membershipPrice->end_date,
        ]);

        DB::table('membership_prices')
            ->where('id', $membershipPrice->id)
            ->update([
                'stripe_price_general_id'       => $generalPrice->id,
                'stripe_price_preferential_id'  => $preferentialPrice->id,
            ]);
    }

    /**
     * Handle the MembershipPrice "updated" event.
     */
    public function updated(MembershipPrice $membershipPrice): void
    {
        $stripe    = $this->stripe;
        $newPrices = [];
        $membership = $membershipPrice->membership;

        if (!$membership->stripe_product_id) return; 

        if ($membershipPrice->wasChanged('amount_general')) {
            if ($membershipPrice->getOriginal('stripe_price_general_id')) {
                $stripe->prices->update($membershipPrice->getOriginal('stripe_price_general_id'), ['active' => false]);
            }
            $price = $stripe->prices->create([
                'product'     => $membership->stripe_product_id,
                'unit_amount' => (int) ($membershipPrice->amount_general * 100),
                'currency'    => 'mxn',
                'nickname'    => 'General ' . $membershipPrice->start_date . ' - ' . $membershipPrice->end_date,
            ]);
            $newPrices['stripe_price_general_id'] = $price->id;
        }

        if ($membershipPrice->wasChanged('amount_preferential')) {
            if ($membershipPrice->getOriginal('stripe_price_preferential_id')) {
                $stripe->prices->update($membershipPrice->getOriginal('stripe_price_preferential_id'), ['active' => false]);
            }
            $price = $stripe->prices->create([
                'product'     => $membership->stripe_product_id,
                'unit_amount' => (int) ($membershipPrice->amount_preferential * 100),
                'currency'    => 'mxn',
                'nickname'    => 'Preferencial ' . $membershipPrice->start_date . ' - ' . $membershipPrice->end_date,
            ]);
            $newPrices['stripe_price_preferential_id'] = $price->id;
        }

        if (!empty($newPrices)) {
            DB::table('membership_prices')
                ->where('id', $membershipPrice->id)
                ->update($newPrices);
        }
    }

    /**
     * Handle the MembershipPrice "deleted" event.
     */
    public function deleted(MembershipPrice $membershipPrice): void
    {
        //
    }

    /**
     * Handle the MembershipPrice "restored" event.
     */
    public function restored(MembershipPrice $membershipPrice): void
    {
        //
    }

    /**
     * Handle the MembershipPrice "force deleted" event.
     */
    public function forceDeleted(MembershipPrice $membershipPrice): void
    {
        //
    }
}
