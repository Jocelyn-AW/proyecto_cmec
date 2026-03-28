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
            background-color: #e8ebf4;
            color: #333;
            line-height: 1.6;
            padding: 40px 20px;
        }

        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(61, 71, 133, 0.15);
        }

        .email-header {
            background: linear-gradient(135deg, #2E3A6E 0%, #3D4785 50%, #4A569D 100%);
            padding: 50px 30px;
            text-align: center;
            position: relative;
        }

        .email-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.5;
        }

        .email-header-content {
            position: relative;
            z-index: 1;
        }

        .logo-container {
            display: inline-block;
            /* background: rgba(255, 255, 255, 0.2); */
            /* padding: 16px; */
            border-radius: 16px;
            backdrop-filter: blur(10px);
            /* margin-bottom: 16px; */
        }

        .email-header img {
            max-width: 120px;
            height: auto;
            display: block;
        }

        .email-header h1 {
            color: #ffffff;
            margin-top: 12px;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .email-header-subtitle {
            color: rgba(255, 255, 255, 0.85);
            font-size: 14px;
            margin-top: 8px;
            font-weight: 400;
        }

        .email-body {
            padding: 48px 40px;
        }

        .greeting {
            font-size: 18px;
            color: #3D4785;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .email-body h2 {
            color: #1a1a2e;
            font-size: 22px;
            margin-bottom: 20px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .email-body p {
            color: #4a5568;
            margin-bottom: 18px;
            font-size: 15px;
            line-height: 1.7;
        }

        .button-container {
            text-align: center;
            margin: 32px 0;
        }

        .button {
            display: inline-block;
            padding: 16px 36px;
            background: linear-gradient(135deg, #2E3A6E 0%, #3D4785 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 15px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(61, 71, 133, 0.35);
            transition: all 0.3s ease;
            letter-spacing: 0.3px;
        }

        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(61, 71, 133, 0.45);
        }

        .button-secondary {
            background: transparent;
            border: 2px solid #3D4785;
            color: #3D4785 !important;
            box-shadow: none;
        }

        .info-box {
            background: linear-gradient(135deg, #f4f5fa 0%, #e8ebf4 100%);
            border-left: 4px solid #3D4785;
            padding: 20px 24px;
            margin: 24px 0;
            border-radius: 0 12px 12px 0;
        }

        .info-box p {
            margin-bottom: 0;
            color: #2E3A6E;
        }

        .info-box strong {
            color: #3D4785;
        }

        .highlight-box {
            background: linear-gradient(135deg, #2E3A6E 0%, #3D4785 100%);
            padding: 24px;
            border-radius: 12px;
            margin: 24px 0;
            text-align: center;
        }

        .highlight-box p {
            color: #ffffff;
            margin: 0;
            font-size: 16px;
        }

        .highlight-box .highlight-value {
            font-size: 32px;
            font-weight: 700;
            color: #ffffff;
            display: block;
            margin-top: 8px;
        }

        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #d0d5e8, transparent);
            margin: 32px 0;
        }

        .features-grid {
            display: table;
            width: 100%;
            margin: 24px 0;
        }

        .feature-item {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 16px 8px;
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #f4f5fa 0%, #e8ebf4 100%);
            border-radius: 12px;
            margin: 0 auto 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .feature-item h4 {
            color: #1a1a2e;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .feature-item p {
            color: #718096;
            font-size: 12px;
            margin: 0;
        }

        .email-footer {
            background: linear-gradient(180deg, #f4f5fa 0%, #e8ebf4 100%);
            padding: 40px 30px;
            text-align: center;
            border-top: 1px solid #d0d5e8;
        }

        .footer-logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #2E3A6E 0%, #3D4785 100%);
            border-radius: 10px;
            margin: 0 auto 16px;
        }

        .email-footer p {
            color: #5a6380;
            font-size: 13px;
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .email-footer a {
            color: #3D4785;
            text-decoration: none;
            font-weight: 500;
        }

        .email-footer a:hover {
            text-decoration: underline;
        }

        .social-links {
            margin: 24px 0;
        }

        .social-links a {
            display: inline-block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            margin: 0 6px;
            background: #ffffff;
            color: #3D4785;
            text-decoration: none;
            border-radius: 50%;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(61, 71, 133, 0.15);
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: linear-gradient(135deg, #2E3A6E 0%, #3D4785 100%);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(61, 71, 133, 0.3);
        }

        .unsubscribe-link {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #d0d5e8;
        }

        .unsubscribe-link a {
            color: #a0aec0 !important;
            font-size: 12px;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            background: linear-gradient(135deg, #2E3A6E 0%, #3D4785 100%);
            color: #ffffff;
            font-size: 11px;
            font-weight: 600;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        @media only screen and (max-width: 600px) {
            body {
                padding: 20px 12px;
            }

            .email-body {
                padding: 32px 24px;
            }

            .email-header {
                padding: 40px 24px;
            }

            .email-header h1 {
                font-size: 22px;
            }

            .button {
                display: block;
                padding: 16px 24px;
            }

            .feature-item {
                display: block;
                width: 100%;
                padding: 12px 0;
            }

            .features-grid {
                display: block;
            }
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="email-header">
            <div class="email-header-content">
                <div class="logo-container">
                    <img src="{{ asset('images/placeholders/Logo-CMECB-300x300.png') }}" alt="Logo">
                </div>
                @if (isset($headerSubtitle))
                    <p class="email-header-subtitle">{{ $headerSubtitle }}</p>
                @endif
            </div>
        </div>

        <!-- Body Content -->
        <div class="email-body">
            @yield('content')
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>Colegio Mexicano de Especialistas en Coloproctología</strong></p>
            <p>{{ $footerAddress ?? 'Dirección de la empresa' }}</p>

            <div class="social-links">
                @if (isset($socialLinks))
                    @foreach ($socialLinks as $platform => $url)
                        <a href="{{ $url }}">{{ strtoupper(substr($platform, 0, 2)) }}</a>
                    @endforeach
                @endif
            </div>

            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>

            @if (isset($unsubscribeUrl))
                <div class="unsubscribe-link">
                    <a href="{{ $unsubscribeUrl }}">Cancelar suscripción</a>
                </div>
            @endif
        </div>
    </div>
</body>

</html>
