@extends('emails.layouts.base')

@section('content')
    <h2>Bienvenido al sistema</h2>

    <p>Hola <strong>{{ $name }}</strong>,</p>

    <p>Tu cuenta ha sido creada correctamente. Aquí están tus datos de acceso:</p>

    <div class="info-box">
        <p><strong>Correo:</strong> {{ $email }}</p>
        <p><strong>Contraseña:</strong> {{ $password }}</p>
    </div>

    <p>Puedes iniciar sesión desde el siguiente enlace:</p>

    <a href="{{ $loginUrl }}" class="button">
        Iniciar sesión
    </a>

    <p>Por seguridad, te recomendamos cambiar tu contraseña después de iniciar sesión.</p>

    <p>Saludos,<br>
        Equipo {{ config('app.name') }}</p>
@endsection
