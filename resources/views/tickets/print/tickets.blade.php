@php
    use App\Models\Tickets\Ticket;
    use App\Models\Areas\Area;
    use App\Models\User;
    use App\Models\Equipos\Equipo;

    $query = Ticket::with([
        'usuario:id,name,email,telefono',
        'encargado:id,name,email,especialidad',
        'equipo.area:id,nombre',
        'programacionMantenimiento.area:id,nombre',
        'mantenimientos',
    ]);

    // Filtros de fecha
    if (!empty($params['fecha_inicio'])) {
        $query->whereDate('fecha_creacion', '>=', $params['fecha_inicio']);
    }
    if (!empty($params['fecha_fin'])) {
        $query->whereDate('fecha_creacion', '<=', $params['fecha_fin']);
    }

    // Filtro de Área
    if (!empty($params['id_area'])) {
        $areaId = $params['id_area'];
        $query->where(function ($q) use ($areaId) {
            $q->whereHas('equipo', function ($eq) use ($areaId) {
                $eq->where('id_area', $areaId);
            })->orWhereHas('programacionMantenimiento', function ($pm) use ($areaId) {
                $pm->where('id_area', $areaId);
            });
        });
    }

    // Filtro de Solicitante (Usuario)
    if (!empty($params['id_usuario'])) {
        $query->where('id_usuario', $params['id_usuario']);
    }

    // Filtro de Técnico Encargado
    if (!empty($params['id_encargado'])) {
        $query->where('id_encargado', $params['id_encargado']);
    }

    // Filtro de Equipo
    if (!empty($params['id_equipo'])) {
        $query->where('id_equipo', $params['id_equipo']);
    }

    // Filtro de Estado
    if (!empty($params['estado_actual']) && $params['estado_actual'] !== 'all') {
        $query->where('estado_actual', $params['estado_actual']);
    }

    // Filtro de Prioridad
    if (!empty($params['prioridad']) && $params['prioridad'] !== 'all') {
        $query->where('prioridad', $params['prioridad']);
    }

    // Filtro de Tipo
    if (!empty($params['tipo']) && $params['tipo'] !== 'all') {
        $query->where('tipo', $params['tipo']);
    }

    $tickets = $query->orderBy('fecha_creacion', 'desc')->get();

    // Métricas ITIL para Resumen Ejecutivo
    $totalTickets = $tickets->count();
    $resueltosCount = $tickets->whereIn('estado_actual', ['resuelto', 'cerrado'])->count();
    $enProcesoCount = $tickets->where('estado_actual', 'en_proceso')->count();
    $abiertosCount = $tickets->where('estado_actual', 'abierto')->count();
    $canceladosCount = $tickets->where('estado_actual', 'cancelado')->count();
    $criticosCount = $tickets->where('prioridad', 'critica')->count();
    $tasaResolucion = $totalTickets > 0 ? round(($resueltosCount / $totalTickets) * 100, 1) : 0;

    // Etiquetas legibles
    $tipoLabels = [
        'hardware' => 'Hardware',
        'software' => 'Software',
        'network' => 'Redes/Comunicaciones',
        'preventivo' => 'Preventivo Planificado',
        'correctivo' => 'Correctivo Emergente',
        'other' => 'Otro / Consulta',
    ];

    $prioridadLabels = [
        'baja' => 'Baja',
        'media' => 'Media',
        'alta' => 'Alta',
        'critica' => 'Crítica',
    ];

    $estadoLabels = [
        'abierto' => 'Abierto',
        'en_proceso' => 'En Proceso',
        'resuelto' => 'Resuelto',
        'cerrado' => 'Cerrado',
        'cancelado' => 'Cancelado',
    ];

    // Cargar nombres de entidades filtradas para el encabezado
    $areaFiltrada = !empty($params['id_area']) ? Area::find($params['id_area'])?->nombre : 'Todas las áreas';
    $usuarioFiltrado = !empty($params['id_usuario']) ? User::find($params['id_usuario'])?->name : 'Todos los solicitantes';
    $encargadoFiltrado = !empty($params['id_encargado']) ? User::find($params['id_encargado'])?->name : 'Todos los técnicos';
    $equipoFiltrado = !empty($params['id_equipo']) ? Equipo::find($params['id_equipo'])?->nombre : 'Todos los equipos';
@endphp

