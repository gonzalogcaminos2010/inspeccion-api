<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Preliminar de Inspección #{{ $inspection->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.4;
            padding: 20px 30px;
        }

        /* ===== HEADER ===== */
        .header-table { width: 100%; margin-bottom: 15px; }
        .header-table td { vertical-align: middle; }
        .header-logo { width: 100px; }
        .header-logo img { max-width: 90px; max-height: 55px; }
        .header-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        /* ===== INSPECTOR BOX ===== */
        .inspector-box {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .inspector-box td {
            border: 1px solid #000;
            padding: 5px 8px;
            font-size: 11px;
        }
        .inspector-box .lbl { font-weight: normal; }

        /* ===== CERTIFICA ===== */
        .certifica-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 6px;
        }
        .certifica-text {
            font-size: 11px;
            margin-bottom: 8px;
        }

        /* ===== EQUIPMENT TABLE ===== */
        .equip-table {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .equip-table td {
            border: 1px solid #000;
            padding: 4px 8px;
            font-size: 11px;
        }
        .equip-table .lbl { font-weight: bold; white-space: nowrap; }

        /* ===== CLAVE ===== */
        .clave-section {
            margin-bottom: 10px;
        }
        .clave-title {
            font-weight: bold;
            font-style: italic;
            font-size: 11px;
            margin-bottom: 4px;
        }
        .clave-box {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
        }
        .clave-box td {
            border: 1px solid #000;
            padding: 4px 8px;
            font-size: 11px;
        }
        .clave-box .lbl { font-weight: bold; }

        /* ===== NORMAS ===== */
        .normas-section { margin-bottom: 10px; }
        .normas-title {
            font-weight: bold;
            font-style: italic;
            font-size: 11px;
            margin-bottom: 4px;
        }
        .normas-text { font-size: 9px; line-height: 1.5; }

        /* ===== RESULTADO ===== */
        .resultado-line {
            font-size: 11px;
            margin-bottom: 4px;
        }
        .resultado-value {
            font-weight: bold;
            text-decoration: underline;
        }

        /* ===== RECOMENDACION ===== */
        .recomendacion-box {
            border: 1px solid #000;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: bold;
            font-style: italic;
            margin-bottom: 6px;
        }

        /* ===== OBSERVACIONES ===== */
        .observaciones-line {
            font-size: 11px;
            margin-bottom: 10px;
        }

        /* ===== NOTA ===== */
        .nota-text {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        /* ===== FIRMAS ===== */
        .firmas-table { width: 100%; margin-top: 20px; }
        .firmas-table td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 10px 20px;
        }
        .firma-img { max-width: 180px; max-height: 70px; }
        .firma-line {
            border-top: 1px solid #000;
            padding-top: 5px;
            font-weight: bold;
            font-style: italic;
            font-size: 11px;
        }
        .firma-nombre { font-size: 10px; font-weight: bold; }
        .firma-cargo { font-size: 9px; }

        /* ===== FOOTER ===== */
        .footer {
            position: fixed;
            bottom: 15px;
            left: 30px;
            right: 30px;
        }
        .footer-table { width: 100%; }
        .footer-left { text-align: left; font-size: 9px; }
        .footer-right { text-align: right; font-size: 9px; }

        /* ===== WATERMARK ===== */
        .watermark {
            position: fixed;
            top: 35%;
            left: 10%;
            font-size: 90px;
            color: rgba(200, 200, 200, 0.3);
            transform: rotate(-35deg);
            font-weight: bold;
            letter-spacing: 10px;
            z-index: -1;
        }
    </style>
</head>
<body>
    @if(!empty($isPreview))
        <div class="watermark">PREVIEW</div>
    @endif

    {{-- ===== FOOTER FIJO ===== --}}
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td class="footer-left">R3 PEAT 01 REV.07 - DICIEMBRE 2019</td>
                <td class="footer-right">Pág. 1 de 1</td>
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
                INFORME PRELIMINAR DE INSPECCION
            </td>
        </tr>
    </table>

    {{-- ===== INSPECTOR INFO ===== --}}
    <table class="inspector-box">
        <tr>
            <td style="width: 60%;">
                <span class="lbl">Nombre del Inspector :</span>
                {{ $inspector->name ?? '—' }}
            </td>
            <td style="width: 40%;">
                <span class="lbl">Informe Nro. :</span>
                {{ $inspection->id }}
            </td>
        </tr>
        <tr>
            <td>
                <span class="lbl">Empresa :</span>
                {{ $client->name ?? '—' }}
            </td>
            <td>
                <span class="lbl">Fecha :</span>
                {{ $inspection->started_at?->format('d-m-Y') ?? now()->format('d-m-Y') }}
            </td>
        </tr>
    </table>

    {{-- ===== CERTIFICA ===== --}}
    <p class="certifica-title">CERTIFICA:</p>
    <p class="certifica-text">1) Que se ha realizado la Inspección del objeto cuyas características son:</p>

    {{-- ===== DATOS DEL EQUIPO ===== --}}
    <table class="equip-table">
        <tr>
            <td colspan="2">
                <span class="lbl">Nombre del Equipo :</span>
                {{ $equipment->name ?? '—' }}
            </td>
        </tr>
        <tr>
            <td style="width: 50%;">
                <span class="lbl">Marca :</span>
                {{ $equipment->brand ?? '—' }}
            </td>
            <td style="width: 50%;">
                <span class="lbl">Modelo :</span>
                {{ $equipment->model ?? '—' }}
            </td>
        </tr>
        <tr>
            <td>
                <span class="lbl">Estructura dimensiones :</span>
                {{ $equipment->metadata['estructura_dimensiones'] ?? '—' }}
            </td>
            <td>
                <span class="lbl">N° de Serie :</span>
                {{ $equipment->serial_number ?? '—' }}
            </td>
        </tr>
        <tr>
            <td>
                <span class="lbl">Interno N° :</span>
                {{ $equipment->internal_code ?? '—' }}
            </td>
            <td>
                <span class="lbl">Año de Fabricación :</span>
                {{ $equipment->year ?? '—' }}
            </td>
        </tr>
        {{-- Campos dinámicos del metadata --}}
        @php
            $metaFields = $equipment->metadata ?? [];
            // Excluir campos ya mostrados arriba y campos internos
            $excludeKeys = ['estructura_dimensiones', 'clave', 'normas_referencia', 'proxima_inspeccion'];
            $extraFields = array_diff_key($metaFields, array_flip($excludeKeys));
        @endphp
        @foreach(collect($extraFields)->chunk(2) as $chunk)
            <tr>
                @foreach($chunk as $key => $value)
                    <td>
                        <span class="lbl">{{ str_replace('_', ' ', ucfirst($key)) }} :</span>
                        {{ $value }}
                    </td>
                @endforeach
                @if($chunk->count() === 1)
                    <td></td>
                @endif
            </tr>
        @endforeach
    </table>

    {{-- ===== CLAVE DE IDENTIFICACIÓN ===== --}}
    <div class="clave-section">
        <p class="clave-title">Clave de Identificación del Equipo</p>
        <table class="clave-box">
            <tr>
                <td>
                    <span class="lbl">Clave :</span>
                    {{ $equipment->metadata['clave'] ?? ($equipment->serial_number ? $equipment->year . '-' . substr($equipment->serial_number, -4) : '—') }}
                </td>
                <td>
                    <span class="lbl">Oblea :</span>
                    {{ $equipment->metadata['oblea'] ?? '—' }}
                </td>
            </tr>
        </table>
    </div>

    {{-- ===== NORMAS DE REFERENCIA ===== --}}
    <div class="normas-section">
        <p class="normas-title">Normas de Referencia</p>
        @if(!empty($equipment->metadata['normas_referencia']))
            <div class="normas-text">
                {!! nl2br(e($equipment->metadata['normas_referencia'])) !!}
            </div>
        @elseif($template->description)
            <div class="normas-text">{{ $template->description }}</div>
        @else
            <div class="normas-text">
                PE ATI 03 Procedimiento Especial para la Inspección de equipos especiales.<br>
                IE ATI 18 Instructivo Especial de Inspección y Evaluación de Defectos.
            </div>
        @endif
    </div>

    {{-- ===== RESULTADO ===== --}}
    <p class="resultado-line">
        2) Que el resultado de la inspección es :
        <span class="resultado-value">
            {{ $overallResultLabel }}
        </span>
        @if(!empty($isPreview) && !$inspection->overall_result)
            <em style="font-size: 9px; color: #888;">(preliminar)</em>
        @endif
    </p>

    {{-- ===== TESTIGO ===== --}}
    <p class="resultado-line">
        3) Que ha presenciado las pruebas :
        {{ $client->contact_name ?? '—' }}
    </p>

    <br>

    {{-- ===== RECOMENDACIÓN PRÓXIMA INSPECCIÓN ===== --}}
    <div class="recomendacion-box">
        Se recomienda que el equipo sea sometido a una nueva inspección antes de :
        {{ $equipment->metadata['proxima_inspeccion'] ?? '—' }}
    </div>

    {{-- ===== OBSERVACIONES ===== --}}
    <p class="observaciones-line">
        <strong>OBSERVACIONES :</strong>
        {{ $inspection->observations ?? 'Sin observaciones.' }}
    </p>

    {{-- ===== NOTA ===== --}}
    <p class="nota-text">
        NOTA: El informe queda sujeto a la aprobación con la emisión del certificado
    </p>

    {{-- ===== FIRMAS ===== --}}
    <table class="firmas-table">
        <tr>
            {{-- Firma Inspector --}}
            <td>
                @if(isset($signatures['inspector']))
                    <img src="{{ $signatures['inspector'] }}" class="firma-img" alt="Firma Inspector"><br>
                @else
                    <div style="height: 70px;"></div>
                @endif
                <div class="firma-line">Firma Inspector</div>
                @if($inspector)
                    <div class="firma-nombre">{{ strtoupper($inspector->name) }}</div>
                    <div class="firma-cargo">INSPECTOR</div>
                    <div class="firma-cargo">O.I. ESTÁNDAR Co S.R.L.</div>
                    <div class="firma-cargo">AMERICAN ADVISOR</div>
                @endif
            </td>
            {{-- Firma Cliente --}}
            <td>
                @if(isset($signatures['client']))
                    <img src="{{ $signatures['client'] }}" class="firma-img" alt="Firma Cliente"><br>
                @else
                    <div style="height: 70px;"></div>
                @endif
                <div class="firma-line">Firma Representante del Cliente</div>
                @if($client)
                    <div class="firma-nombre">{{ $client->contact_name ?? '' }}</div>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
