<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - {{ $conference->name }}</title>
</head>
<body>

<h1>{{ $conference->name }}</h1>

<form method="POST" action="{{ route('public.conference.store', $conference) }}">
    @csrf

    {{-- Tipo de asistente --}}
    <div>
        <label>Tipo de asistente</label>
        <select name="person_type" id="person_type" onchange="toggleMemberField(this.value)">
            <option value="guest">Invitado</option>
            <option value="member">Miembro</option>
            <option value="resident">Residente</option>
            <option value="surgeon">Cirujano</option>
            <option value="nurse">Enfermero</option>
        </select>
    </div>

    {{-- Campo exclusivo para miembros --}}
    <div id="member_field" style="display:none">
        <label>Número de socio (CMEC)</label>
        <input type="text" name="cmec_member_id" id="cmec_member_id">
        <button type="button" onclick="validateMember()">Verificar</button>
        <span id="member_status"></span>
    </div>

    {{-- Datos personales --}}
    <div>
        <label>Nombre completo</label>
        <input type="text" name="name" id="name" value="{{ old('name') }}" required>
    </div>

    <div>
        <label>Email</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" required>
    </div>

    <div>
        <label>Teléfono</label>
        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required>
    </div>

    <div>
        <label>Estado</label>
        <input type="text" name="state" id="state" value="{{ old('state') }}" required>
    </div>

    <div>
        <label>Ciudad</label>
        <input type="text" name="city" id="city" value="{{ old('city') }}" required>
    </div>

    <div>
        <label>Especialidad</label>
        <input type="text" name="specialty" value="{{ old('specialty') }}">
    </div>

    <div>
        <label>Fecha de nacimiento</label>
        <input type="date" name="birth_date" value="{{ old('birth_date') }}">
    </div>

    <div>
        <label>Necesidades especiales</label>
        <input type="text" name="special_needs" value="{{ old('special_needs') }}">
    </div>

    @if($errors->any())
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <button type="submit">Continuar al pago</button>

</form>

<script>
function toggleMemberField(value) {
    const memberField = document.getElementById('member_field');
    memberField.style.display = value === 'member' ? 'block' : 'none';

    // Limpiar campos si cambia de tipo
    if (value !== 'member') {
        document.getElementById('name').readOnly = false;
        document.getElementById('email').readOnly = false;
        document.getElementById('phone').readOnly = false;
    }
}

function validateMember() {
    const cmecId = document.getElementById('cmec_member_id').value;
    const status = document.getElementById('member_status');

    if (!cmecId) return;

    fetch('{{ route('public.conference.validate-member') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ cmec_member_id: cmecId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            status.textContent = 'Número de socio no encontrado';
            return;
        }

        // Autocompletar campos con datos del miembro
        document.getElementById('name').value = data.name;
        document.getElementById('email').value = data.email;
        document.getElementById('phone').value = data.phone;
        document.getElementById('name').readOnly = true;
        document.getElementById('email').readOnly = true;
        document.getElementById('phone').readOnly = true;
        status.textContent = '✓ Miembro verificado';
    })
    .catch(() => {
        status.textContent = 'Error al verificar';
    });
}
</script>

</body>
</html>