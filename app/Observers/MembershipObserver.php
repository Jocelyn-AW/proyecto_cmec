<?php

namespace App\Observers;

use App\Models\Membership;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class MembershipObserver
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('cashier.secret'));
    }

    /**
     * Handle the Membership "created" event.
     */
    public function created(Membership $membership): void
    {
        $product = $this->stripe->products->create([
            'name'     => $membership->name,
            'metadata' => ['type' => 'membership'],
        ]);

        DB::table('memberships')
            ->where('id', $membership->id)
            ->update(['stripe_product_id' => $product->id]);
    }

    /**
     * Handle the Membership "updated" event.
     */
    public function updated(Membership $membership): void
    {
        Log::info('got here');

        if (!$membership->stripe_product_id) {
            $product = $this->stripe->products->create([
                'name'     => $membership->name,
                'metadata' => ['type' => 'membership'],
            ]);

            DB::table('memberships')
                ->where('id', $membership->id)
                ->update(['stripe_product_id' => $product->id]);

            $membership->refresh();
        }

        if ($membership->wasChanged('name')) {
            $this->stripe->products->update($membership->stripe_product_id, [
                'name' => $membership->name,
            ]);
        }
    }

    /**
     * Handle the Membership "deleted" event.
     */
    public function deleted(Membership $membership): void
    {
        if ($membership->stripe_product_id) {
            $this->stripe->products->update($membership->stripe_product_id, [
                'active' => false,
            ]);
        }
    }

    /**
     * Handle the Membership "restored" event.
     */
    public function restored(Membership $membership): void
    {
        //
    }

    /**
     * Handle the Membership "force deleted" event.
     */
    public function forceDeleted(Membership $membership): void
    {
        //
    }
}
