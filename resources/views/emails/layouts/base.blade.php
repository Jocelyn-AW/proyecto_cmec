<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Correo' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f4f4f7;
            color: #333;
            line-height: 1.6;
        }

        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }

        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
        }

        .email-header img {
            max-width: 150px;
            height: auto;
        }

        .email-header h1 {
            color: #ffffff;
            margin-top: 15px;
            font-size: 24px;
            font-weight: 600;
        }

        .email-body {
            padding: 40px 30px;
        }

        .email-body h2 {
            color: #1a202c;
            font-size: 20px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .email-body p {
            color: #4a5568;
            margin-bottom: 16px;
            font-size: 15px;
        }

        .button {
            display: inline-block;
            padding: 14px 28px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
        }

        .button:hover {
            opacity: 0.9;
        }

        .info-box {
            background-color: #f7fafc;
            border-left: 4px solid #667eea;
            padding: 16px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .email-footer {
            background-color: #f7fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }

        .email-footer p {
            color: #718096;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .social-links {
            margin-top: 20px;
        }

        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #667eea;
            text-decoration: none;
        }

        @media only screen and (max-width: 600px) {
            .email-body {
                padding: 30px 20px;
            }

            .email-header {
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="email-header">
            @if (isset($logo))
                <img src="{{ $logo }}" alt="Logo">
            @endif
            <h1>{{ $headerTitle ?? config('app.name') }}</h1>
        </div>

        <!-- Body Content -->
        <div class="email-body">
            @yield('content')
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
            <p>{{ $footerAddress ?? 'Dirección de la empresa' }}</p>

            @if (isset($unsubscribeUrl))
                <p style="margin-top: 15px;">
                    <a href="{{ $unsubscribeUrl }}" style="color: #718096; text-decoration: underline;">
                        Cancelar suscripción
                    </a>
                </p>
            @endif

            <div class="social-links">
                @if (isset($socialLinks))
                    @foreach ($socialLinks as $platform => $url)
                        <a href="{{ $url }}">{{ ucfirst($platform) }}</a>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</body>

</html>
