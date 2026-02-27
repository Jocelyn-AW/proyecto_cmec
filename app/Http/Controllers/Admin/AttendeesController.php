<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Attendee;
use App\Http\Helpers\Constants;
use Inertia\Inertia;
use App\Models\Course;
use App\Models\Conference;
use App\Models\Webinar;
use App\Models\Member;
use Illuminate\Validation\ValidationException;

class AttendeesController extends Controller
{
    public function index(Request $request, $event_type)
    {
        $attendees = $this->addFilters($request, $event_type);
        $events = $this->getEvents($event_type);

        if ($event_type === Constants::EVENT_WEBINAR) {
            return Inertia::render('WebinarsAttendees/Index', [
            'attendees' => $attendees,
            'eventName' => $events['eventName'],
            'events' => $events['events']
        ]);
        }

        return Inertia::render('CoursesAttendees/Index', [
            'attendees' => $attendees,
            'eventName' => $events['eventName'],
            'events' => $events['events']
        ]);
    }

    private function addFilters(Request $request, $event_type)
    {
        $search = $request->input('search', null);
        $perPage = $request->input('per_page', 10);

        $title = $event_type == Constants::EVENT_CONFERENCE ? 'name' : 'topic';

        $attendees = Attendee::with(['event' => function ($query) use ($title) {
            $query->select('id', $title, 'member_price', 'guest_price', 'resident_price');
        }]);

        if (!empty($search)) {
            $attendees->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('state', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
                ->orWhereMorphRelation('event', $event_type, $title, 'like', "%{$search}%");
        }

        $attendees->where('event_type', $event_type);

        return $attendees->paginate($perPage)->withQueryString();
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
            default:
                $eventName = 'Evento';
                $events = [];
        }

        $events->addSelect('member_price', 'guest_price', 'resident_price');
        
        return [
            'eventName' => $eventName,
            'events' => $events->get()
        ];
    }


    public function store(Request $request)
    {
        try {
            $this->mergeNullableFields($request);

            $rules = $this->getValidationRules($request);
            $data = $request->validate($rules, $this->getValidationMessages());
            $data['person_id'] = $this->getMemberByCmecId($request);

            Attendee::create($data);

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
            $this->mergeNullableFields($request);

            $rules = $this->getValidationRules($request);
            $data = $request->validate($rules, $this->getValidationMessages());
            $data['person_id'] = $this->getMemberByCmecId($request);

            $attendee = Attendee::findOrFail($id);
            $attendee->update($data);

            return redirect()
                ->route('attendees.index', ['event' => $data['event_type']])
                ->with('success', 'Asistente actualizado exitosamente');
        } catch (ValidationException $e) {

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {

            return redirect()
                ->back()
                ->with('error', 'Ocurrió un error al actualizar el asistente. Por favor, inténtalo de nuevo.')
                ->withInput();
        }
    }

    public function delete($id)
    {
        $attendee = Attendee::findOrFail($id);
        $attendee->delete();

        return redirect()
            ->route('attendees.index', ['event' => $attendee->event_type])
            ->with('success', 'Asistente eliminado exitosamente');
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

            'event_id' => 'required|integer',
            'event_type' => 'required|string|in:course,conference,webinar',
            'person_id' => 'nullable|integer',
            'person_type' => 'required|string|in:member,resident,guest',
        ];

        if ($request->input('person_type') === Constants::PERSON_MEMBER) {
            $rules['cmec_member_id'] = 'required|string|max:50';
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
}
