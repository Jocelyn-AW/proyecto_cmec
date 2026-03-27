<?php

namespace App\Listeners;

use App\Http\Controllers\Admin\AttendeesController;
use App\Models\Attendee;
use Laravel\Cashier\Events\WebhookReceived;

class StripeWebhookListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(WebhookReceived $event): void
    {
        if ($event->payload['type'] === 'checkout.session.completed') {
            $session = $event->payload['data']['object'];

            // Buscar el attendee por su stripe_id
            $attendee = Attendee::where('stripe_id', $session['customer'])->first();

            if ($attendee) {
                $attendee->update(['status' => 'paid']);

                AttendeesController::registerPayment($attendee, [
                    'payment_method' => 'stripe',
                    'reference'      => $session['payment_intent'],
                    'status'         => 'paid',
                ]);
            }
        }
    }
}
