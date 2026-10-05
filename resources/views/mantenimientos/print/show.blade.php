@php
	$planificacion = App\Models\Tickets\PlanificacionMantenimiento::with([
		'area',
		'tickets.equipo',
		'tickets.usuario',
		'tickets.encargado',
	])->find($params['planificacion_id'] ?? null);

	$ticketsPorFecha = collect();

	if ($planificacion) {
		$ticketsPorFecha = $planificacion->tickets
			->sortBy('fecha_planificada')
			->groupBy(function ($ticket) {
				if (!$ticket->fecha_planificada) {
					return 'Sin fecha planificada';
				}

				return \Carbon\Carbon::parse($ticket->fecha_planificada)->format('d/m/Y');
			});
	}

	$rangos = [
		'inicio' => $planificacion?->fecha_inicio
			? \Carbon\Carbon::parse($planificacion->fecha_inicio)->format('d/m/Y')
			: 'N/A',
		'fin' => $planificacion?->fecha_fin
			? \Carbon\Carbon::parse($planificacion->fecha_fin)->format('d/m/Y')
			: 'N/A',
	];
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

	.date-title {
		font-size: 12px;
		font-weight: 700;
		color: #111827;
		margin: 0 0 6px 0;
		border-bottom: 1px dashed #d1d5db;
		padding-bottom: 4px;
	}

	.date-group {
		margin-bottom: 10px;
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
		text-transform: uppercase;
		font-size: 9px;
	}

	.empty-message {
		padding: 8px;
		color: #6b7280;
		font-size: 10px;
		font-style: italic;
		border: 1px solid #e5e7eb;
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

		.section-card,
		.section-body,
		.date-group {
			break-inside: auto !important;
			page-break-inside: auto !important;
		}

		.date-title {
			break-after: avoid !important;
			page-break-after: avoid !important;
		}

		.info-item,
		tr,
		td,
		th {
			break-inside: avoid !important;
			page-break-inside: avoid !important;
		}

		thead {
			display: table-header-group;
		}

		tfoot {
			display: table-footer-group;
		}
	}
</style>

<div class="print-sheet container-fluid">
	<div class="print-header">
		<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
			<div class="brand-mark">
				<img src="{{ asset('img/logo_light.png') }}" alt="Logo institucional" class="brand-logo">
				<div class="brand-copy">
					<h1 class="print-title">Planificación de mantenimiento</h1>
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
					<span class="info-label">Area</span>
					<div class="info-value">{{ $planificacion?->area?->nombre ?? 'N/A' }}</div>
				</div>
				<div class="info-item">
					<span class="info-label">Fecha de inicio</span>
					<div class="info-value">{{ $rangos['inicio'] }}</div>
				</div>
				<div class="info-item">
					<span class="info-label">Fecha de fin</span>
					<div class="info-value">{{ $rangos['fin'] }}</div>
				</div>
				<div class="info-item">
					<span class="info-label">Total de tickets</span>
					<div class="info-value">{{ $planificacion?->tickets?->count() ?? 0 }}</div>
				</div>
				<div class="info-item">
					<span class="info-label">Creado</span>
					<div class="info-value">{{ $planificacion?->created_at ? $planificacion->created_at->format('d/m/Y g:i A') : 'N/A' }}</div>
				</div>
				<div class="info-item">
					<span class="info-label">Actualizado</span>
					<div class="info-value">{{ $planificacion?->updated_at ? $planificacion->updated_at->format('d/m/Y g:i A') : 'N/A' }}</div>
				</div>
			</div>
		</div>
	</div>

	<div class="section-card">
		<div class="section-header">Tickets por fecha planificada</div>
		<div class="section-body">
            @php
                $index = 0;
            @endphp
			@forelse($ticketsPorFecha as $fecha => $tickets)
				<div class="date-group">
					<p class="date-title">{{ $fecha }}</p>
					<table class="table-print">
						<thead>
							<tr>
                                <th style="width: 5%;">#</th>
								<th style="width: 35%;">Equipo</th>
								<th style="width: 15%;">Estado</th>
								<th style="width: 22.5%;">Usuario</th>
								<th style="width: 22.5%;">Encargado</th>
							</tr>
						</thead>
						<tbody>
							@foreach($tickets as $ticket)
								<tr>
                                    <td style="width: 5%;">{{ ++$index }}</td>
									<td style="width: 35%;">{{ $ticket->equipo?->nombre ?? $ticket->equipo?->cod_inventario ?? 'No asignado' }}</td>
									<td style="width: 15%;">{{ ucfirst(str_replace('_', ' ', $ticket->estado_actual ?? 'N/A')) }}</td>
									<td style="width: 22.5%;">{{ $ticket->usuario?->name ?? 'No asignado' }}</td>
									<td style="width: 22.5%;">{{ $ticket->encargado?->name ?? 'No asignado' }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			@empty
				<div class="empty-message">No hay tickets asociados a esta planificación.</div>
			@endforelse
		</div>
	</div>
</div>
