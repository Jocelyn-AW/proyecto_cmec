@extends('emails.layouts.base')

@section('content')
    <h2>Restablecer contraseña</h2>

    <p>
        Recibimos una solicitud para restablecer tu contraseña.
    </p>

    <p>
        Haz clic en el botón de abajo para crear una nueva contraseña:
    </p>

    <div style="text-align:center;">
        <a href="{{ $resetUrl }}" class="button">
            Restablecer contraseña
        </a>
    </div>

    <div class="info-box">
        <p>
            Este enlace expirará en 60 minutos por seguridad.
        </p>
    </div>

    <p>
        Si no solicitaste este cambio, puedes ignorar este correo.
    </p>

    <p>
        — {{ config('app.name') }}
    </p>
@endsection
