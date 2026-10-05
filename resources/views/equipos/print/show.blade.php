@php
    $equipo = App\Models\Equipos\Equipo::with([
        'usuario',
        'area',
        'caracteristicas',
        'componentes.caracteristicas',
        'perifericos',
        'historialAreas.area',
        'prestamoActivo.areaDestino',
        'prestamoActivo.responsable',
        'prestamos.areaOrigen',
        'prestamos.areaDestino',
        'prestamos.responsable',
    ])->find($params['equipo_id']);

    $names = config('CaracteristicsNames');

    $baseCharacteristics = ['marca', 'modelo', 'num_serie', 'estado_funcional'];
    $caracteristicas = $equipo?->caracteristicas?->keyBy('nombre') ?? collect();
    $componentes = $equipo?->componentes ?? collect();
    $additionalCharacteristics = $caracteristicas->reject(function ($item) use ($baseCharacteristics) {
        return in_array($item->nombre, $baseCharacteristics, true);
    });

    $metaFields = [
        ['label' => 'Nombre', 'value' => $equipo->nombre ?? 'Equipo sin nombre'],
        ['label' => 'Marca', 'value' => $caracteristicas->get('marca')?->valor ?? 'N/A'],
        ['label' => 'Modelo', 'value' => $caracteristicas->get('modelo')?->valor ?? 'N/A'],
        ['label' => 'Número de serie', 'value' => $caracteristicas->get('num_serie')?->valor ?? 'N/A'],
        ['label' => 'Código de inventario', 'value' => $equipo->cod_inventario ?? 'N/A'],
        ['label' => 'Estado funcional', 'value' => $caracteristicas->get('estado_funcional')?->valor ?? 'N/A'],
        ['label' => 'Estado', 'value' => $equipo->estado ? ucfirst($equipo->estado) : 'N/A'],
        ['label' => 'Tipo', 'value' => $equipo->tipo ? strtoupper(str_replace('_', ' ', $equipo->tipo)) : 'N/A'],
        ['label' => 'Área Propietaria', 'value' => $equipo->area?->nombre ?? 'N/A'],
        ['label' => 'Ubicación / Préstamo Actual', 'value' => $equipo->prestamoActivo ? ('En préstamo en: ' . ($equipo->prestamoActivo->areaDestino?->nombre ?? 'N/A') . ($equipo->prestamoActivo->responsable ? (' (Resp: ' . $equipo->prestamoActivo->responsable->name . ')') : '')) : 'En custodia de su área propietaria'],
    ];

    $formatValue = function ($value) {
        if ($value === null || $value === '') {
            return 'N/A';
        }

        return is_string($value) ? ucfirst($value) : $value;
    };
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
        border-bottom: 1px solid #9ca3af;
        padding: 0 0 10px 0;
        margin-bottom: 12px;
        background: #ffffff;
    }

    .print-title {
        font-size: 16px;
        font-weight: 700;
        margin: 0;
        color: #111827;
    }

    .brand-mark {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .brand-logo {
        width: 48px;
        height: 48px;
        object-fit: contain;
        flex: 0 0 auto;
    }

    .section-card {
        border: 1px solid #d1d5db;
        margin-bottom: 10px;
        background: #ffffff;
    }

    .section-header {
        background: #f9fafb;
        color: #111827;
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
        grid-template-columns: repeat(3, minmax(0, 1fr));
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
        word-break: break-word;
    }

    .table-print {
        width: 100%;
        border-collapse: collapse;
    }

    .table-print th,
    .table-print td {
        border: 1px solid #d1d5db;
        padding: 5px 8px;
        font-size: 10px;
        vertical-align: top;
    }

    .table-print th {
        background: #f9fafb;
        color: #111827;
        text-align: left;
        font-weight: 700;
    }

    .component-card {
        border: 1px solid #d1d5db;
        margin-bottom: 8px;
        background: #ffffff;
    }

    .component-title {
        background: #f9fafb;
        color: #111827;
        padding: 6px 8px;
        font-size: 10px;
        font-weight: 700;
        border-bottom: 1px solid #d1d5db;
    }

    .component-empty {
        padding: 8px;
        color: #6b7280;
        font-size: 10px;
        font-style: italic;
    }

    .text-muted-print {
        color: #6b7280;
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

        .component-card,
        .info-item,
        tr {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        table {
            page-break-inside: auto;
        }
    }
</style>

<div class="print-sheet container-fluid">
    <div class="print-header">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div class="brand-mark">
                <img src="{{ asset('img/logo_light.png') }}" alt="Logo institucional" class="brand-logo">
                <div class="brand-copy">
                    <h1 class="print-title">{{ $equipo->nombre ?? 'Equipo' }} {{ $equipo->cod_inventario ?? '' }}</h1>
                </div>
            </div>
            <div class="text-end">
                <div class="text-muted-print" style="font-size: 11px;">Generado el</div>
                <div style="font-size: 13px; font-weight: 700;">{{ now()->format('d/m/Y g:i A') }}</div>
                <div class="text-muted-print" style="font-size: 11px; margin-top: 6px;">Generado por</div>
                <div style="font-size: 13px; font-weight: 700;">{{ auth()->user()->name ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="section-header">Datos generales</div>
        <div class="section-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Usuario asignado</span>
                    <div class="info-value">{{ $equipo->usuario?->name ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <span class="info-label">Área</span>
                    <div class="info-value">{{ $equipo->area?->nombre ?? 'N/A' }}</div>
                </div>
                @foreach($metaFields as $field)
                    <div class="info-item">
                        <span class="info-label">{{ $field['label'] }}</span>
                        <div class="info-value">{{ $field['value'] }}</div>
                    </div>
                @endforeach
                <div class="info-item">
                    <span class="info-label">Creado</span>
                    <div class="info-value">{{ $equipo->created_at ? $equipo->created_at->format('d/m/Y g:i A') : 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <span class="info-label">Actualizado</span>
                    <div class="info-value">{{ $equipo->updated_at ? $equipo->updated_at->format('d/m/Y g:i A') : 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>

    @if($additionalCharacteristics->count())
        <div class="section-card">
            <div class="section-header">Características adicionales</div>
            <div class="section-body">
                <table class="table-print">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Nombre</th>
                            <th style="width: 70%;">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($additionalCharacteristics as $caracteristica)
                            <tr>
                                <td style="width: 30%;">{{ $names[$caracteristica->nombre] ?? ucfirst(str_replace('_', ' ', $caracteristica->nombre)) }}</td>
                                <td style="width: 70%;">{{ $formatValue($caracteristica->valor) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="section-card">
        <div class="section-header">Componentes</div>
        <div class="section-body">
            @forelse($componentes as $componente)
                <div class="component-card">
                    <div class="component-title">{{ $names[$componente->nombre] ?? ucfirst(str_replace('_', ' ', $componente->nombre)) }}</div>
                    @if($componente->caracteristicas && $componente->caracteristicas->count())
                        <table class="table-print m-0">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Característica</th>
                                    <th style="width: 70%;">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($componente->caracteristicas as $caracteristica)
                                    <tr>
                                        <td style="width: 30%;">{{ $names[$caracteristica->nombre] ?? ucfirst(str_replace('_', ' ', $caracteristica->nombre)) }}</td>
                                        <td style="width: 70%;">{{ $formatValue($caracteristica->valor) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="component-empty">Este componente no tiene características registradas.</div>
                    @endif
                </div>
            @empty
                <div class="component-empty">El equipo no tiene componentes registrados.</div>
            @endforelse
        </div>
    </div>

    @if($equipo && $equipo->perifericos && $equipo->perifericos->count())
        <div class="section-card">
            <div class="section-header">Periféricos Conectados</div>
            <div class="section-body">
                <table class="table-print m-0">
                    <thead>
                        <tr>
                            <th style="width: 20%;">Tipo</th>
                            <th style="width: 25%;">Marca & Modelo</th>
                            <th style="width: 20%;">N° Serie / Cód. Inv.</th>
                            <th style="width: 15%;">Estado</th>
                            <th style="width: 20%;">Detalles / Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($equipo->perifericos as $periferico)
                            <tr>
                                <td style="font-weight: 700;">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        @if($periferico->imagen)
                                            <img src="{{ asset('storage/' . $periferico->imagen) }}" alt="Foto" style="width: 28px; height: 28px; object-fit: cover; border-radius: 4px; border: 1px solid #d1d5db;">
                                        @endif
                                        <span>{{ ucfirst(str_replace('_', ' ', $periferico->tipo)) }}</span>
                                    </div>
                                </td>
                                <td>{{ $periferico->marca ? ($periferico->marca . ' ' . ($periferico->modelo ?? '')) : ($periferico->modelo ?? 'N/A') }}</td>
                                <td>
                                    @if($periferico->num_serie) S/N: {{ $periferico->num_serie }} @endif
                                    @if($periferico->cod_inventario) <span style="color: #1e40af;">(Inv: {{ $periferico->cod_inventario }})</span> @endif
                                    @if(!$periferico->num_serie && !$periferico->cod_inventario) N/A @endif
                                </td>
                                <td>
                                    <span style="font-weight: 600; text-transform: capitalize;">{{ $periferico->estado }}</span>
                                </td>
                                <td>{{ $periferico->observacion ?: 'Sin observaciones' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($equipo && $equipo->historialAreas && $equipo->historialAreas->count())
        <div class="section-card">
            <div class="section-header">Historial de Áreas Asignadas</div>
            <div class="section-body">
                <table class="table-print m-0">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Área Asignada</th>
                            <th style="width: 20%;">Fecha Asignación</th>
                            <th style="width: 20%;">Fecha Desasignación</th>
                            <th style="width: 35%;">Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($equipo->historialAreas->sortByDesc('fecha_asignacion') as $historial)
                            <tr>
                                <td style="font-weight: 700;">{{ $historial->area?->nombre ?? 'N/A' }}</td>
                                <td>{{ $historial->fecha_asignacion ? \Carbon\Carbon::parse($historial->fecha_asignacion)->format('d/m/Y') : 'N/A' }}</td>
                                <td>
                                    @if($historial->fecha_fin)
                                        {{ \Carbon\Carbon::parse($historial->fecha_fin)->format('d/m/Y') }}
                                    @else
                                        <span style="color: #047857; font-weight: 700;">Actual / Vigente</span>
                                    @endif
                                </td>
                                <td>{{ $historial->observacion ?: 'Sin observaciones' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($equipo && $equipo->prestamos && $equipo->prestamos->count())
        <div class="section-card">
            <div class="section-header">Historial de Préstamos entre Áreas</div>
            <div class="section-body">
                <table class="table-print m-0">
                    <thead>
                        <tr>
                            <th style="width: 12%;">Estado</th>
                            <th style="width: 20%;">Área Prestamista (Dueña)</th>
                            <th style="width: 20%;">Área Receptora (Destino)</th>
                            <th style="width: 18%;">Responsable Receptor</th>
                            <th style="width: 15%;">Fecha Préstamo</th>
                            <th style="width: 15%;">Fecha Devolución</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($equipo->prestamos->sortByDesc('fecha_prestamo') as $prestamo)
                            <tr>
                                <td style="font-weight: 700;">
                                    @if($prestamo->estado === 'activo')
                                        <span style="color: #b45309;">En Préstamo</span>
                                    @else
                                        <span style="color: #047857;">Devuelto</span>
                                    @endif
                                </td>
                                <td>{{ $prestamo->areaOrigen?->nombre ?? 'N/A' }}</td>
                                <td style="font-weight: 600;">{{ $prestamo->areaDestino?->nombre ?? 'N/A' }}</td>
                                <td>{{ $prestamo->responsable?->name ?? 'N/A' }}</td>
                                <td>{{ $prestamo->fecha_prestamo ? \Carbon\Carbon::parse($prestamo->fecha_prestamo)->format('d/m/Y') : 'N/A' }}</td>
                                <td>
                                    @if($prestamo->fecha_devolucion_real)
                                        {{ \Carbon\Carbon::parse($prestamo->fecha_devolucion_real)->format('d/m/Y') }}
                                    @elseif($prestamo->fecha_devolucion_estimada)
                                        <span style="color: #6b7280;">Prev: {{ \Carbon\Carbon::parse($prestamo->fecha_devolucion_estimada)->format('d/m/Y') }}</span>
                                    @else
                                        <span style="color: #b45309;">Pendiente</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>