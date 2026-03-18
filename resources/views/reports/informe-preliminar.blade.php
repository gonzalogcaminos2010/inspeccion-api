<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Preliminar de Inspección #{{ $inspection->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        .page-break {
            page-break-after: always;
        }

        /* Header */
        .header {
            width: 100%;
            border-bottom: 2px solid #1a365d;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header table {
            width: 100%;
        }
        .header .logo {
            width: 120px;
        }
        .header .logo img {
            max-width: 110px;
            max-height: 60px;
        }
        .header .title-cell {
            text-align: center;
            vertical-align: middle;
        }
        .header .title-cell h1 {
            font-size: 14px;
            color: #1a365d;
            margin-bottom: 2px;
        }
        .header .title-cell h2 {
            font-size: 10px;
            color: #555;
            font-weight: normal;
        }
        .header .doc-info {
            width: 140px;
            text-align: right;
            font-size: 8px;
            color: #666;
            vertical-align: middle;
        }

        /* Section headers */
        .section-title {
            background-color: #1a365d;
            color: #fff;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 12px;
            margin-bottom: 6px;
        }

        /* Info tables */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .info-table td {
            padding: 4px 8px;
            border: 1px solid #ddd;
            font-size: 9px;
        }
        .info-table .label {
            background-color: #f0f4f8;
            font-weight: bold;
            width: 25%;
            color: #1a365d;
        }
        .info-table .value {
            width: 25%;
        }

        /* Answers table */
        .answers-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .answers-table th {
            background-color: #2d4a7a;
            color: #fff;
            padding: 5px 8px;
            font-size: 9px;
            text-align: left;
            font-weight: bold;
        }
        .answers-table td {
            padding: 4px 8px;
            border: 1px solid #ddd;
            font-size: 9px;
        }
        .answers-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .answers-table .flagged {
            color: #c53030;
            font-weight: bold;
        }

        /* Findings table */
        .findings-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .findings-table th {
            background-color: #2d4a7a;
            color: #fff;
            padding: 5px 8px;
            font-size: 9px;
            text-align: left;
        }
        .findings-table td {
            padding: 4px 8px;
            border: 1px solid #ddd;
            font-size: 9px;
        }
        .severity-critical { color: #c53030; font-weight: bold; }
        .severity-major { color: #d69e2e; font-weight: bold; }
        .severity-minor { color: #3182ce; }

        /* Result box */
        .result-box {
            border: 2px solid #1a365d;
            padding: 10px;
            text-align: center;
            margin: 10px 0;
        }
        .result-box .result-label {
            font-size: 10px;
            color: #555;
            margin-bottom: 4px;
        }
        .result-box .result-value {
            font-size: 16px;
            font-weight: bold;
        }
        .result-approved { color: #276749; }
        .result-rejected { color: #c53030; }
        .result-conditional { color: #d69e2e; }

        /* Score */
        .score-display {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            margin: 5px 0;
        }

        /* Signatures */
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .signatures-table td {
            width: 33%;
            text-align: center;
            padding: 10px;
            vertical-align: bottom;
        }
        .signatures-table .sig-img {
            max-width: 150px;
            max-height: 60px;
        }
        .signatures-table .sig-line {
            border-top: 1px solid #333;
            padding-top: 4px;
            font-size: 9px;
            color: #555;
        }
        .signatures-table .sig-date {
            font-size: 8px;
            color: #888;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }

        /* Observations */
        .observations-box {
            border: 1px solid #ddd;
            padding: 8px;
            margin-bottom: 10px;
            min-height: 40px;
            font-size: 9px;
        }

        /* Watermark Preview */
        .watermark {
            position: fixed;
            top: 35%;
            left: 15%;
            font-size: 80px;
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

    {{-- Footer --}}
    <div class="footer">
        R3 PEAT 01 REV.07 &mdash; Informe Preliminar de Inspección &mdash; American Advisor &mdash; Generado: {{ now()->format('d/m/Y H:i') }}
    </div>

    {{-- Header --}}
    <div class="header">
        <table>
            <tr>
                <td class="logo">
                    @php
                        $logoPath = public_path('images/logo-american-advisor.png');
                    @endphp
                    @if(file_exists($logoPath))
                        <img src="{{ $logoPath }}" alt="Logo">
                    @else
                        <strong style="font-size: 9px; color: #1a365d;">AMERICAN<br>ADVISOR</strong>
                    @endif
                </td>
                <td class="title-cell">
                    <h1>INFORME PRELIMINAR DE INSPECCIÓN</h1>
                    <h2>{{ $template->name ?? 'N/A' }} ({{ $template->code ?? '' }} v{{ $template->version ?? '1' }})</h2>
                </td>
                <td class="doc-info">
                    R3 PEAT 01 REV.07<br>
                    Inspección #{{ $inspection->id }}<br>
                    Fecha: {{ $inspection->started_at?->format('d/m/Y') ?? 'N/A' }}
                </td>
            </tr>
        </table>
    </div>

    {{-- Client & Equipment Info --}}
    <div class="section-title">1. DATOS GENERALES</div>
    <table class="info-table">
        <tr>
            <td class="label">Cliente</td>
            <td class="value">{{ $client->name ?? 'N/A' }}</td>
            <td class="label">RUC</td>
            <td class="value">{{ $client->ruc ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Contacto</td>
            <td class="value">{{ $client->contact_name ?? 'N/A' }}</td>
            <td class="label">Teléfono</td>
            <td class="value">{{ $client->contact_phone ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Dirección</td>
            <td class="value" colspan="3">{{ $client->address ?? 'N/A' }}</td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="label">Equipo</td>
            <td class="value">{{ $equipment->name ?? 'N/A' }}</td>
            <td class="label">Código Interno</td>
            <td class="value">{{ $equipment->internal_code ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Tipo</td>
            <td class="value">{{ $equipment->type ?? 'N/A' }}</td>
            <td class="label">Marca / Modelo</td>
            <td class="value">{{ $equipment->brand ?? '' }} {{ $equipment->model ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Año</td>
            <td class="value">{{ $equipment->year ?? 'N/A' }}</td>
            <td class="label">Placa</td>
            <td class="value">{{ $equipment->plate ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">N° Serie</td>
            <td class="value" colspan="3">{{ $equipment->serial_number ?? 'N/A' }}</td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="label">Inspector</td>
            <td class="value">{{ $inspector->name ?? 'N/A' }}</td>
            <td class="label">Fecha Inspección</td>
            <td class="value">{{ $inspection->started_at?->format('d/m/Y H:i') ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Estado</td>
            <td class="value">{{ strtoupper($inspection->status) }}</td>
            <td class="label">Fecha Finalización</td>
            <td class="value">{{ $inspection->completed_at?->format('d/m/Y H:i') ?? 'Pendiente' }}</td>
        </tr>
    </table>

    {{-- Result --}}
    <div class="result-box">
        <div class="result-label">RESULTADO GENERAL DE LA INSPECCIÓN</div>
        <div class="result-value @if($inspection->overall_result === 'approved') result-approved @elseif($inspection->overall_result === 'rejected') result-rejected @else result-conditional @endif">
            {{ $overallResultLabel }}
        </div>
        @if(!empty($isPreview))
            <div style="font-size: 9px; color: #888; font-style: italic;">(resultado preliminar)</div>
        @endif
        @if($inspection->score !== null)
            <div class="score-display">Puntaje: {{ $inspection->score }}%</div>
        @endif
    </div>

    {{-- Answers by Section --}}
    <div class="section-title">2. DETALLE DE LA INSPECCIÓN</div>

    @forelse($answersBySection as $sectionName => $answers)
        <div style="margin-top: 8px; margin-bottom: 4px; font-weight: bold; font-size: 10px; color: #1a365d;">
            {{ $sectionName }}
        </div>
        <table class="answers-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 45%;">Pregunta</th>
                    <th style="width: 30%;">Respuesta</th>
                    <th style="width: 20%;">Observación</th>
                </tr>
            </thead>
            <tbody>
                @foreach($answers as $index => $answer)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $answer->question?->text ?? 'N/A' }}</td>
                        <td class="{{ $answer->is_flagged ? 'flagged' : '' }}">
                            @if($answer->question?->type === 'yes_no')
                                @if($answer->answer_boolean === true)
                                    SÍ
                                @elseif($answer->answer_boolean === false)
                                    NO
                                @else
                                    —
                                @endif
                                @if($answer->is_flagged)
                                    ⚠
                                @endif
                            @elseif($answer->answer_text)
                                {{ $answer->answer_text }}
                            @elseif($answer->answer_number !== null)
                                {{ $answer->answer_number }}
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $answer->notes ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p style="font-size: 9px; color: #888; padding: 8px;">No se registraron respuestas.</p>
    @endforelse

    {{-- Findings --}}
    @if($inspection->findings->isNotEmpty())
        <div class="section-title">3. HALLAZGOS</div>
        <table class="findings-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 12%;">Severidad</th>
                    <th style="width: 43%;">Descripción</th>
                    <th style="width: 30%;">Recomendación</th>
                    <th style="width: 10%;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inspection->findings as $index => $finding)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="severity-{{ $finding->severity }}">
                            {{ strtoupper($finding->severity) }}
                        </td>
                        <td>{{ $finding->description }}</td>
                        <td>{{ $finding->recommendation ?? '—' }}</td>
                        <td>{{ $finding->is_resolved ? 'Resuelto' : 'Pendiente' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Observations --}}
    <div class="section-title">{{ $inspection->findings->isNotEmpty() ? '4' : '3' }}. OBSERVACIONES</div>
    <div class="observations-box">
        {{ $inspection->observations ?? 'Sin observaciones.' }}
    </div>

    @if($inspection->supervisor_notes)
        <div class="section-title">{{ $inspection->findings->isNotEmpty() ? '5' : '4' }}. NOTAS DEL SUPERVISOR</div>
        <div class="observations-box">
            {{ $inspection->supervisor_notes }}
        </div>
    @endif

    {{-- Signatures --}}
    <div class="section-title">FIRMAS</div>
    <table class="signatures-table">
        <tr>
            @foreach(['inspector' => 'Inspector', 'supervisor' => 'Supervisor', 'client' => 'Cliente'] as $role => $label)
                <td>
                    @if(isset($signatures[$role]))
                        <img src="{{ $signatures[$role] }}" class="sig-img" alt="Firma {{ $label }}"><br>
                    @else
                        <div style="height: 60px;"></div>
                    @endif
                    <div class="sig-line">
                        {{ $label }}
                        @if($role === 'inspector' && $inspector)
                            <br>{{ $inspector->name }}
                        @endif
                        @if($role === 'supervisor' && $inspection->approver)
                            <br>{{ $inspection->approver->name }}
                        @endif
                    </div>
                    @php
                        $signedAt = $inspection->{$role . '_signed_at'};
                    @endphp
                    @if($signedAt)
                        <div class="sig-date">{{ $signedAt->format('d/m/Y H:i') }}</div>
                    @endif
                </td>
            @endforeach
        </tr>
    </table>
</body>
</html>