<style>
    .print-sheet {
        font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
        color: #111827;
        line-height: 1.25;
        background: #ffffff;
        padding: 0;
    }

    .print-header {
        border-bottom: 2px solid #1e3a8a;
        padding-bottom: 8px;
        margin-bottom: 10px;
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
    }

    .print-title {
        font-size: 15px;
        font-weight: 800;
        margin: 0;
        color: #1e3a8a;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .print-subtitle {
        font-size: 10px;
        color: #4b5563;
        margin-top: 1px;
    }

    .doc-meta {
        font-size: 9px;
        text-align: right;
        color: #4b5563;
    }

    .doc-meta strong {
        color: #111827;
    }

    /* Tarjetas de Métricas ITIL */
    .kpi-container {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 6px;
        margin-bottom: 10px;
    }

    .kpi-card {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 6px 8px;
        text-align: center;
    }

    .kpi-title {
        font-size: 8px;
        text-transform: uppercase;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 2px;
    }

    .kpi-value {
        font-size: 14px;
        font-weight: 800;
        color: #0f172a;
    }

    /* Caja de Criterios y Filtros */
    .filter-badge-box {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        padding: 6px 10px;
        margin-bottom: 10px;
        font-size: 9px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .filter-item {
        display: flex;
        gap: 4px;
    }

    .filter-label {
        font-weight: 700;
        color: #475569;
    }

    .filter-val {
        color: #0f172a;
    }

    /* Tabla de Datos */
    .table-print {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    .table-print th,
    .table-print td {
        border: 1px solid #cbd5e1;
        padding: 4px 6px;
        font-size: 9px;
        vertical-align: middle;
    }

    .table-print th {
        background-color: #1e3a8a;
        color: #ffffff;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 8.5px;
        text-align: left;
    }

    .table-print tr:nth-child(even) {
        background-color: #f8fafc;
    }

    /* Insignias de Estado y Prioridad */
    .badge-status {
        display: inline-block;
        padding: 1px 4px;
        border-radius: 3px;
        font-size: 8px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .status-abierto { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
    .status-en_proceso { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .status-resuelto { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .status-cerrado { background: #e2e8f0; color: #334155; border: 1px solid #cbd5e1; }
    .status-cancelado { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    .badge-prio {
        display: inline-block;
        padding: 1px 4px;
        border-radius: 3px;
        font-size: 8px;
        font-weight: 700;
    }
    .prio-baja { color: #047857; }
    .prio-media { color: #b45309; }
    .prio-alta { color: #c2410c; }
    .prio-critica { color: #b91c1c; font-weight: 800; }

    /* Bloque de Firmas de Conformidad */
    .signatures-block {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        margin-top: 30px;
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
            font-size: 9px;
            background: #ffffff;
            padding: 0;
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
    <!-- Encabezado Institucional -->
    <div class="print-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="brand-mark">
                <img src="{{ asset('img/logo_light.png') }}" alt="Logo institucional" class="brand-logo">
                <div>
                    <h1 class="print-title">Reporte Consolidado de Gestión de Tickets</h1>
                </div>
            </div>
            <div class="doc-meta">
                <div><strong>Emisión:</strong> {{ now()->format('d/m/Y h:i A') }}</div>
                <div><strong>Emitido por:</strong> {{ auth()->user()->name ?? 'Sistema TI' }}</div>
            </div>
        </div>
    </div>

    <!-- Criterios de Selección / Filtros Aplicados -->
    <div class="filter-badge-box">
        <div class="filter-item">
            <span class="filter-label">Período:</span>
            <span class="filter-val">
                @if(!empty($params['fecha_inicio']) && !empty($params['fecha_fin']))
                    {{ \Carbon\Carbon::parse($params['fecha_inicio'])->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($params['fecha_fin'])->format('d/m/Y') }}
                @elseif(!empty($params['fecha_inicio']))
                    Desde {{ \Carbon\Carbon::parse($params['fecha_inicio'])->format('d/m/Y') }}
                @elseif(!empty($params['fecha_fin']))
                    Hasta {{ \Carbon\Carbon::parse($params['fecha_fin'])->format('d/m/Y') }}
                @else
                    Historial Completo
                @endif
            </span>
        </div>
        <div class="filter-item">
            <span class="filter-label">Área:</span>
            <span class="filter-val">{{ $areaFiltrada }}</span>
        </div>
        <div class="filter-item">
            <span class="filter-label">Solicitante:</span>
            <span class="filter-val">{{ $usuarioFiltrado }}</span>
        </div>
        <div class="filter-item">
            <span class="filter-label">Técnico:</span>
            <span class="filter-val">{{ $encargadoFiltrado }}</span>
        </div>
        <div class="filter-item">
            <span class="filter-label">Equipo:</span>
            <span class="filter-val">{{ $equipoFiltrado }}</span>
        </div>
        <div class="filter-item">
            <span class="filter-label">Estado:</span>
            <span class="filter-val">{{ !empty($params['estado_actual']) && $params['estado_actual'] !== 'all' ? ($estadoLabels[$params['estado_actual']] ?? $params['estado_actual']) : 'Todos' }}</span>
        </div>
    </div>

    <!-- Resumen Ejecutivo de Métricas (ITIL KPIs) -->
    <div class="kpi-container">
        <div class="kpi-card">
            <div class="kpi-title">Total Solicitudes</div>
            <div class="kpi-value">{{ $totalTickets }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Tasa de Resolución</div>
            <div class="kpi-value" style="color: #059669;">{{ $tasaResolucion }}%</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Resueltos / Cerrados</div>
            <div class="kpi-value" style="color: #047857;">{{ $resueltosCount }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">En Atención</div>
            <div class="kpi-value" style="color: #d97706;">{{ $enProcesoCount }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Pendientes / Abiertos</div>
            <div class="kpi-value" style="color: #2563eb;">{{ $abiertosCount }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Incidentes Críticos</div>
            <div class="kpi-value" style="color: #dc2626;">{{ $criticosCount }}</div>
        </div>
    </div>

    <!-- Tabla Detallada de Tickets -->
    <table class="table-print">
        <thead>
            <tr>
                <th style="width: 5%;">#ID</th>
                <th style="width: 10%;">Fecha Reg.</th>
                <th style="width: 11%;">Categoría / Tipo</th>
                <th style="width: 8%;">Prioridad</th>
                <th style="width: 22%;">Título & Detalle</th>
                <th style="width: 14%;">Solicitante & Área</th>
                <th style="width: 12%;">Técnico Responsable</th>
                <th style="width: 8%;">Estado</th>
                <th style="width: 10%;">Resolución</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $tkt)
                @php
                    $areaNombre = $tkt->equipo?->area?->nombre ?? $tkt->programacionMantenimiento?->area?->nombre ?? 'Área N/A';
                    $equipoInfo = $tkt->equipo ? " ({$tkt->equipo->cod_inventario})" : '';
                @endphp
                <tr>
                    <td style="font-weight: 700; text-align: center; width: 5%;">#{{ $tkt->id }}</td>
                    <td style="font-size: 8.5px; width: 10%;">{{ \Carbon\Carbon::parse($tkt->fecha_creacion)->format('d/m/Y H:i') }}</td>
                    <td style="width: 11%;">{{ $tipoLabels[$tkt->tipo] ?? ucfirst($tkt->tipo) }}</td>
                    <td style="width: 8%;">
                        <span class="badge-prio prio-{{ $tkt->prioridad }}">
                            ● {{ $prioridadLabels[$tkt->prioridad] ?? ucfirst($tkt->prioridad) }}
                        </span>
                    </td>
                    <td style="width: 22%;">
                        <div style="font-weight: 700; color: #0f172a;">{{ $tkt->titulo }}</div>
                        @if($tkt->descripcion)
                            <div style="font-size: 8px; color: #64748b; margin-top: 1px;">
                                {{ Str::limit($tkt->descripcion, 75) }}
                            </div>
                        @endif
                        @if($tkt->equipo)
                            <div style="font-size: 8px; color: #2563eb; margin-top: 1px;">
                                🖥️ {{ $tkt->equipo->nombre }}{{ $equipoInfo }}
                            </div>
                        @endif
                    </td>
                    <td style="width: 14%;">
                        <div style="font-weight: 600;">{{ $tkt->usuario?->name ?? 'N/A' }}</div>
                        <div style="font-size: 8px; color: #64748b;">{{ $areaNombre }}</div>
                    </td>
                    <td style="width: 12%;">
                        {{ $tkt->encargado?->name ?? 'Sin asignar' }}
                        @if($tkt->encargado?->especialidad)
                            <div style="font-size: 8px; color: #64748b;">{{ $tkt->encargado->especialidad }}</div>
                        @endif
                    </td>
                    <td style="text-align: center; width: 8%;">
                        <span class="badge-status status-{{ $tkt->estado_actual }}">
                            {{ $estadoLabels[$tkt->estado_actual] ?? $tkt->estado_actual }}
                        </span>
                    </td>
                    <td style="width: 10%;">
                        @if($tkt->fecha_resolucion)
                            <div style="font-size: 8px; font-weight: 600; color: #047857;">
                                {{ \Carbon\Carbon::parse($tkt->fecha_resolucion)->format('d/m/Y H:i') }}
                            </div>
                        @endif
                        @if($tkt->observacion_resolucion)
                            <div style="font-size: 8px; color: #475569; font-style: italic;">
                                {{ Str::limit($tkt->observacion_resolucion, 60) }}
                            </div>
                        @elseif(!$tkt->fecha_resolucion)
                            <span style="color: #94a3b8; font-size: 8px;">En atención</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 15px; color: #64748b; font-style: italic;">
                        No se encontraron registros de tickets que coincidan con los criterios y filtros especificados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

