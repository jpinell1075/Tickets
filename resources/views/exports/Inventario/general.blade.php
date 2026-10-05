<html>
    <tr></tr>
    <tr></tr>
    <tr></tr>
    <tr></tr>
    <tr></tr>
    <tr></tr>
    <tr></tr>
    <tr></tr>
    <tr></tr>
    <tr></tr>
    <tr></tr>
    <tr>
        <td style="border: 1px solid black;">
            <span style="text-align: start; font-size: 11pt;">CENTRO TECNOLOGICO :</span>
        </td>
        <td style="text-align: start; font-size: 11pt; border: 1px solid black;" colspan="4">
            
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid black;">
            <span style="text-align: start; font-size: 11pt;">RESPONSABLE DE SOPORTE DEL CENTRO</span>
        </td>
        <td style="text-align: start; font-size: 11pt; border: 1px solid black;" colspan="4">
            
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid black;">
            <span style="text-align: start; font-size: 11pt;">CORREO INSTITUCIONAL</span>
        </td>
        <td style="text-align: start; font-size: 11pt; border: 1px solid black;" colspan="4">
            
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid black;">
            <span style="text-align: start; font-size: 11pt;">CELULAR:</span>
        </td>
        <td style="text-align: start; font-size: 11pt; border: 1px solid black;" colspan="4">
            
        </td>
    </tr>

    <!-- CPUS -->
    <tr>
        <td style="border: 1px solid black; text-align: center;" colspan="11">
            <span style="font-size: 20pt;">CPU</span>
        </td>
        <td style="border: 1px solid black; text-align: center;" colspan="2">
            <span style="font-size: 11pt;">ESTADO</span>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">UBICACIÓN</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MARCA</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MODELO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">NUMERO DE SERIE</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">CÓDIGO DE INVENTARIO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">PROCESADOR</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">DISCO DURO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MEMORIA RAM</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">TARJETA INALAMBRICA</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">TARJETA DE VIDEO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">SISTEMA OPERATIVO (VERSION X32,X64)</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">FUNCIONAL</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">NO FUNCIONAL</span>
        </td>
    </tr>
    @if(isset($equipos['cpu']) && $equipos['cpu']->count() > 0)
        @foreach ($equipos['cpu'] as $equipo)
            <tr>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->area?->nombre ?? 'N/A' }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('marca')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('modelo')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('num_serie')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->cod_inventario }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->componentes->firstWhere('nombre', 'cpu')?->caracteristicas->get('cpu_model')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->componentes->firstWhere('nombre', 'storage')?->caracteristicas->get('storage_type')?->valor ?? '' }}
                        {{ $equipo->componentes->firstWhere('nombre', 'storage')?->caracteristicas->get('storage_capacity')?->valor ?? '' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->componentes->firstWhere('nombre', 'ram')?->caracteristicas->get('ram_type')?->valor ?? '' }}
                        {{ $equipo->componentes->firstWhere('nombre', 'ram')?->caracteristicas->get('ram_capacity')?->valor ?? '' }}
                        {{ $equipo->componentes->firstWhere('nombre', 'ram')?->caracteristicas->get('ram_speed')?->valor ?? '' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->componentes->firstWhere('nombre', 'wifi_card')?->caracteristicas->get('wifi_card_model')?->valor ?? '' }}
                        {{ $equipo->componentes->firstWhere('nombre', 'wifi_card')?->caracteristicas->get('wifi_card_speed')?->valor ?? '' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->componentes->firstWhere('nombre', 'gpu')?->caracteristicas->get('gpu_model')?->valor ?? '' }}
                        {{ $equipo->componentes->firstWhere('nombre', 'gpu')?->caracteristicas->get('gpu_memory')?->valor ?? '' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('operative_system')?->valor ?? '' }}
                        {{ $equipo->caracteristicas->get('operative_system_arquitecture')?->valor ?? '' }}
                        {{ $equipo->caracteristicas->get('operative_system_version')?->valor ?? '' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->caracteristicas->get('estado_funcional')?->valor === 'funcional' ? 'X' : '' }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->caracteristicas->get('estado_funcional')?->valor === 'no_funcional' ? 'X' : '' }}</span>
                </td>
            </tr>
        @endforeach
    @else
        <tr>
            <td style="border: 1px solid black; text-align: center;" colspan="13">
                <span style="font-size: 11pt;"></span>
            </td>
        </tr>
    @endif

    <!-- MONITORES -->
    <tr>
        <td style="border: 1px solid black; text-align: center;" colspan="7">
            <span style="font-size: 20pt;">MONITOR</span>
        </td>
        <td style="border: 1px solid black; text-align: center;" colspan="2">
            <span style="font-size: 11pt;">ESTADO</span>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">UBICACIÓN</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MARCA</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MODELO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">TAMAÑO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">NUMERO DE SERIE</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">CÓDIGO DE INVENTARIO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">COLOR</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">FUNCIONAL</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">NO FUNCIONAL</span>
        </td>
    </tr>
    @if(isset($equipos['monitor']) && $equipos['monitor']->count() > 0)
        @foreach ($equipos['monitor'] as $equipo)
            <tr>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->area?->nombre ?? 'N/A' }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('marca')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('modelo')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('size')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('num_serie')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->cod_inventario }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('color')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->caracteristicas->get('estado_funcional')?->valor === 'funcional' ? 'X' : '' }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->caracteristicas->get('estado_funcional')?->valor === 'no_funcional' ? 'X' : '' }}</span>
                </td>
        @endforeach
    @else
        <tr>
            <td style="border: 1px solid black; text-align: center;" colspan="9">
                <span style="font-size: 11pt;"></span>
            </td>
        </tr>
    @endif

    <!-- UPS -->
    <tr>
        <td style="border: 1px solid black; text-align: center;" colspan="7">
            <span style="font-size: 20pt;">BATERIAS UPS / ESTABILIZADOR</span>
        </td>
        <td style="border: 1px solid black; text-align: center;" colspan="2">
            <span style="font-size: 11pt;">ESTADO</span>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">UBICACIÓN</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MARCA</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MODELO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">TAMAÑO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">NUMERO DE SERIE</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">CÓDIGO DE INVENTARIO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">COLOR</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">FUNCIONAL</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">NO FUNCIONAL</span>
        </td>
    </tr>
    @if(isset($equipos['ups']) && $equipos['ups']->count() > 0)
        @foreach ($equipos['ups'] as $equipo)
            <tr>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->area?->nombre ?? 'N/A' }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('marca')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('modelo')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('size')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('num_serie')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->cod_inventario }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('color')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->caracteristicas->get('estado_funcional')?->valor === 'funcional' ? 'X' : '' }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->caracteristicas->get('estado_funcional')?->valor === 'no_funcional' ? 'X' : '' }}</span>
                </td>
        @endforeach
    @else
        <tr>
            <td style="border: 1px solid black; text-align: center;" colspan="9">
                <span style="font-size: 11pt;"></span>
            </td>
        </tr>
    @endif

    <!-- switches -->
    <tr>
        <td style="border: 1px solid black; text-align: center;" colspan="7">
            <span style="font-size: 20pt;">SWITCHES</span>
        </td>
        <td style="border: 1px solid black; text-align: center;" colspan="2">
            <span style="font-size: 11pt;">ESTADO</span>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">UBICACIÓN</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MARCA</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MODELO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">NUMERO DE SERIE</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">CÓDIGO DE INVENTARIO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;"># DE PUERTOS</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">FIBRA CANAL (FIBRA O LAN)</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">FUNCIONAL</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">NO FUNCIONAL</span>
        </td>
    </tr>
    @if(isset($equipos['switches']) && $equipos['switches']->count() > 0)
        @foreach ($equipos['switches'] as $equipo)
            <tr>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->area?->nombre ?? 'N/A' }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('marca')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('modelo')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('num_serie')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->cod_inventario }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('ports_number')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('switch_canal')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->caracteristicas->get('estado_funcional')?->valor === 'funcional' ? 'X' : '' }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->caracteristicas->get('estado_funcional')?->valor === 'no_funcional' ? 'X' : '' }}</span>
                </td>
        @endforeach
    @else
        <tr>
            <td style="border: 1px solid black; text-align: center;" colspan="9">
                <span style="font-size: 11pt;"></span>
            </td>
        </tr>
    @endif

    <!-- aps -->
    <tr>
        <td style="border: 1px solid black; text-align: center;" colspan="5">
            <span style="font-size: 20pt;">CONEXIÓN INALÁMBRICA (AP´S)</span>
        </td>
        <td style="border: 1px solid black; text-align: center;" colspan="2">
            <span style="font-size: 11pt;">ESTADO</span>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">UBICACIÓN</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MARCA</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MODELO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">NUMERO DE SERIE</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">CÓDIGO DE INVENTARIO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">FUNCIONAL</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">NO FUNCIONAL</span>
        </td>
    </tr>
    @if(isset($equipos['aps']) && $equipos['aps']->count() > 0)
        @foreach ($equipos['aps'] as $equipo)
            <tr>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->area?->nombre ?? 'N/A' }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('marca')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('modelo')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('num_serie')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->cod_inventario }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->caracteristicas->get('estado_funcional')?->valor === 'funcional' ? 'X' : '' }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->caracteristicas->get('estado_funcional')?->valor === 'no_funcional' ? 'X' : '' }}</span>
                </td>
        @endforeach
    @else
        <tr>
            <td style="border: 1px solid black; text-align: center;" colspan="7">
                <span style="font-size: 11pt;"></span>
            </td>
        </tr>
    @endif

    <!-- telefonia_ip -->
    <tr>
        <td style="border: 1px solid black; text-align: center;" colspan="6">
            <span style="font-size: 20pt;">TELEFONIA IP</span>
        </td>
        <td style="border: 1px solid black; text-align: center;" colspan="2">
            <span style="font-size: 11pt;">ESTADO</span>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">UBICACIÓN</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MARCA</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MODELO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">NUMERO DE SERIE</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">CÓDIGO DE INVENTARIO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MAC-ADDRESS</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">FUNCIONAL</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">NO FUNCIONAL</span>
        </td>
    </tr>
    @if(isset($equipos['telefonia_ip']) && $equipos['telefonia_ip']->count() > 0)
        @foreach ($equipos['telefonia_ip'] as $equipo)
            <tr>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->area?->nombre ?? 'N/A' }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('marca')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('modelo')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('num_serie')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->cod_inventario }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    {{ $equipo->caracteristicas->get('mac_address')?->valor ?? 'N/A' }}
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->caracteristicas->get('estado_funcional')?->valor === 'funcional' ? 'X' : '' }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->caracteristicas->get('estado_funcional')?->valor === 'no_funcional' ? 'X' : '' }}</span>
                </td>
        @endforeach
    @else
        <tr>
            <td style="border: 1px solid black; text-align: center;" colspan="8">
                <span style="font-size: 11pt;"></span>
            </td>
        </tr>
    @endif

    <!-- impresoras -->
    <tr>
        <td style="border: 1px solid black; text-align: center;" colspan="5">
            <span style="font-size: 20pt;">IMPRESORA, FOTOCOPIADORA (IMPRESORAS MATRICIALES)</span>
        </td>
        <td style="border: 1px solid black; text-align: center;" colspan="2">
            <span style="font-size: 11pt;">ESTADO</span>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">UBICACIÓN</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MARCA</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MODELO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">NUMERO DE SERIE</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">CÓDIGO DE INVENTARIO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">FUNCIONAL</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">NO FUNCIONAL</span>
        </td>
    </tr>
    @if(isset($equipos['impresoras']) && $equipos['impresoras']->count() > 0)
        @foreach ($equipos['impresoras'] as $equipo)
            <tr>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->area?->nombre ?? 'N/A' }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('marca')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('modelo')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('num_serie')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->cod_inventario }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->caracteristicas->get('estado_funcional')?->valor === 'funcional' ? 'X' : '' }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->caracteristicas->get('estado_funcional')?->valor === 'no_funcional' ? 'X' : '' }}</span>
                </td>
        @endforeach
    @else
        <tr>
            <td style="border: 1px solid black; text-align: center;" colspan="7">
                <span style="font-size: 11pt;"></span>
            </td>
        </tr>
    @endif

    <!-- reloj_biometrico -->
    <tr>
        <td style="border: 1px solid black; text-align: center;" colspan="5">
            <span style="font-size: 20pt;">RELOJ BIOMETRICO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;" colspan="2">
            <span style="font-size: 11pt;">ESTADO</span>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">UBICACIÓN</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MARCA</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MODELO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">NUMERO DE SERIE</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">CÓDIGO DE INVENTARIO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">FUNCIONAL</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">NO FUNCIONAL</span>
        </td>
    </tr>
    @if(isset($equipos['reloj_biometrico']) && $equipos['reloj_biometrico']->count() > 0)
        @foreach ($equipos['reloj_biometrico'] as $equipo)
            <tr>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->area?->nombre ?? 'N/A' }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('marca')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('modelo')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('num_serie')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->cod_inventario }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->caracteristicas->get('estado_funcional')?->valor === 'funcional' ? 'X' : '' }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->caracteristicas->get('estado_funcional')?->valor === 'no_funcional' ? 'X' : '' }}</span>
                </td>
        @endforeach
    @else
        <tr>
            <td style="border: 1px solid black; text-align: center;" colspan="7">
                <span style="font-size: 11pt;"></span>
            </td>
        </tr>
    @endif

    <!-- data_show -->
    <tr>
        <td style="border: 1px solid black; text-align: center;" colspan="5">
            <span style="font-size: 20pt;">DATA SHOW</span>
        </td>
        <td style="border: 1px solid black; text-align: center;" colspan="2">
            <span style="font-size: 11pt;">ESTADO</span>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">UBICACIÓN</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MARCA</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MODELO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">NUMERO DE SERIE</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">CÓDIGO DE INVENTARIO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">FUNCIONAL</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">NO FUNCIONAL</span>
        </td>
    </tr>
    @if(isset($equipos['data_show']) && $equipos['data_show']->count() > 0)
        @foreach ($equipos['data_show'] as $equipo)
            <tr>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->area?->nombre ?? 'N/A' }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('marca')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('modelo')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('num_serie')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->cod_inventario }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->caracteristicas->get('estado_funcional')?->valor === 'funcional' ? 'X' : '' }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->caracteristicas->get('estado_funcional')?->valor === 'no_funcional' ? 'X' : '' }}</span>
                </td>
        @endforeach
    @else
        <tr>
            <td style="border: 1px solid black; text-align: center;" colspan="7">
                <span style="font-size: 11pt;"></span>
            </td>
        </tr>
    @endif

    <!-- pantalla_proyector -->
    <tr>
        <td style="border: 1px solid black; text-align: center;" colspan="5">
            <span style="font-size: 20pt;">PANTALLA DE PROYECCIÓN</span>
        </td>
        <td style="border: 1px solid black; text-align: center;" colspan="2">
            <span style="font-size: 11pt;">ESTADO</span>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">UBICACIÓN</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MARCA</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MODELO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">NUMERO DE SERIE</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">CÓDIGO DE INVENTARIO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">FUNCIONAL</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">NO FUNCIONAL</span>
        </td>
    </tr>
    @if(isset($equipos['pantalla_proyector']) && $equipos['pantalla_proyector']->count() > 0)
        @foreach ($equipos['pantalla_proyector'] as $equipo)
            <tr>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->area?->nombre ?? 'N/A' }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('marca')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('modelo')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('num_serie')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->cod_inventario }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->caracteristicas->get('estado_funcional')?->valor === 'funcional' ? 'X' : '' }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->caracteristicas->get('estado_funcional')?->valor === 'no_funcional' ? 'X' : '' }}</span>
                </td>
        @endforeach
    @else
        <tr>
            <td style="border: 1px solid black; text-align: center;" colspan="7">
                <span style="font-size: 11pt;"></span>
            </td>
        </tr>
    @endif

    <!-- laptops -->
    <tr>
        <td style="border: 1px solid black; text-align: center;" colspan="5">
            <span style="font-size: 20pt;">LAPTOPS</span>
        </td>
        <td style="border: 1px solid black; text-align: center;" colspan="2">
            <span style="font-size: 11pt;">ESTADO</span>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">UBICACIÓN</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MARCA</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">MODELO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">NUMERO DE SERIE</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">CÓDIGO DE INVENTARIO</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">FUNCIONAL</span>
        </td>
        <td style="border: 1px solid black; text-align: center;">
            <span style="font-size: 11pt;">NO FUNCIONAL</span>
        </td>
    </tr>
    @if(isset($equipos['laptops']) && $equipos['laptops']->count() > 0)
        @foreach ($equipos['laptops'] as $equipo)
            <tr>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->area?->nombre ?? 'N/A' }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('marca')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('modelo')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">
                        {{ $equipo->caracteristicas->get('num_serie')?->valor ?? 'N/A' }}
                    </span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->cod_inventario }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->caracteristicas->get('estado_funcional')?->valor === 'funcional' ? 'X' : '' }}</span>
                </td>
                <td style="border: 1px solid black; text-align: center;">
                    <span style="font-size: 11pt;">{{ $equipo->caracteristicas->get('estado_funcional')?->valor === 'no_funcional' ? 'X' : '' }}</span>
                </td>
        @endforeach
    @else
        <tr>
            <td style="border: 1px solid black; text-align: center;" colspan="7">
                <span style="font-size: 11pt;"></span>
            </td>
        </tr>
    @endif
</html>