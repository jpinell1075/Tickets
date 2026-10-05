@php
    $equipo = App\Models\Equipos\Equipo::with([
        'usuario',
        'area',
        'caracteristicas',
        'mantenimientos' => function ($query) {
            $query->with(['encargado', 'ticket'])->orderBy('fecha', 'desc');
        },
    ])->find($params['equipo_id'] ?? null);

    $caracteristicas = $equipo?->caracteristicas?->keyBy('nombre') ?? collect();
    $mantenimientos = $equipo?->mantenimientos ?? collect();
    $totalMantenimientos = $mantenimientos->count();
    $conTicket = $mantenimientos->whereNotNull('id_ticket')->count();
    $directos = $totalMantenimientos - $conTicket;
@endphp

<style>
    .print-sheet {
        font-family: Arial, Helvetica, sans-serif;
        color: #111827;
        line-height: 1.3;
        background: #ffffff;
        padding: 0;
    }

    .print-header {
        border-bottom: 2px solid #7c3aed;
        padding: 0 0 10px 0;
        margin-bottom: 12px;
        background: #ffffff;
    }

    .print-title {
        font-size: 16px;
        font-weight: 700;
        margin: 0;
        color: #5b21b6;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .print-subtitle {
        font-size: 11px;
        color: #4b5563;
        margin-top: 2px;
    }

    .brand-mark {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .brand-logo {
        width: 44px;
        height: 44px;
        object-fit: contain;
        flex: 0 0 auto;
    }

    .section-card {
        border: 1px solid #d1d5db;
        margin-bottom: 12px;
        background: #ffffff;
    }

    .section-header {
        background: #f8fafc;
        color: #5b21b6;
        padding: 6px 10px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        border-bottom: 1px solid #d1d5db;
    }

    .section-body {
        padding: 8px 10px;
        background: #ffffff;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 6px 10px;
    }

    .info-item {
        padding: 5px 8px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
    }

    .info-label {
        display: block;
        font-size: 9px;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 1px;
        font-weight: 700;
    }

    .info-value {
        font-size: 11px;
        color: #111827;
        font-weight: 600;
        word-break: break-word;
    }

    .table-print {
        width: 100%;
        border-collapse: collapse;
    }

    .table-print th,
    .table-print td {
        border: 1px solid #cbd5e1;
        padding: 6px 8px;
        font-size: 10px;
        vertical-align: top;
    }

    .table-print th {
        background: #5b21b6;
        color: #ffffff;
        text-align: left;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 9px;
    }

    .table-print tr:nth-child(even) {
        background-color: #f8fafc;
    }

    .empty-state {
        padding: 16px;
        color: #6b7280;
        font-size: 11px;
        font-style: italic;
        text-align: center;
    }

    /* Firmas */
    .signatures-block {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
        margin-top: 25px;
        page-break-inside: avoid;
        break-inside: avoid;
    }

    .signature-item {
        text-align: center;
    }

    .signature-line {
        border-top: 1px solid #475569;
        margin-bottom: 4px;
    }

    .signature-name {
        font-size: 9.5px;
        font-weight: 700;
        color: #0f172a;
    }

    .signature-title {
        font-size: 8.5px;
        color: #64748b;
    }

    @media print {
        html, body {
            background: #ffffff !important;
        }

        .print-sheet {
            font-size: 10px;
            background: #ffffff;
            padding: 0;
        }

        .section-card {
            break-inside: auto !important;
            page-break-inside: auto !important;
        }

        tr {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        .signatures-block {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }
    }
</style>

<div class="print-sheet container-fluid">
    <div class="print-header">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div class="brand-mark">
                <img src="{{ asset('img/logo_light.png') }}" alt="Logo institucional" class="brand-logo">
                <div>
                    <h1 class="print-title">Historial de Mantenimientos</h1>
                    <div class="print-subtitle">Equipo: {{ $equipo->nombre ?? 'Equipo' }} ({{ $equipo->cod_inventario ?? 'S/C' }})</div>
                </div>
            </div>
            <div class="text-end">
                <div style="font-size: 9px; color: #6b7280;">Fecha de emisión:</div>
                <div style="font-size: 12px; font-weight: 700;">{{ now()->format('d/m/Y g:i A') }}</div>
                <div style="font-size: 9px; color: #6b7280; margin-top: 4px;">Generado por:</div>
                <div style="font-size: 11px; font-weight: 700;">{{ auth()->user()->name ?? 'Sistema' }}</div>
            </div>
        </div>
    </div>

    <!-- Datos del Equipo -->
    <div class="section-card">
        <div class="section-header">Datos Generales y Estado del Equipo</div>
        <div class="section-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Código Inventario</span>
                    <div class="info-value">{{ $equipo->cod_inventario ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <span class="info-label">Nombre del Equipo</span>
                    <div class="info-value">{{ $equipo->nombre ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <span class="info-label">Área / Ubicación</span>
                    <div class="info-value">{{ $equipo->area?->nombre ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <span class="info-label">Usuario Asignado</span>
                    <div class="info-value">{{ $equipo->usuario?->name ?? 'Sin asignar' }}</div>
                </div>
                <div class="info-item">
                    <span class="info-label">Marca / Modelo</span>
                    <div class="info-value">{{ $caracteristicas->get('marca')?->valor ?? 'N/A' }} {{ $caracteristicas->get('modelo')?->valor ?? '' }}</div>
                </div>
                <div class="info-item">
                    <span class="info-label">Estado Funcional</span>
                    <div class="info-value">{{ ucfirst($caracteristicas->get('estado_funcional')?->valor ?? 'N/A') }}</div>
                </div>
                <div class="info-item">
                    <span class="info-label">Total Intervenciones</span>
                    <div class="info-value" style="color: #7c3aed;">{{ $totalMantenimientos }}</div>
                </div>
                <div class="info-item">
                    <span class="info-label">Origen (Ticket / Directo)</span>
                    <div class="info-value">{{ $conTicket }} ticket / {{ $directos }} dir.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Listado de Mantenimientos -->
    <div class="section-card">
        <div class="section-header">Registro de Intervenciones Técnicas Realizadas</div>
        <div class="section-body" style="padding: 0;">
            @if($mantenimientos->count() > 0)
                <table class="table-print">
                    <thead>
                        <tr>
                            <th style="width: 5%; text-align: center;">#</th>
                            <th style="width: 15%;">Fecha y Hora</th>
                            <th style="width: 20%;">Técnico Responsable</th>
                            <th style="width: 12%; text-align: center;">Origen</th>
                            <th style="width: 48%;">Trabajo Realizado / Diagnóstico</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mantenimientos as $index => $mnt)
                            <tr>
                                <td style="text-align: center; font-weight: 700; width: 5%;">#{{ $mnt->id }}</td>
                                <td style="font-weight: 600; width: 15%;">
                                    {{ $mnt->fecha ? \Carbon\Carbon::parse($mnt->fecha)->format('d/m/Y g:i A') : 'N/A' }}
                                </td>
                                <td style="width: 20%;">
                                    <div style="font-weight: 600;">{{ $mnt->encargado?->name ?? 'Sin asignar' }}</div>
                                    @if($mnt->encargado?->especialidad)
                                        <div style="font-size: 8px; color: #6b7280;">{{ $mnt->encargado->especialidad }}</div>
                                    @endif
                                </td>
                                <td style="text-align: center; width: 12%;">
                                    @if($mnt->ticket)
                                        <span style="font-weight: 700; color: #2563eb;">Ticket #{{ $mnt->ticket->id }}</span>
                                    @else
                                        <span style="color: #6b7280; font-size: 9px; font-style: italic;">Directo</span>
                                    @endif
                                </td>
                                <td style="white-space: pre-line; width: 48%;">{{ $mnt->observacion ?: 'Mantenimiento preventivo / correctivo ejecutado.' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    No se registran mantenimientos técnicos para este equipo.
                </div>
            @endif
        </div>
    </div>
</div>

