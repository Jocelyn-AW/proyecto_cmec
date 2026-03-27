<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Attendee;
use App\Models\Conference;
use App\Models\Course;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Webinar;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $history = $this->buildQuery($request);

        /* Log::info('History debug', [
            'history' => $history?->toArray(),
        ]); */

        return Inertia::render('History/Index', [
            'history' => $history,
        ]);
    }

    // --------------------------------------------------
    // PRIAVTE: Query principal
    // --------------------------------------------------

    private function buildQuery(Request $request)
    {
        $member     = $this->resolveAuthMember();
        $perPage    = 10;
        $dateFrom   = $request->get('date_from');
        $dateTo     = $request->get('date_to');
        $didAttend  = $request->get('did_attend');
        $hasDiploma = $request->get('has_diploma');

        $query = Attendee::where('person_type', 'member')
            ->where('person_id', $member->id)
            ->with([
                'media' => fn($q) => $q->where('collection_name', 'diplomas'),
                'event' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Webinar::class         => ['sessions'],
                        AcademicSession::class => ['sessions'],
                        Conference::class      => ['sessions'],
                        Course::class          => ['sessions'],
                    ]);
                },
            ])
            ->orderBy('created_at', 'desc');

        // ------------------------------------------
        // Filtro: rango de fecha del evento (por sesiones)
        // ------------------------------------------

        if ($dateFrom || $dateTo) {
            $query->whereHasMorph(
                'event',
                [Webinar::class, AcademicSession::class, Conference::class, Course::class],
                fn($q) => $q->whereHas('sessions', function ($sq) use ($dateFrom, $dateTo) {
                    if ($dateFrom) $sq->whereDate('date', '>=', $dateFrom);
                    if ($dateTo)   $sq->whereDate('date', '<=', $dateTo);
                })
            );
        }

        // ------------------------------------------
        // Filtro: asistencia
        // ------------------------------------------

        if (!is_null($didAttend)) {
            $query->where('did_attend', $didAttend === 'yes');
        }

        // ------------------------------------------
        // Filtro: tiene diploma
        // ------------------------------------------

        if (!is_null($hasDiploma)) {
            $hasDiploma === 'yes'
                ? $query->whereHas('media',        fn($q) => $q->where('collection_name', 'diplomas'))
                : $query->whereDoesntHave('media', fn($q) => $q->where('collection_name', 'diplomas'));
        }

        $paginated = $query->paginate($perPage)->withQueryString();

        $payments = $this->loadPaymentsForAttendees($paginated->getCollection(), $member->id);

        return $paginated->through(
            fn($attendee) => $this->formatAttendee($attendee, $payments)
        );
    }

    // ------------------------------------------------------------------
    // PRIVATE: Cargar pagos del miembro para los eventos
    // ------------------------------------------------------------------
    private function loadPaymentsForAttendees(Collection $attendees, int $memberId): Collection
    {
        if ($attendees->isEmpty()) {
            return collect();
        }

        // pares [type, id] de los attendees
        $pairs = $attendees->map(fn($a) => [
            'type' => $a->event_type,
            'id'   => $a->event_id,
        ])->unique(fn($p) => $p['type'] . ':' . $p['id']);

        $query = Payment::where('user_type', 'member')
            ->where('user_id', $memberId)
            ->where(function ($q) use ($pairs) {
                foreach ($pairs as $pair) {
                    $q->orWhere(function ($q2) use ($pair) {
                        $q2->where('event_payed_type', $pair['type'])
                            ->where('event_payed_id',   $pair['id']);
                    });
                }
            })
            ->latest('id')
            ->get();

        // indexar como "event_type:event_id" -> Payment
        return $query->keyBy(fn($p) => $p->event_payed_type . ':' . $p->event_payed_id);
    }

    // ------------------------------------------------------------------
    // Convertir el attendee en el array que necesita el frontend
    // ------------------------------------------------------------------
    private function formatAttendee(Attendee $attendee, Collection $payments): array
    {
        $event     = $attendee->event;
        $eventType = $attendee->event_type;
        $key       = $eventType . ':' . $attendee->event_id;
        $payment   = $payments->get($key);

        $firstDate = $event?->sessions
            ?->sortBy('date')
            ->first()
            ?->date;

        return [
            'id'          => $attendee->id,
            'folio'       => $attendee->folio,
            'event_type'  => $eventType,
            'event_name'  => $this->resolveEventName($event, $eventType),
            'cover_url'   => $event?->cover_url,
            'first_date'  => $firstDate,
            'status'      => $attendee->status,
            'did_attend'  => $attendee->did_attend,
            'diploma_url' => $attendee->diploma_url,
            'payment' => [
                // si hay pago registrado, si no, el price del attendee
                'amount'         => $payment?->amount ?? $attendee->price,
                'status'         => $payment?->status ?? $attendee->status,
                'payment_method' => $payment?->payment_method,
                'payment_date'   => $payment?->payment_date?->format('Y-m-d'),
            ],
        ];
    }

    private function resolveEventName($event, string $eventType): ?string
    {
        if (!$event) return null;

        return in_array($eventType, ['conference', 'pre_conference', 'trans_conference'])
            ? $event->name
            : $event->topic ?? null;
    }

    private function resolveAuthMember(): Member
    {
        $member = Member::where('user_id', Auth::id())->first();

        if (!$member) {
            abort(403, 'No tienes un perfil de miembro vinculado.');
        }

        return $member;
    }
}
