<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Attendee;
use App\Models\Conference;
use App\Models\Course;
use App\Models\Member;
use App\Models\Webinar;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AttendeeRegistrationController extends Controller
{
    protected function getEventConfig(): array
    {
        $prices = [
            'member'   => 'stripe_price_member_id',
            'guest'    => 'stripe_price_guest_id',
            'resident' => 'stripe_price_resident_id',
        ];

        $amounts = [
            'member'   => 'member_price',
            'guest'    => 'guest_price',
            'resident' => 'resident_price',
        ];

        $personTypes = [
            ['value' => 'member',   'label' => 'Miembro'],
            ['value' => 'guest',    'label' => 'Invitado (no socio)'],
            ['value' => 'resident', 'label' => 'Residente'],
        ];

        $otherPrices = [
            'surgeon'  => 'stripe_price_surgeon_id',
            'nurse'    => 'stripe_price_nurse_id',
        ];

        $otherAmounts = [
            'surgeon'  => 'surgeon_price',
            'nurse'    => 'nurse_price',
        ];

        $otherPersonTypes = [
            ['value' => 'surgeon', 'label' => 'Cirujano'],
            ['value' => 'nurse',   'label' => 'Enfermero'],
        ];

        $conferencePrices = array_merge($prices, $otherPrices);
        $conferenceAmounts = array_merge($amounts, $otherAmounts);
        $conferencePersonTypes = array_merge($personTypes, $otherPersonTypes);

        return [
            'conference' => [
                'model'         => Conference::class,
                'name_field'    => 'name',
                'prices'        => $conferencePrices,
                'amounts'       => $conferenceAmounts,
                'person_types'  => $conferencePersonTypes
            ],
            'course' => [
                'model'         => Course::class,
                'name_field'    => 'topic',
                'prices'        => $prices,
                'amounts'       => $amounts,
                'person_types'  => $personTypes
            ],
            'webinar' => [
                'model'         => Webinar::class,
                'name_field'    => 'topic',
                'prices'        => $prices,
                'amounts'       => $amounts,
                'person_types'  => $personTypes
            ],
            'academic_session' => [
                'model'         => AcademicSession::class,
                'name_field'    => 'topic',
                'prices'        => $prices,
                'amounts'       => $amounts,
                'person_types'  => $personTypes
            ],
        ];
    }

    protected function resolveEvent(string $eventType, int $eventId)
    {
        $config = $this->getEventConfig();

        abort_unless(isset($config[$eventType]), 404);

        $model = $config[$eventType]['model'];

        return [$model::findOrFail($eventId), $config[$eventType]];
    }

    public function show(string $eventType, int $eventId)
    {
        [$event, $config] = $this->resolveEvent($eventType, $eventId);

        return Inertia::render('Public/EventRegistration', [
            'event'      => $event,
            'event_type' => $eventType,
            'event_name' => $event->{$config['name_field']},
            'person_types' => $config['person_types'],
        ]);
    }

    public function validateMember(Request $request)
    {
        $member = Member::where('cmec_member_id', $request->cmec_member_id)->first();

        if (!$member) {
            return response()->json(['error' => 'Número de socio no encontrado'], 404);
        }

        return response()->json([
            'name'  => $member->name,
            'email' => $member->email,
            'phone' => $member->phone,
        ]);
    }

    public function store(Request $request, string $eventType, int $eventId)
    {
        [$event, $config] = $this->resolveEvent($eventType, $eventId);

        $request->validate([
            'name'        => 'required|string',
            'email'       => 'required|email',
            'phone'       => 'required|string',
            'state'       => 'required|string',
            'city'        => 'required|string',
            'person_type' => 'required|string',
        ]);

        // Determinar member si aplica
        $member = null;
        if ($request->person_type === 'member') {
            $member = Member::where('cmec_member_id', $request->cmec_member_id)->firstOrFail();
        }

        // Resolver price_id y amount
        $personType    = $request->person_type;
        $priceIdField  = $config['prices'][$personType]  ?? $config['prices']['guest'];
        $amountField   = $config['amounts'][$personType] ?? $config['amounts']['guest'];
        $priceId       = $event->$priceIdField;
        $amount        = $event->$amountField;

        // Crear attendee
        $attendee = Attendee::create([
            'event_type'    => $eventType,
            'event_id'      => $event->id,
            'person_type'   => $personType,
            'person_id'     => $member?->id,
            'name'          => $request->name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'state'         => $request->state,
            'city'          => $request->city,
            'specialty'     => $request->specialty,
            'birth_date'    => $request->birth_date,
            'special_needs' => $request->special_needs,
            'status'        => 'pending',
            'price'         => $amount,
        ]);

        return Inertia::location($attendee->checkout($priceId, [
            'success_url' => route('payment.success', [$eventType, $event->id]),
            'cancel_url'  => route('payment.cancel',  [$eventType, $event->id]),
        ])->url);
    }

    public function success(string $eventType, int $eventId)
    {
        [$event, $config] = $this->resolveEvent($eventType, $eventId);

        return Inertia::render('Public/Payment/Success', [
            'event'      => $event,
            'event_type' => $eventType,
            'event_name' => $event->{$config['name_field']},
        ]);
    }

    public function cancel(string $eventType, int $eventId)
    {
        [$event, $config] = $this->resolveEvent($eventType, $eventId);

        return Inertia::render('Public/Payment/Cancelled', [
            'event'      => $event,
            'event_type' => $eventType,
            'event_name' => $event->{$config['name_field']},
        ]);
    }
}