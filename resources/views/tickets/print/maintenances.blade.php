@php
    use App\Models\Tickets\Mantenimiento;
    use App\Models\Areas\Area;
    use App\Models\User;
    use App\Models\Equipos\Equipo;

    $query = Mantenimiento::with([
        'equipo.area:id,nombre',
        'encargado:id,name,email,especialidad',
        'ticket:id,titulo,estado_actual,tipo,prioridad',
    ]);

    // Filtros de fecha
    if (!empty($params['fecha_inicio'])) {
        $query->whereDate('fecha', '>=', $params['fecha_inicio']);
    }
    if (!empty($params['fecha_fin'])) {
        $query->whereDate('fecha', '<=', $params['fecha_fin']);
    }

    // Filtro de Área (a través del equipo)
    if (!empty($params['id_area'])) {
        $areaId = $params['id_area'];
        $query->whereHas('equipo', function ($q) use ($areaId) {
            $q->where('id_area', $areaId);
        });
    }

    // Filtro de Técnico Encargado
    if (!empty($params['id_encargado'])) {
        $query->where('id_encargado', $params['id_encargado']);
    }

    // Filtro de Equipo
    if (!empty($params['id_equipo'])) {
        $query->where('id_equipo', $params['id_equipo']);
    }

    // Filtro de Tipo de Mantenimiento
    if (!empty($params['tipo']) && $params['tipo'] !== 'all') {
        $query->where('tipo', $params['tipo']);
    }

    $mantenimientos = $query->orderBy('fecha', 'desc')->get();

    // Métricas ITIL para Mantenimiento de Activos
    $totalMantenimientos = $mantenimientos->count();
    $equiposUnicos = $mantenimientos->pluck('id_equipo')->filter()->unique()->count();
    $tecnicosUnicos = $mantenimientos->pluck('id_encargado')->filter()->unique()->count();
    $conTicketCount = $mantenimientos->whereNotNull('id_ticket')->count();
    $preventivosCount = $mantenimientos->where('tipo', 'preventivo')->count();
    $correctivosCount = $mantenimientos->where('tipo', 'correctivo')->count();

    $tipoLabels = [
        'preventivo' => 'Preventivo',
        'correctivo' => 'Correctivo',
        'predictivo' => 'Predictivo',
        'otro' => 'Otro',
    ];

    // Nombres descriptivos de filtros
    $areaFiltrada = !empty($params['id_area']) ? Area::find($params['id_area'])?->nombre : 'Todas las áreas';
    $encargadoFiltrado = !empty($params['id_encargado']) ? User::find($params['id_encargado'])?->name : 'Todos los técnicos';
    $eqObj = !empty($params['id_equipo']) ? Equipo::find($params['id_equipo']) : null;
    $equipoFiltrado = $eqObj ? ($eqObj->nombre ? $eqObj->nombre . ' (' . $eqObj->cod_inventario . ')' : $eqObj->cod_inventario) : 'Todos los equipos';
    $tipoFiltrado = (!empty($params['tipo']) && $params['tipo'] !== 'all') ? ($tipoLabels[$params['tipo']] ?? ucfirst($params['tipo'])) : 'Todos los tipos';
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
        border-bottom: 2px solid #065f46;
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
        color: #065f46;
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

    /* Tarjetas de Métricas */
    .kpi-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
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
        font-size: 8.5px;
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
        background-color: #065f46;
        color: #ffffff;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 8.5px;
        text-align: left;
    }

    .table-print tr:nth-child(even) {
        background-color: #f8fafc;
    }

    /* Bloque de Firmas */
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
                    <h1 class="print-title">Reporte de Mantenimiento</h1>
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
            <span class="filter-label">Técnico:</span>
            <span class="filter-val">{{ $encargadoFiltrado }}</span>
        </div>
        <div class="filter-item">
            <span class="filter-label">Equipo:</span>
            <span class="filter-val">{{ $equipoFiltrado }}</span>
        </div>
        <div class="filter-item">
            <span class="filter-label">Tipo:</span>
            <span class="filter-val">{{ $tipoFiltrado }}</span>
        </div>
    </div>

    <!-- Resumen Ejecutivo de Métricas -->
    <div class="kpi-container">
        <div class="kpi-card">
            <div class="kpi-title">Mantenimientos Ejecutados</div>
            <div class="kpi-value" style="color: #065f46;">{{ $totalMantenimientos }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Activos / Equipos Intervenidos</div>
            <div class="kpi-value" style="color: #2563eb;">{{ $equiposUnicos }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Preventivos / Correctivos</div>
            <div class="kpi-value" style="color: #7c3aed; font-size: 13px;">{{ $preventivosCount }} Prev / {{ $correctivosCount }} Corr</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Derivados de Ticket</div>
            <div class="kpi-value" style="color: #059669;">{{ $conTicketCount }}</div>
        </div>
    </div>

    <!-- Tabla Detallada de Mantenimientos -->
    <table class="table-print">
        <thead>
            <tr>
                <th style="width: 5%;">#ID</th>
                <th style="width: 10%;">Fecha / Hora</th>
                <th style="width: 10%; text-align: center;">Tipo</th>
                <th style="width: 11%;">Cód. Inventario</th>
                <th style="width: 16%;">Nombre del Equipo</th>
                <th style="width: 12%;">Área / Ubicación</th>
                <th style="width: 13%;">Técnico Responsable</th>
                <th style="width: 7%;">Ticket Ref.</th>
                <th style="width: 16%;">Detalle / Trabajo Realizado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mantenimientos as $mnt)
                @php
                    $equipo = $mnt->equipo;
                    $area = $equipo?->area?->nombre ?? 'Área N/A';
                    $tipo = $mnt->tipo ?? 'preventivo';
                    $tipoStyle = match($tipo) {
                        'preventivo' => 'background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;',
                        'correctivo' => 'background: #fef3c7; color: #92400e; border: 1px solid #fde68a;',
                        'predictivo' => 'background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe;',
                        default => 'background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;'
                    };
                    $tipoShort = match($tipo) {
                        'preventivo' => 'Preventivo',
                        'correctivo' => 'Correctivo',
                        'predictivo' => 'Predictivo',
                        default => ucfirst(substr($tipo, 0, 4))
                    };
                @endphp
                <tr>
                    <td style="font-weight: 700; text-align: center; width: 5%;">#{{ $mnt->id }}</td>
                    <td style="font-size: 8.5px; width: 10%;">{{ \Carbon\Carbon::parse($mnt->fecha)->format('d/m/Y H:i') }}</td>
                    <td style="text-align: center; width: 10%;">
                        <span style="display: inline-block; padding: 1px 4px; font-size: 7.5px; font-weight: 700; border-radius: 3px; text-transform: uppercase; {{ $tipoStyle }}">
                            {{ $tipoShort }}
                        </span>
                    </td>
                    <td style="font-weight: 600; color: #1e40af; width: 11%;">{{ $equipo?->cod_inventario ?? 'S/C' }}</td>
                    <td style="width: 16%;">
                        <div style="font-weight: 700; color: #0f172a;">{{ $equipo?->nombre ?? 'Equipo no especificado' }}</div>
                    </td>
                    <td style="width: 16%;">
                        {{ $area }}
                    </td>
                    <td style="width: 16%;">
                        <div style="font-weight: 600;">{{ $mnt->encargado?->name ?? 'Técnico no asignado' }}</div>
                        @if($mnt->encargado?->especialidad)
                            <div style="font-size: 8px; color: #64748b;">{{ $mnt->encargado->especialidad }}</div>
                        @endif
                    </td>
                    <td style="text-align: center; width: 16%;">
                        @if($mnt->ticket)
                            <span style="font-weight: 700; color: #2563eb;">#{{ $mnt->ticket->id }}</span>
                        @else
                            <span style="color: #94a3b8; font-size: 8px;">Directo</span>
                        @endif
                    </td>
                    <td style="width: 16%;">
                        <div style="font-size: 8.5px; color: #334155;">
                            {{ $mnt->observacion ?: 'Mantenimiento preventivo/correctivo ejecutado satisfactoriamente.' }}
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 15px; color: #64748b; font-style: italic;">
                        No se encontraron registros de mantenimientos que coincidan con los criterios y filtros especificados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

