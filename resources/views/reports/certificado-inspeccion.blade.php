<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado de Inspección - {{ $inspection->certificate_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #000;
            line-height: 1.4;
            padding: 15px 25px;
        }

        /* ===== HEADER ===== */
        .header-table { width: 100%; margin-bottom: 12px; }
        .header-table td { vertical-align: middle; }
        .header-logo { width: 100px; }
        .header-logo img { max-width: 90px; max-height: 55px; }
        .header-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        /* ===== TABLES ===== */
        .info-table {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .info-table td {
            border: 1px solid #000;
            padding: 4px 8px;
            font-size: 10px;
        }
        .info-table .lbl { font-weight: bold; white-space: nowrap; }

        /* ===== DECLARATION ===== */
        .declaration {
            margin: 12px 0;
            font-size: 10px;
            line-height: 1.6;
            text-align: justify;
        }
        .declaration strong {
            font-size: 11px;
        }

        /* ===== RE-INSPECTION ===== */
        .reinspection-box {
            border: 2px solid #000;
            padding: 8px 12px;
            margin: 10px 0;
            text-align: center;
            font-size: 11px;
            font-weight: bold;
        }

        /* ===== OBLEA ===== */
        .oblea-section {
            margin: 10px 0;
            font-size: 11px;
        }
        .oblea-section .lbl { font-weight: bold; }

        /* ===== QR ===== */
        .qr-section {
            text-align: center;
            margin: 10px 0;
        }
        .qr-section img {
            width: 120px;
            height: 120px;
        }
        .qr-label {
            font-size: 8px;
            color: #555;
            margin-top: 4px;
        }

        /* ===== FIRMAS ===== */
        .firmas-table { width: 100%; margin-top: 15px; }
        .firmas-table td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 8px 15px;
        }
        .firma-img { max-width: 160px; max-height: 60px; }
        .firma-line {
            border-top: 1px solid #000;
            padding-top: 4px;
            font-weight: bold;
            font-style: italic;
            font-size: 10px;
        }
        .firma-nombre { font-size: 9px; font-weight: bold; }
        .firma-cargo { font-size: 8px; }

        /* ===== FOOTER ===== */
        .footer {
            position: fixed;
            bottom: 10px;
            left: 25px;
            right: 25px;
        }
        .footer-table { width: 100%; }
        .footer-left { text-align: left; font-size: 8px; }
        .footer-right { text-align: right; font-size: 8px; }

        /* ===== NOTE ===== */
        .note-text {
            font-size: 9px;
            font-style: italic;
            margin: 8px 0;
        }
    </style>
</head>
<body>

    {{-- ===== FOOTER FIJO ===== --}}
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td class="footer-left">CERT-AA-01 REV.01 - {{ now()->format('F Y') }}</td>
                <td class="footer-right">Pag. 1 de 1</td>
            </tr>
        </table>
    </div>

    {{-- ===== HEADER ===== --}}
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @php
                    $logoPath = public_path('images/logo-american-advisor.png');
                @endphp
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}" alt="Logo">
                @else
                    <strong style="font-size: 10px;">AMERICAN<br>ADVISOR</strong>
                @endif
            </td>
            <td class="header-title">
                CERTIFICADO DE INSPECCION DE<br>
                {{ strtoupper($equipment->name ?? 'EQUIPO') }}
            </td>
        </tr>
    </table>

    {{-- ===== INFO GENERAL ===== --}}
    <table class="info-table">
        <tr>
            <td style="width: 50%;">
                <span class="lbl">Set N° :</span>
                {{ $inspection->id }}
            </td>
            <td style="width: 50%;">
                <span class="lbl">Certificado N° :</span>
                {{ $inspection->certificate_number }}
            </td>
        </tr>
        <tr>
            <td>
                <span class="lbl">Solicitante :</span>
                {{ $client->name ?? '---' }}
            </td>
            <td>
                <span class="lbl">Propietario :</span>
                {{ $client->name ?? '---' }}
            </td>
        </tr>
        <tr>
            <td>
                <span class="lbl">Lugar de inspeccion :</span>
                {{ $equipment->metadata['lugar_inspeccion'] ?? $client->address ?? '---' }}
            </td>
            <td>
                <span class="lbl">Fecha :</span>
                {{ $inspection->certificate_issued_at?->format('d/m/Y') ?? now()->format('d/m/Y') }}
            </td>
        </tr>
    </table>

    {{-- ===== DATOS DEL EQUIPO ===== --}}
    <table class="info-table">
        <tr>
            <td colspan="2">
                <span class="lbl">Tipo de Equipo :</span>
                {{ $equipment->name ?? '---' }}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="lbl">Norma/s aplicable/s :</span>
                {{ $equipment->metadata['normas_referencia'] ?? $template->description ?? '---' }}
            </td>
        </tr>
        <tr>
            <td style="width: 50%;">
                <span class="lbl">Marca :</span>
                {{ $equipment->brand ?? '---' }}
            </td>
            <td style="width: 50%;">
                <span class="lbl">Modelo :</span>
                {{ $equipment->model ?? '---' }}
            </td>
        </tr>
        <tr>
            <td>
                <span class="lbl">N° de Serie :</span>
                {{ $equipment->serial_number ?? '---' }}
            </td>
            <td>
                <span class="lbl">Capacidad Maxima :</span>
                {{ $equipment->metadata['capacidad_maxima'] ?? '---' }}
            </td>
        </tr>
        <tr>
            <td>
                <span class="lbl">Ano de fabricacion :</span>
                {{ $equipment->year ?? '---' }}
            </td>
            <td>
                <span class="lbl">N° de Interno :</span>
                {{ $equipment->internal_code ?? '---' }}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="lbl">Dominio :</span>
                {{ $equipment->plate ?? $equipment->metadata['dominio'] ?? '---' }}
            </td>
        </tr>
    </table>

    {{-- ===== DECLARACION ===== --}}
    <div class="declaration">
        <p>El equipo fue inspeccionado por <strong>American Advisor</strong>, que certifica que cumple con los requisitos de la/s norma/s tecnica/s aplicable/s, siendo apto para su operacion.</p>
    </div>

    {{-- ===== NOTA ===== --}}
    <p class="note-text">
        Las futuras inspecciones quedaran bajo la responsabilidad del propietario o usuario.
    </p>

    {{-- ===== RE-INSPECCION ===== --}}
    <div class="reinspection-box">
        SE RECOMIENDA INSPECCIONAR NUEVAMENTE EN UN LAPSO DE:
        <span style="font-size: 14px; text-decoration: underline;">
            {{ $equipment->metadata['proxima_inspeccion'] ?? '12 MESES' }}
        </span>
    </div>

    {{-- ===== OBLEA / PRECINTO ===== --}}
    <div class="oblea-section">
        <span class="lbl">Oblea / Precinto N° :</span>
        {{ $inspection->certificate_number }}
    </div>

    {{-- ===== QR + FIRMA LAYOUT ===== --}}
    <table style="width: 100%; margin-top: 10px;">
        <tr>
            {{-- QR Code --}}
            <td style="width: 40%; text-align: center; vertical-align: top;">
                <div class="qr-section">
                    @if(!empty($qrCodeDataUri))
                        <img src="{{ $qrCodeDataUri }}" alt="QR Verificacion">
                        <div class="qr-label">Escanee para verificar este certificado</div>
                    @endif
                </div>
            </td>
            {{-- Firma Supervisor --}}
            <td style="width: 60%; text-align: center; vertical-align: bottom;">
                @if(isset($signatures['supervisor']))
                    <img src="{{ $signatures['supervisor'] }}" class="firma-img" alt="Firma Supervisor"><br>
                @else
                    <div style="height: 60px;"></div>
                @endif
                <div class="firma-line">Revisado por</div>
                @if($inspection->approver)
                    <div class="firma-nombre">{{ strtoupper($inspection->approver->name) }}</div>
                    <div class="firma-cargo">SUPERVISOR</div>
                    <div class="firma-cargo">AMERICAN ADVISOR</div>
                @endif
                <div class="firma-cargo" style="margin-top: 4px;">
                    Fecha de emision: {{ $inspection->certificate_issued_at?->format('d/m/Y') }}
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
