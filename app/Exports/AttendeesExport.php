<?php
namespace App\Exports;

use App\Http\Helpers\Constants;
use App\Models\Member;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendeesExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(
        private $attendees,
        private string $event_type
    ) {}

    public function collection()
    {
        $is_conference = $this->isConferenceRelated($this->event_type);

        $memberIds = $this->attendees
            ->where('person_type', 'member')
            ->whereNotNull('person_id')
            ->pluck('person_id');

        $members = Member::select('id', 'cmec_member_id')
            ->whereIn('id', $memberIds)
            ->get()
            ->keyBy('id');

        return $this->attendees->map(function ($attendee) use ($is_conference, $members) {
            $cmecId = null;
            if ($attendee->person_type === 'member' && $attendee->person_id) {
                $cmecId = $members->get($attendee->person_id)?->cmec_member_id;
            }

            $row = [
                'Nombre'            => $attendee->name,
                'Email'             => $attendee->email,
                'Teléfono'          => $attendee->phone,
                'Estado'            => $attendee->state,
                'Ciudad'            => $attendee->city,
                'Tipo'              => $this->getType($attendee->person_type, $is_conference),
                'Monto'             => $attendee->price,
                'Estatus de Pago'   => $this->getPaymentStatus($attendee->status),
                'Asistió'           => $attendee->did_attend ? 'Sí' : 'No',
            ];

            if ($is_conference) {
                $row['Especialidad']        = $attendee->specialty;
                $row['Necesidades especiales'] = $attendee->special_needs;
                $row['ID CMEC']             = $cmecId;
            } else {
                $row['Folio'] = $attendee->folio;
            }

            return $row;
        });
    }

    public function headings(): array
    {
        $is_conference = $this->isConferenceRelated($this->event_type);

        $base = ['Nombre', 'Email', 'Teléfono', 'Estado', 'Ciudad', 'Tipo', 'Monto', 'Estatus de Pago', 'Asistió'];

        if ($is_conference) {
            return array_merge($base, ['Especialidad', 'Necesidades especiales', 'ID CMEC']);
        }

        return array_merge($base, ['Folio']);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function isConferenceRelated(string $event_type): bool
    {
        return in_array($event_type, [
            Constants::EVENT_CONFERENCE,
            Constants::EVENT_PRECONFERENCE,
            Constants::EVENT_TRANSCONFERENCE,
        ]);
    }

    private function getPaymentStatus(string $status) : string
    {
        $labels = [
            'paid' => 'Pagado',
            'pending' => 'Pendiente',
            'cancelled' => 'Cancelado',
        ];

        return $labels[$status];
    }

    private function getType(string $person_type, bool $is_conference) : string
    {
        $conferenceLabels = [
            'member' => 'Miembro CMEC',
            'guest' => 'Invitado (no socio)',
            'resident' => 'Residente / Medico General',
            'surgeon' => 'Residente de cirugía',
            'nurse' => 'Enfermero / Estudiante',
        ];

        $commonLabels =  [
            'member' => 'Miembro CMEC',
            'guest' => 'Invitado (no socio)',
            'resident' => 'Residente',
        ];

        if ($is_conference) {
            return $conferenceLabels[$person_type];
        } else {
            return $commonLabels[$person_type];
        }
    } 


}