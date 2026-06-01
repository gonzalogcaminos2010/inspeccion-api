<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Inspecciones finalizadas</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
    <h2 style="color: #1e3a8a;">American Advisor — Inspecciones finalizadas</h2>

    <p>Estimado cliente
        @if($inspectionRequest->client?->name)
            <strong>{{ $inspectionRequest->client->name }}</strong>,
        @else
            ,
        @endif
    </p>

    <p>
        Las inspecciones de la solicitud
        <strong>{{ $inspectionRequest->request_number ?? '#'.$inspectionRequest->id }}</strong>
        fueron revisadas y aprobadas. Detalle:
    </p>

    <table cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr style="background: #f3f4f6; text-align: left;">
                <th style="border-bottom: 1px solid #e5e7eb;">Equipo</th>
                <th style="border-bottom: 1px solid #e5e7eb;">Resultado</th>
                <th style="border-bottom: 1px solid #e5e7eb;">Certificado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inspections as $i)
                <tr>
                    <td style="border-bottom: 1px solid #f3f4f6;">{{ $i['equipment'] }}</td>
                    <td style="border-bottom: 1px solid #f3f4f6;">{{ $i['result'] ?? '—' }}</td>
                    <td style="border-bottom: 1px solid #f3f4f6;">
                        @if($i['public_url'])
                            <a href="{{ $i['public_url'] }}">{{ $i['certificate'] ?? 'Ver' }}</a>
                        @else
                            {{ $i['certificate'] ?? '—' }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 24px; color: #6b7280; font-size: 13px;">
        Este es un mensaje automático de American Advisor — Sistema de Inspección.
    </p>
</body>
</html>
