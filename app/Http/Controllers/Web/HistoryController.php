<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Attendee;
use App\Models\Conference;
use App\Models\Course;
use App\Models\Member;
use App\Models\Webinar;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class HistoryController extends Controller
{
    // ---------------------------------------------
    // Index
    // ---------------------------------------------

    public function index(Request $request)
    {
        $history = $this->addFilters($request);
        // DEBUG
        $user   = Auth::user();
        $member = Member::where('user_id', $user->id)->first();
        Log::info('History debug', [
            'user_id'    => $user?->id,
            'user_email' => $user?->email,
            'member'     => $member?->toArray(),
            'attendees_count' => $member
                ? Attendee::where('person_type', 'member')
                ->where('person_id', $member->id)
                ->count()
                : 'NO MEMBER FOUND',
            'history' => $history?->toArray(),
        ]);
        // FIN DEBUG

        return Inertia::render('History/Index', [
            'history' => $history,
        ]);
    }
    // ---------------------------------------------
    // PRIVATE: Filters / Queries
    // ---------------------------------------------

    private function addFilters(Request $request)
    {
        $perPage    = 20;
        $dateFrom   = $request->get('date_from',   null);
        $dateTo     = $request->get('date_to',     null);
        $didAttend  = $request->get('did_attend',  null);
        $hasDiploma = $request->get('has_diploma', null);

        $member = $this->resolveAuthMember();

        $query = Attendee::where('person_type', 'member')
            ->where('person_id', $member->id)
            ->with([
                'eventPayment',
                'event' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Webinar::class         => ['sessions'],
                        AcademicSession::class => ['sessions'],
                        Conference::class      => ['sessions'],
                        Course::class          => ['sessions'],
                    ]);
                },
                'media' => fn($q) => $q->where('collection_name', 'diplomas'),
            ])
            ->orderBy('created_at', 'desc');

        // ------------------------------------------
        // Filtro: rango de fecha del evento (por sesiones)
        // ------------------------------------------

        if ($dateFrom || $dateTo) {
            $query->whereHasMorph(
                'event',
                [
                    Webinar::class,
                    AcademicSession::class,
                    Conference::class,
                    Course::class,
                ],
                function ($q) use ($dateFrom, $dateTo) {
                    $q->whereHas('sessions', function ($sq) use ($dateFrom, $dateTo) {
                        if ($dateFrom) {
                            $sq->whereDate('date', '>=', $dateFrom);
                        }
                        if ($dateTo) {
                            $sq->whereDate('date', '<=', $dateTo);
                        }
                    });
                }
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
                ? $query->whereHas('media',          fn($q) => $q->where('collection_name', 'diplomas'))
                : $query->whereDoesntHave('media',   fn($q) => $q->where('collection_name', 'diplomas'));
        }

        return $query->paginate($perPage)->withQueryString();
    }

    // ---------------------------------------------
    // PRIVATE: Resolve Member
    // ---------------------------------------------

    /**
     * Obtiene el Member vinculado al usuario autenticado.
     * Aborta con 403 si no tiene miembro vinculado.
     */
    private function resolveAuthMember(): Member
    {
        $member = Member::where('user_id', Auth::id())->first();

        if (!$member) {
            abort(403, 'No tienes un perfil de miembro vinculado.');
        }

        return $member;
    }
}
