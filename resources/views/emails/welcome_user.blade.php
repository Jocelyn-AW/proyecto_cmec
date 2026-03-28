@extends('emails.layouts.base')

@section('content')
    <h2>Bienvenido al sistema</h2>

    <p>Hola <strong>{{ $name }}</strong>,</p>

    <p>Tu cuenta ha sido creada correctamente. Aquí están tus datos de acceso:</p>

    <div class="info-box">
        <p><strong>Correo:</strong> {{ $email }}</p>
        <p><strong>Contraseña:</strong> {{ $password }}</p>
    </div>

    <div style="display: flex; flex-direction: column; align-items: center;">
        <p style="font-size: small; text-align: center;">Por seguridad, te recomendamos cambiar tu contraseña 
            después de iniciar sesión. Puedes iniciar sesión desde el siguiente enlace.
        </p>
        

        <a href="{{ $loginUrl }}" class="button" style="margin-bottom: 1rem;">
            Iniciar sesión
        </a>
    </div>

    
@endsection
