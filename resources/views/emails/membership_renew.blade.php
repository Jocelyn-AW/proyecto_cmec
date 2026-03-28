@extends('emails.layouts.base')

@section('content')
    <h2>Membresía activada</h2>

    <p>Hola <strong>{{ $name }}</strong>,</p>

    <p>Tu membresía ha sido renovada existosamente.</p>

    <div class="info-box">
        <p><strong>Fecha de Renovación: </strong> {{ $inscription_date }}</p>
        <p><strong>Fecha de Vencimiento: </strong> {{ $expiration_date }}</p>
    </div>

    <p>Gracias por formar parte de CMEC. </p>
    
@endsection
