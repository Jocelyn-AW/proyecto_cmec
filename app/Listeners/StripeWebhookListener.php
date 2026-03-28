<?php

namespace App\Listeners;

use App\Http\Controllers\Admin\AttendeesController;
use App\Models\Attendee;
use App\Models\Member;
use App\Models\MembershipPrice;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
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
            $metadata = $session['metadata'] ?? [];

            Log::info('session '. $session);
            Log::info('meta '. $metadata);

            //Si es membresia
            if (isset($metadata['member_id'])) {
                Log::info('es un pago de membresia');
                $this->handleMembershipPayment($session, $metadata);
                return;
            }

            Log::info('es un pago de evento');

            // Buscar el attendee por su stripe_id
            $attendee = Attendee::where('stripe_id', $session['customer'])->first();
            

            if ($attendee) {
                Log::info('ejecutar pago');
                $attendee->update(['status' => 'paid']);

                AttendeesController::registerPayment($attendee, [
                    'payment_method' => 'stripe',
                    'reference'      => $session['payment_intent'],
                    'status'         => 'paid',
                ]);
            }
        }
    }

    private function handleMembershipPayment(array $session, array $metadata): void
    {
        $member          = Member::find($metadata['member_id']);
        $membershipPrice = MembershipPrice::find($metadata['membership_price_id']);

        if (!$member || !$membershipPrice) {
            Log::warning('Webhook: member o membershipPrice no encontrado');
            return;
        }

        // Actualizar inscription_date
        $member->update([
            'inscription_date' => now(),
            'expiration_date' => now()->copy()->addYear()
        ]);

        // Registrar pago
        Payment::create([
            'user_type'        => 'member',
            'user_id'          => $member->id,
            'event_payed_type' => 'membership',
            'event_payed_id'   => $membershipPrice->membership_id,
            'payment_method'   => 'stripe',
            'amount'           => $metadata['amount'],
            'reference'        => $session['payment_intent'],
            'status'           => 'paid',
            'payment_date'     => now(),
        ]);
    }
}
