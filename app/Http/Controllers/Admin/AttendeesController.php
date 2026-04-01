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
use App\Models\InvoiceData;
use App\Models\Webinar;
use App\Models\Member;
use App\Models\Payment;
use App\Exports\AttendeesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class AttendeesController extends Controller
{
    public function index(Request $request, $event_type)
    {
        $attendees = $this->addFilters($request, $event_type);
        $events = $this->getEvents($event_type);

        return Inertia::render('Attendees/Index', [
            'attendees' => $attendees,
            'eventType'  => $event_type,
            'eventName' => $events['eventName'],
            'allEvents' => $events['allEvents'],
            'activeEvents' => $events['activeEvents']
        ]);
    }

    private function addFilters(Request $request, $event_type)
    {
        try {
            $search     = $request->input('search', null);
            $event_id   = $request->input('event_id', null);
            $did_attend = $request->input('did_attend', null);
            $perPage    = $request->input('per_page', 10);
            $status     = $request->input('status', '');

            $is_conference = $event_type == Constants::EVENT_CONFERENCE;
            $title         = $is_conference ? 'name' : 'topic';

            $attendees = Attendee::where('event_type', $event_type);

            $attendees->withTrashFilter($status);

            $attendees->with([
                'event' => function (MorphTo $morphTo) use ($event_type, $title) {
                    $morphTo->withTrashed()->morphWith([
                        $event_type => function ($query) use ($title) {
                            $query->select('id', $title);
                        }
                    ]);
                },
                'payments' => function ($query) {
                    $query->withTrashed();
                },
                'invoiceData' => function ($query) {
                    $query->withTrashed();
                }
            ]);

            if (!empty($search)) {
                $attendees->where(function ($query) use ($search, $event_type, $title) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('state', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhereHasMorph('event', [$event_type], function ($query) use ($search, $title) {
                            $query->withTrashed()->where($title, 'like', "%{$search}%");
                        });
                });
            }

            if (!empty($event_id))  $attendees->where('event_id', $event_id);
            if (isset($did_attend)) $attendees->where('did_attend', $did_attend);

            $paginated = $attendees->latest()->paginate($perPage)->withQueryString();

            $memberIds = $paginated->getCollection()
                ->where('person_type', 'member')
                ->whereNotNull('person_id')
                ->pluck('person_id');

            if ($memberIds->isNotEmpty()) {
                $members = Member::select('id', 'cmec_member_id')
                    ->whereIn('id', $memberIds)
                    ->get()
                    ->keyBy('id');

                $paginated->getCollection()->transform(function ($attendee) use ($members) {
                    if ($attendee->person_type === 'member' && $attendee->person_id) {
                        $member = $members->get($attendee->person_id);
                        if ($member) {
                            $attendee->person = $member;
                        }
                    }
                    return $attendee;
                });
            }

            // inyección de pagos de miembros POR EVENTO
            $memberPersonIds = $memberIds;

            if ($memberPersonIds->isNotEmpty()) {
                $memberPayments = Payment::where('user_type', 'member')
                    ->whereIn('user_id', $memberPersonIds)
                    ->withTrashed()
                    ->get()
                    ->groupBy('user_id');

                $paginated->getCollection()->transform(function ($attendee) use ($memberPayments) {
                    if (
                        $attendee->person_type === 'member' &&
                        $attendee->person_id &&
                        $attendee->payments->isEmpty()
                    ) {
                        $payments = $memberPayments->get($attendee->person_id, collect())
                            ->filter(
                                fn($p) =>
                                $p->event_payed_type === $attendee->event_type &&
                                    $p->event_payed_id   === $attendee->event_id
                            )
                            ->values();

                        $attendee->setRelation('payments', $payments);
                    }
                    return $attendee;
                });
            }
            //

            return $paginated;
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
                $events = Conference::select('id', 'name')
                    ->where('subtype', Constants::EVENT_CONFERENCE);
                break;
            case Constants::EVENT_PRECONFERENCE:
                $eventName = 'Pre-congreso';
                $events = Conference::select('id', 'name')
                    ->where('subtype', Constants::EVENT_PRECONFERENCE);
                break;
            case Constants::EVENT_TRANSCONFERENCE:
                $eventName = 'Trans-congreso';
                $events = Conference::select('id', 'name')
                    ->where('subtype', Constants::EVENT_TRANSCONFERENCE);
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

        $activeEvents = $events->addSelect('member_price', 'guest_price', 'resident_price', 'deleted_at');

        if ($this->isConferenceRelated($event_type)) {
            $activeEvents->addSelect('surgeon_price', 'nurse_price');
        }

        $activeEvents->orderBy('created_at', 'desc');
        $allEvents = (clone $activeEvents)->withTrashed();

        return [
            'eventName' => $eventName,
            'allEvents' => $allEvents->get(),
            'activeEvents' => $activeEvents->get()
        ];
    }


    public function store(Request $request)
    {
        try {
            $this->mergeNullableFields($request);

            $rules = $this->getValidationRules($request);
            $data = $request->validate($rules, $this->getValidationMessages());
            $data['person_id'] = $this->getMemberByCmecId($request);


            $exists = Attendee::where('event_id', $request->event_id)
                ->where('event_type', $request->event_type)
                ->where('person_type', $request->person_type)
                ->when($request->person_type === Constants::PERSON_MEMBER, function ($query) use ($data) {
                    $query->where('person_id', $data['person_id']);
                }, function ($query) use ($request) {
                    $query->where('email', $request->email);
                })
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'event_id' => 'Este usuario ya está registrado en este evento.'
                ]);
            }

            $attendee = Attendee::create($data);

            $this->registerPayment($attendee, $data);
            $this->saveInvoiceData($attendee, $data);

            return redirect()
                ->route('attendees.index', ['event' => $data['event_type']])
                ->with('success', 'Asistente creado exitosamente');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
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
            $this->saveInvoiceData($attendee, $data);

            return redirect()
                ->route('attendees.index', $this->getActiveFilters($request, $data['event_type']))
                ->with('success', 'Asistente actualizado exitosamente');
        } catch (ValidationException $e) {
            Log::error($e->getMessage());
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
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
        $attendee->delete();

        return redirect()
            ->route('attendees.index', $this->getActiveFilters($request, $attendee->event_type))
            ->with('success', 'Asistente desactivado exitosamente');
    }

    public function restore(Request $request, $id)
    {
        $attendee = Attendee::withTrashed()->findOrFail($id);
        $attendee->restore();

        return redirect()
            ->route('attendees.index', $this->getActiveFilters($request, $attendee->event_type))
            ->with('success', 'Asistente restaurado exitosamente');
    }

    public function findMemberByCmec(string $cmecId)
    {
        $member = Member::where('cmec_member_id', $cmecId)->first();

        if (!$member) {
            return response()->json(['found' => false], 404);
        }

        $member->load('invoiceData');

        return response()->json([
            'found' => true,
            'member' => [
                'name'           => $member->name . ' ' . $member->last_name,
                'email'          => $member->email,
                'phone'          => $member->phone,
                'state'          => $member->state,
                'city'           => $member->city,
                'hospital'       => $member->hospital,
                'has_invoice_data' => $member->invoiceData !== null,
                // Datos fiscales
                'rfc'              => $member->invoiceData?->rfc ?? '',
                'tax_name'         => $member->invoiceData?->name ?? '',
                'postal_code'      => $member->invoiceData?->postal_code ?? '',
                'tax_person_type'  => $member->invoiceData?->person_type ?? '',
                'tax_regime'       => $member->invoiceData?->tax_regime ?? '',
                'cfdi_use'         => $member->invoiceData?->cfdi_use ?? '',
                'address'          => $member->invoiceData?->address ?? '',
            ],
        ]);
    }

    public function getInvoiceData(string $cmecId)
    {
        $member = Member::where('cmec_member_id', $cmecId)->first();

        if (!$member) {
            return response()->json(['found' => false], 404);
        }

        $invoiceData = InvoiceData::where('billable_type', 'member')
            ->where('billable_id', $member->id)
            ->first();

        if (!$invoiceData) {
            return response()->json(['found' => false], 404);
        }

        return response()->json([
            'found' => true,
            'invoiceData' => [
                'rfc'              => $invoiceData->rfc,
                'name'             => $invoiceData->name,
                'email'            => $invoiceData->email,
                'postal_code'      => $invoiceData->postal_code,
                'person_type'      => $invoiceData->person_type,
                'tax_regime'       => $invoiceData->tax_regime,
                'cfdi_use'         => $invoiceData->cfdi_use,
                'address'          => $invoiceData->address,
            ],
        ]);
    }

    public function uploadDiploma(Request $request, $id)
    {
        $attendee = Attendee::findOrFail($id);

        if ($request->hasFile('diploma')) {
            $attendee->clearMediaCollection('diplomas');
            $attendee->addMediaFromRequest('diploma')
                ->toMediaCollection('diplomas');
        }

        return redirect()
            ->route('attendees.index', $this->getActiveFilters($request, $attendee->event_type))
            ->with('success', 'Diploma subido exitosamente');
    }

    public function changeDidAttend(Request $request, $id)
    {
        try {
            $attendee = Attendee::findOrFail($id);

            $attendee->did_attend = !$attendee->did_attend;
            $attendee->update();

            return redirect()
                ->route('attendees.index', $this->getActiveFilters($request, $attendee->event_type));
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
            'event_type' => 'required|string|in:course,conference,webinar,academic_session,pre_conference,trans_conference',
            'person_id' => 'nullable|integer',
            'person_type' => 'required|string|in:member,resident,guest,surgeon,nurse',

            'payment_method' => 'required|string|in:debit_card,credit_card,cash,transfer,stripe,free',
            'reference' => 'nullable|string',
            'specialty' => 'nullable|string|max:200',

            //Datos de Facturacion
            'has_invoice' => 'required|boolean',
            'rfc' => 'required_if:has_invoice,true|nullable|string|uppercase|min:12|max:13',
            'tax_name' => 'required_if:has_invoice,true|nullable|string|min:3|max:190',
            'address' => 'required_if:has_invoice,true|nullable|string|min:3|max:190',
            'postal_code' => 'required_if:has_invoice,true|nullable|string|digits:5',
            'tax_regime' => 'required_if:has_invoice,true|nullable|string|digits:3',
            'cfdi_use' => 'required_if:has_invoice,true|nullable|string|between:3,4',
            'tax_person_type' => 'required_if:has_invoice,true|nullable|in:fisica,moral',
        ];

        if ($request->input('person_type') === Constants::PERSON_MEMBER) {
            $rules['cmec_member_id'] = 'required|string|max:50';
        }

        $methodsWithReference = [
            Constants::METHOD_DEBIT_CARD,
            Constants::METHOD_CREDIT_CARD,
            Constants::METHOD_TRANSFER,
            Constants::METHOD_STRIPE,
        ];
        $statusWithReference = [
            Constants::STATUS_CANCELLED,
            Constants::STATUS_PAID,
        ];

        if (
            in_array($request->payment_method, $methodsWithReference) &&
            in_array($request->status, $statusWithReference)
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
            'status.in' => 'El estado de pago debe ser "pagado", "pendiente" o "cancelado".',
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
            '*.required_if' => 'Este campo es obligatorio',
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

    public static function registerPayment($attendee, $data)
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

            Log::info('Pago registrado correctamente');
            return true;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    private function saveInvoiceData(Attendee $attendee, array $data)
    {
        try {
            $isMember = $attendee->person_type == Constants::PERSON_MEMBER && !empty($attendee->person_id);

            $attributes = [
                'billable_type' => $isMember ? Constants::PERSON_MEMBER : 'attendee',
                'billable_id' => $isMember ? $attendee->person_id : $attendee->id,
            ];

            $values = [

                'rfc' => $data['rfc'] ?? null,
                'name' => $data['tax_name'] ?? null,
                'email' => $attendee->email,
                'postal_code' => $data['postal_code'] ?? null,
                'person_type' => $data['tax_person_type'] ?? null,
                'tax_regime' => $data['tax_regime'] ?? null,
                'cfdi_use' => $data['cfdi_use'] ?? null,
                'address' => $data['address'] ?? null,
            ];

            InvoiceData::updateOrCreate($attributes, $values);

            return true;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    /**
     * extraemos los filtros activos del request y los combinamos con el event_type
     * agregar aqui filtros nuevos para que se propague en todos los redirects
     */
    private function getActiveFilters(Request $request, string $event_type): array
    {
        return array_filter([
            'event'      => $event_type,
            'event_id'   => $request->get('_filters_event_id'),
            'did_attend' => $request->get('_filters_did_attend'),
            // 'search'  => $request->get('_filters_search'),   // ejemplo
        ], fn($v) => $v !== null && $v !== '');
    }

    private function isConferenceRelated(string $event_type)
    {
        $events = [
            Constants::EVENT_CONFERENCE,
            Constants::EVENT_PRECONFERENCE,
            Constants::EVENT_TRANSCONFERENCE,
        ];

        return in_array($event_type, $events);
    }

    public function exportExcel(Request $request, $event_type)
    {
        $attendees = $this->getAttendeesForExport($request, $event_type);
        $filename  = "asistentes_{$event_type}_" . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new AttendeesExport($attendees, $event_type), $filename);
    }

    public function exportPdf(Request $request, $event_type)
    {
        $is_conference = $this->isConferenceRelated($event_type);
        $attendees     = $this->getAttendeesForExport($request, $event_type);
        $events        = $this->getEvents($event_type);

        $memberIds = $attendees->where('person_type', 'member')->whereNotNull('person_id')->pluck('person_id');
        $members   = Member::select('id', 'cmec_member_id')->whereIn('id', $memberIds)->get()->keyBy('id');

        $pdf = Pdf::loadView('exports.attendees', [
            'attendees'     => $attendees,
            'eventName'     => $events['eventName'],
            'is_conference' => $is_conference,
            'members'       => $members,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("asistentes_{$event_type}_" . now()->format('Y-m-d') . '.pdf');
    }

    private function getAttendeesForExport(Request $request, string $event_type)
    {
        $search     = $request->input('search', null);
        $event_id   = $request->input('event_id', null);
        $did_attend = $request->input('did_attend', null);
        $status     = $request->input('status', '');

        $is_conference = $this->isConferenceRelated($event_type);
        $title         = $is_conference ? 'name' : 'topic';

        $query = Attendee::where('event_type', $event_type)
            ->withTrashFilter($status);

        if (!empty($search)) {
            $query->where(function ($q) use ($search, $event_type, $title) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('state', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhereHasMorph('event', [$event_type], function ($q) use ($search, $title) {
                        $q->withTrashed()->where($title, 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($event_id))  $query->where('event_id', $event_id);
        if (isset($did_attend)) $query->where('did_attend', $did_attend);

        return $query->latest()->get();
    }
}
