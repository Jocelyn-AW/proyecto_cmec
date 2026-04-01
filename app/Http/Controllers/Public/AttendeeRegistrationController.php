<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Attendee;
use App\Models\Conference;
use App\Models\Course;
use App\Models\Member;
use App\Models\Webinar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
            'amounts' => $config['amounts'],
        ]);
    }

    public function validateMember(Request $request)
    {
        $member = Member::where('cmec_member_id', $request->cmec_member_id)->first();

        if (!$member) {
            return response()->json(['error' => 'Número de socio no encontrado'], 404);
        }

        return response()->json([
            'name'  => $member->name . ' ' . $member->last_name,
            'email' => $member->email,
            'phone' => $member->phone,
            'state' => $member->state,
            'city'  => $member->city,
        ]);
    }

    public function store(Request $request, string $eventType, int $eventId)
    {
        [$event, $config] = $this->resolveEvent($eventType, $eventId);

        $rules = [
            'name'           => 'required|string',
            'email'          => 'required|email',
            'phone'          => 'required|string',
            'state'          => 'required|string',
            'city'           => 'required|string',
            'person_type'    => 'required|string',
            'cmec_member_id' => 'required_if:person_type,member|nullable|string',
        ];

        $messages = [
            'required'      => 'Este campo es requerido',
            'required_if'   => 'Este campo es requerido',
            'email'         => 'Ingrese un email válido',
        ];

        $request->validate($rules, $messages);

        $member  = null;
        $priceId = null;

        $attendeeCheck = $this->checkIfAttendeeExists($eventType, $eventId, $request);

        if ($attendeeCheck instanceof RedirectResponse || $attendeeCheck instanceof Response) {
            return $attendeeCheck;
        }

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

    private function checkIfAttendeeExists(string $eventType, int $eventId, Request $request)
    {
        [$event, $config] = $this->resolveEvent($eventType, $eventId);

        if ($request->person_type === 'member') {
            $member = Member::where('cmec_member_id', $request->cmec_member_id)->firstOrFail();

            if (!$member) {
                return back()->withErrors([
                    'cmec_member_id' => 'Número de socio no encontrado'
                ]);
            }

            $record = Attendee::where('event_type', $eventType)
                ->where('event_id', $eventId)
                ->where('person_id', $member->id)
                ->first();

            if ($record) {
                if ($record->status === 'paid') {
                    return back()->withErrors([
                        'cmec_member_id' => 'Este miembro ya está registrado en este evento.'
                    ]);
                }

                $priceIdField = $config['prices']['member'];
                $priceId      = $event->$priceIdField;

                return Inertia::location($record->checkout($priceId, [
                    'success_url' => route('payment.success', [$eventType, $event->id]),
                    'cancel_url'  => route('payment.cancel',  [$eventType, $event->id]),
                ])->url);
            } else {
                return null;
            }

        } else {

            $record = Attendee::where('event_type', $eventType)
                ->where('event_id', $eventId)
                ->whereRaw('LOWER(email) = ?', [strtolower(trim($request->email))])
                ->where(function($query) use ($request) {
                    $query->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($request->name))])
                            ->orWhere('phone', $request->phone);
                })
                ->first();


            if ($record) {

                if ($record->status === 'paid') {
                    return back()->withErrors([
                        'name' => 'Este asistente ya está registrado en este evento.'
                    ]);
                }
            
                $priceIdField = $config['prices'][$record->person_type] ?? $config['prices']['guest'];
                $priceId      = $event->$priceIdField;

                return Inertia::location($record->checkout($priceId, [
                    'success_url' => route('payment.success', [$eventType, $event->id]),
                    'cancel_url'  => route('payment.cancel',  [$eventType, $event->id]),
                ])->url);
            } else {
                return null;
            }
        }
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