<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Attendee;
use App\Http\Helpers\Constants;
use App\Models\AcademicSession;
use Inertia\Inertia;
use App\Models\Course;
use App\Models\Conference;
use App\Models\Webinar;
use App\Models\Member;
use App\Models\Payment;
use Exception;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AttendeesController extends Controller
{
    public function index(Request $request, $event_type)
    {
        $attendees = $this->addFilters($request, $event_type);
        $events = $this->getEvents($event_type);

        $view = 'CoursesAttendees/Index';
        if ($event_type === Constants::EVENT_WEBINAR) {
            $view = 'WebinarsAttendees/Index';
        } else if ($event_type === Constants::EVENT_ACADEMIC_SESSION) {
            $view = 'AcademicSessionsAttendees/Index';
        } else if ($event_type === Constants::EVENT_CONFERENCE) {
            $view = 'ConferencesAttendees/Index';
        }

        return Inertia::render($view, [
            'attendees' => $attendees,
            'eventName' => $events['eventName'],
            'allEvents' => $events['allEvents'],
            'activeEvents' => $events['activeEvents']
        ]);
    }

    private function addFilters(Request $request, $event_type)
    {
        try {
            $search = $request->input('search', null);
            $event_id = $request->input('event_id', null);
            $did_attend = $request->input('did_attend', null);
            $perPage = $request->input('per_page', 10);

            $is_conference = $event_type == Constants::EVENT_CONFERENCE;
            $title = $is_conference ? 'name' : 'topic';

            $attendees = Attendee::where('event_type', $event_type);

            $attendees->with([
                'event' => function (MorphTo $morphTo) use ($event_type, $title) {
                    $morphTo->morphWith([
                        $event_type => function ($query) use ($title) {
                            $query->select('id', $title);
                        }
                    ]);
                }
            ]);

            $attendees->with('payments');

            if (!empty($search)) {
                $attendees->where(function ($query) use ($search, $event_type, $title) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('state', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhereMorphRelation('event', $event_type, $title, 'like', "%{$search}%");
                });
            }

            if (!empty($event_id)) {
                $attendees->where('event_id', $event_id);
            }

            if (isset($did_attend)) $attendees->where('did_attend', $did_attend);

            return $attendees->latest()->paginate($perPage)->withQueryString();
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    private function getEvents($event_type)
    {
        switch ($event_type) {
            case Constants::EVENT_COURSE:
                $eventName = 'Curso';
                $events = Course::select('id', 'topic');
                break;
            case Constants::EVENT_CONFERENCE:
                $eventName = 'Congreso';
                $events = Conference::select('id', 'name');
                break;
            case Constants::EVENT_WEBINAR:
                $eventName = 'Webinar';
                $events = Webinar::select('id', 'topic');
                break;
            case Constants::EVENT_ACADEMIC_SESSION:
                $eventName = 'Sesion Academica';
                $events = AcademicSession::select('id', 'topic');
                break;
            default:
                $eventName = 'Evento';
                $events = collect();
        }

        $allEvents = $events->addSelect('member_price', 'guest_price', 'resident_price', 'is_active');
        if ($event_type == Constants::EVENT_CONFERENCE) {
            $allEvents->addSelect('surgeon_price', 'nurse_price');
        }
        $allEvents->orderBy('created_at', 'desc');

        $active = (clone $allEvents)->where('is_active', '1');

        return [
            'eventName' => $eventName,
            'allEvents' => $allEvents->get(),
            'activeEvents' => $active->get()
        ];
    }


    public function store(Request $request)
    {
        try {
            $this->mergeNullableFields($request);

            $rules = $this->getValidationRules($request);
            $data = $request->validate($rules, $this->getValidationMessages());
            $data['person_id'] = $this->getMemberByCmecId($request);

            $attendee = Attendee::create($data);
            $payment = $this->registerPayment($attendee, $data);

            return redirect()
                ->route('attendees.index', ['event' => $data['event_type']])
                ->with('success', 'Asistente creado exitosamente');
        } catch (ValidationException $e) {

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {

            return redirect()
                ->back()
                ->with('error', 'Ocurrió un error al crear el asistente. Por favor, inténtalo de nuevo.')
                ->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->mergeNullableFieldsForUpdate($request);

            $rules = $this->getValidationRules($request);
            $data = $request->validate($rules, $this->getValidationMessages());
            $data['person_id'] = $this->getMemberByCmecId($request);

            $attendee = Attendee::findOrFail($id);
            $attendee->update($data);
            $this->registerPayment($attendee, $data);

            return redirect()->route('attendees.index', array_filter([
                'event'      => $data['event_type'],
                'event_id'   => $request->get('_filters_event_id'),
                'did_attend' => $request->get('_filters_did_attend'),
            ], fn($v) => $v !== null && $v !== ''))->with('success', 'Asistente actualizado exitosamente');
        } catch (ValidationException $e) {
            Log::error($e->getMessage());
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Ocurrió un error al actualizar el asistente. Por favor, inténtalo de nuevo.')
                ->withInput();
        }
    }

    public function delete(Request $request, $id)
    {
        $attendee = Attendee::findOrFail($id);
        if ($attendee->payments()->count() > 0) {
            $attendee->payments()->delete();
        }
        $attendee->clearMediaCollection('diplomas');

        $attendee->delete();

        return redirect()->route('attendees.index', array_filter([
            'event'      => $attendee->event_type,
            'event_id'   => $request->get('event_id'),
            'did_attend' => $request->get('did_attend'),
        ], fn($v) => $v !== null && $v !== ''))->with('success', 'Asistente eliminado exitosamente');
    }

    public function uploadDiploma(Request $request, $id)
    {
        $attendee = Attendee::findOrFail($id);

        if ($request->hasFile('diploma')) {
            $attendee->addMediaFromRequest('diploma')
                ->toMediaCollection('diplomas');
        }

        return redirect()
            ->route('attendees.index', ['event' => $attendee->event_type])
            ->with('success', 'Diploma subido exitosamente');
    }

    public function changeDidAttend(Request $request, $id)
    {
        try {
            $attendee = Attendee::findOrFail($id);

            $attendee->did_attend = !$attendee->did_attend;
            $attendee->update();

            return redirect()->route('attendees.index', array_filter([
                'event'      => $attendee->event_type,
                'event_id'   => $request->get('event_id'),
                'did_attend' => $request->get('did_attend'),
            ], fn($v) => $v !== null && $v !== ''));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Ocurrió un error al cambiar la asistencia. Por favor, inténtalo de nuevo.')
                ->withInput();
        }
    }

    private function getValidationRules(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:20',
            'state' => 'required|string|max:50',
            'city' => 'required|string|max:50',
            'status' => 'required|in:paid,pending,cancelled',
            'price' => 'nullable|numeric|min:0',
            'did_attend' => 'boolean',
            'folio' => 'required|string|max:5',

            'birth_date' => 'nullable|date',
            'special_needs' => 'nullable|string|max:250',

            'event_id' => 'required|integer',
            'event_type' => 'required|string|in:course,conference,webinar,academic_session',
            'person_id' => 'nullable|integer',
            'person_type' => 'required|string|in:member,resident,guest,surgeon,nurse',

            'payment_method' => 'required|string|in:debit_card,credit_card,cash,transfer,stripe',
            'reference' => 'nullable|string',
            'specialty' => 'nullable|string|max:200'
        ];

        if ($request->input('person_type') === Constants::PERSON_MEMBER) {
            $rules['cmec_member_id'] = 'required|string|max:50';
        }

        if (
            $request->input('status', 'pending') != Constants::STATUS_PENDING &&
            $request->input('payment_method', 'cash') != Constants::METHOD_CASH
        ) {
            $rules['reference'] = 'required|string|max:100';
        }

        return $rules;
    }

    private function getValidationMessages()
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
            'status.required' => 'El estado de pago es obligatorio.',
            'status.in' => 'El estado de pago debe ser "paid", "pending" o "cancelled".',
            'price.numeric' => 'El precio debe ser un número.',
            'price.min' => 'El precio no puede ser negativo.',
            'folio.required' => 'El folio es obligatorio.',
            'person_type.required' => 'Seleccione un tipo válido',
            'status.required' => 'Seleccione un estatus de pago válido',
            'state.required' => 'El estado es obligatorio',
            'city.required' => 'La ciudad es obligatoria',
            'phone.required' => 'El teléfono es obligatorio',
            'cmec_member_id.required' => 'El ID de miembro CMEC es obligatorio para participantes que son miembros.',
            'phone.min' => 'El teléfono debe tener minimo :min digitos',
            'phone.max' => 'El teléfono debe tener maximo :max digitos',
            '*.required' => 'Este campo es obligatorio',
        ];
    }

    private function mergeNullableFields(Request $request)
    {
        $request->mergeIfMissing([
            'phone' => null,
            'state' => null,
            'city' => null,
            'price' => null,
            'status' => 'pending',
            'person_id' => null,
            'did_attend' => false,
        ]);
    }

    private function mergeNullableFieldsForUpdate(Request $request)
    {
        $request->mergeIfMissing([
            'phone' => null,
            'state' => null,
            'city' => null,
            'price' => null,
            'status' => 'pending',
            'person_id' => null,
        ]);
    }

    private function getMemberByCmecId(Request $request)
    {
        if (
            $request->input('person_type') === Constants::PERSON_MEMBER &&
            $request->filled('cmec_member_id')
        ) {
            $cmecMemberId = $request->input('cmec_member_id');
            $member = Member::where('cmec_member_id', $cmecMemberId)->first();

            if (!$member) {
                throw ValidationException::withMessages([
                    'cmec_member_id' => 'No se encontró ningún miembro con el ID CMEC proporcionado.'
                ]);
            }
            return $member->id;
        }
        return null;
    }

    private function registerPayment($attendee, $data)
    {
        try {
            if ($attendee->payments()->count() > 0 || $data['status'] == 'pending') return;

            $paymentMethod = $data['payment_method'];
            $reference     = $data['reference'] ?? '';
            $status        = $data['status'] ?? 'pending';

            $isMember = $attendee->person_type == Constants::PERSON_MEMBER && !empty($attendee->person_id);

            Payment::create([
                'user_type' => $isMember ? 'member' : 'attendee',
                'user_id' => $isMember ? $attendee->person_id : $attendee->id,
                'event_payed_type' => $attendee->event_type,
                'event_payed_id' => $attendee->event_id ?? null,

                'payer_name' => empty($attendee->person_id) ? $attendee->name : null,
                'payer_email' => empty($attendee->person_id) ? $attendee->email : null,
                'payer_phone' => empty($attendee->person_id) ? $attendee->phone : null,
                'payment_method' => $paymentMethod,
                'amount' => $attendee->price ?? 0,
                'reference' => $reference,
                'status' => $status,
                'payment_date' => now()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }
}
