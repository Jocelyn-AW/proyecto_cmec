<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        h2 { text-align: center; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #2d6a4f; color: white; padding: 6px; text-align: left; }
        td { padding: 5px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Lista de Asistentes — {{ $eventName }}</h2>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Estado</th>
                <th>Ciudad</th>
                <th>Tipo</th>
                <th>Monto</th>
                <th>Status</th>
                <th>Asistió</th>
                @if($is_conference)
                    <th>Especialidad</th>
                    <th>Nec. Especiales</th>
                    <th>ID CMEC</th>
                @else
                    <th>Folio</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($attendees as $attendee)
            <tr>
                <td>{{ $attendee->name }}</td>
                <td>{{ $attendee->email }}</td>
                <td>{{ $attendee->phone }}</td>
                <td>{{ $attendee->state }}</td>
                <td>{{ $attendee->city }}</td>

                @if ($is_conference)
                    @if ($attendee->person_type == 'member')
                        <td>Miembro CMEC</td>
                    @elseif ($attendee->person_type == 'guest')
                        <td>Invitado (no socio)</td>
                    @elseif ($attendee->person_type == 'resident')
                        <td>Residente / Medico General</td>
                    @elseif ($attendee->person_type == 'surgeon')
                        <td>Residente de cirugía</td>
                    @elseif ($attendee->person_type == 'nurse')
                        <td>Enfermero / Estudiante</td>
                    @endif
                @else
                    @if ($attendee->person_type == 'member')
                        <td>Miembro CMEC</td>
                    @elseif ($attendee->person_type == 'guest')
                        <td>Invitado (no socio)</td>
                    @elseif ($attendee->person_type == 'resident')
                        <td>Residente</td>
                    @endif
                @endif

                <td>${{ number_format($attendee->price, 2) }}</td>

                @if ($attendee->status == 'paid')
                    <td>Pagado</td>
                @elseif ($attendee->status == 'pending')
                    <td>Pendiente</td>
                @else
                    <td>Cancelado</td>
                @endif

                <td>{{ $attendee->did_attend ? 'Sí' : 'No' }}</td>
                @if($is_conference)
                    <td>{{ $attendee->specialty }}</td>
                    <td>{{ $attendee->special_needs }}</td>
                    <td>{{ $members->get($attendee->person_id)?->cmec_member_id }}</td>
                @else
                    <td>{{ $attendee->folio }}</td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>