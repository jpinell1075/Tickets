<script setup>
import { ref, reactive } from 'vue';
import dashboard from '../../layouts/dashboard.vue';
import SearchSelect from '../../components/inputs/searchSelect.vue';
import Print from '../../components/buttons/Print.vue';

// Filtros para Reporte de Tickets
const ticketFilters = reactive({
    fecha_inicio: '',
    fecha_fin: '',
    id_area: null,
    id_usuario: null,
    id_encargado: null,
    id_equipo: null,
    estado_actual: 'all',
    prioridad: 'all',
    tipo: 'all',
});

// Referencias a los componentes SearchSelect para resetear
const ticketUserRef = ref(null);
const ticketTechRef = ref(null);
const ticketAreaRef = ref(null);
const ticketEquipoRef = ref(null);

function clearTicketFilters() {
    ticketFilters.fecha_inicio = '';
    ticketFilters.fecha_fin = '';
    ticketFilters.id_area = null;
    ticketFilters.id_usuario = null;
    ticketFilters.id_encargado = null;
    ticketFilters.id_equipo = null;
    ticketFilters.estado_actual = 'all';
    ticketFilters.prioridad = 'all';
    ticketFilters.tipo = 'all';
}

// Filtros para Reporte de Mantenimientos
const maintenanceFilters = reactive({
    fecha_inicio: '',
    fecha_fin: '',
    id_area: null,
    id_encargado: null,
    id_equipo: null,
    tipo: 'all',
});

const maintTechRef = ref(null);
const maintAreaRef = ref(null);
const maintEquipoRef = ref(null);

function clearMaintenanceFilters() {
    maintenanceFilters.fecha_inicio = '';
    maintenanceFilters.fecha_fin = '';
    maintenanceFilters.id_area = null;
    maintenanceFilters.id_encargado = null;
    maintenanceFilters.id_equipo = null;
    maintenanceFilters.tipo = 'all';
}
</script>

<template>
    <dashboard :appName="'tickets_app'">

        <div class="container px-4 py-6 mx-auto max-w-7xl">
            <!-- Encabezado de Página -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    Reportes
                </h1>
            </div>

            <div class="space-y-8">
                <!-- ========================================== -->
                <!-- TARJETA 1: REPORTE DE TICKETS E INCIDENCIAS -->
                <!-- ========================================== -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700/80 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">
                                    Reporte de Gestión de Tickets e Incidentes
                                </h3>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-auto">
                            <button 
                                type="button"
                                @click="clearTicketFilters"
                                class="px-3 py-2 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors flex items-center gap-1"
                                title="Limpiar filtros"
                            >
                                <i class='bx bx-reset text-base'></i>
                                <span>Limpiar</span>
                            </button>

                            <Print 
                                :view-name="'tickets.print.tickets'"
                                :title="'Reporte Consolidado de Gestión de Tickets'"
                                :page-properties="{
                                    'pagedjs': true,
                                    'pagecounter': true,
                                    'openInNewTab': true,
                                    'loadBootstrap': true,
                                    'margin': '12px',
                                    'orientation': 'landscape',
                                }"
                                :params="ticketFilters"
                            />
                        </div>
                    </div>

                    <!-- Filtros Dinámicos -->
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Fecha Inicio -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Fecha Desde:
                                </label>
                                <input 
                                    type="date"
                                    v-model="ticketFilters.fecha_inicio"
                                    class="w-full text-xs bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-blue-500"
                                />
                            </div>

                            <!-- Fecha Fin -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Fecha Hasta:
                                </label>
                                <input 
                                    type="date"
                                    v-model="ticketFilters.fecha_fin"
                                    class="w-full text-xs bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-blue-500"
                                />
                            </div>

                            <!-- Área -->
                            <div>
                                <SearchSelect 
                                    ref="ticketAreaRef"
                                    name="Área"
                                    input-name="id_area"
                                    :model="'Area'"
                                    :required="false"
                                    select_name="id_area"
                                    v-model:select_value="ticketFilters.id_area"
                                />
                            </div>

                            <!-- Solicitante (Usuario) -->
                            <div>
                                <SearchSelect 
                                    ref="ticketUserRef"
                                    name="Usuario Solicitante"
                                    input-name="id_usuario"
                                    :model="'User'"
                                    :required="false"
                                    select_name="id_usuario"
                                    v-model:select_value="ticketFilters.id_usuario"
                                />
                            </div>

                            <!-- Técnico Encargado (TI) -->
                            <div>
                                <SearchSelect 
                                    ref="ticketTechRef"
                                    name="Técnico Asignado"
                                    input-name="id_encargado"
                                    :model="'User'"
                                    :required="false"
                                    select_name="id_encargado"
                                    v-model:select_value="ticketFilters.id_encargado"
                                />
                            </div>

                            <!-- Equipo -->
                            <div>
                                <SearchSelect 
                                    ref="ticketEquipoRef"
                                    name="Equipo Asociado"
                                    input-name="id_equipo"
                                    :model="'Equipo'"
                                    :required="false"
                                    select_name="id_equipo"
                                    v-model:select_value="ticketFilters.id_equipo"
                                />
                            </div>

                            <!-- Estado del Ticket -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Estado del Ticket:
                                </label>
                                <select 
                                    v-model="ticketFilters.estado_actual"
                                    class="w-full text-xs bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-blue-500"
                                >
                                    <option value="all">Todos los estados</option>
                                    <option value="abierto">Abierto</option>
                                    <option value="en_proceso">En Proceso</option>
                                    <option value="resuelto">Resuelto</option>
                                    <option value="cerrado">Cerrado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>

                            <!-- Prioridad / Impacto -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Prioridad / Impacto:
                                </label>
                                <select 
                                    v-model="ticketFilters.prioridad"
                                    class="w-full text-xs bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-blue-500"
                                >
                                    <option value="all">Todas las prioridades</option>
                                    <option value="baja">Baja</option>
                                    <option value="media">Media</option>
                                    <option value="alta">Alta</option>
                                    <option value="critica">Crítica</option>
                                </select>
                            </div>

                            <!-- Tipo de Solicitud -->
                            <div class="lg:col-span-2">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Categoría / Tipo de Solicitud:
                                </label>
                                <select 
                                    v-model="ticketFilters.tipo"
                                    class="w-full text-xs bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-blue-500"
                                >
                                    <option value="all">Todas las categorías</option>
                                    <option value="hardware">Hardware (Dispositivos físicos, periféricos, fallas eléctricas)</option>
                                    <option value="software">Software (Aplicaciones, sistemas operativos, ofimática)</option>
                                    <option value="network">Red / Telecomunicaciones (Conectividad, VPN, switches)</option>
                                    <option value="preventivo">Mantenimiento Preventivo Planificado</option>
                                    <option value="correctivo">Mantenimiento Correctivo / Emergencia</option>
                                    <option value="other">Otras Solicitudes / Consultas Generales</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- TARJETA 2: REPORTE DE MANTENIMIENTO Y ACTIVOS TI -->
                <!-- ============================================== -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700/80 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">
                                    Reporte de Mantenimiento
                                </h3>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-auto">
                            <button 
                                type="button"
                                @click="clearMaintenanceFilters"
                                class="px-3 py-2 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors flex items-center gap-1"
                                title="Limpiar filtros"
                            >
                                <i class='bx bx-reset text-base'></i>
                                <span>Limpiar</span>
                            </button>

                            <Print 
                                :view-name="'tickets.print.maintenances'"
                                :title="'Reporte de Mantenimientos Técnicos y Activos TI'"
                                :page-properties="{
                                    'pagedjs': true,
                                    'pagecounter': true,
                                    'openInNewTab': true,
                                    'loadBootstrap': true,
                                    'margin': '12px',
                                    'orientation': 'landscape',
                                }"
                                :params="maintenanceFilters"
                            />
                        </div>
                    </div>

                    <!-- Filtros Dinámicos -->
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Fecha Inicio -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Fecha Desde:
                                </label>
                                <input 
                                    type="date" 
                                    v-model="maintenanceFilters.fecha_inicio"
                                    class="w-full text-xs bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-blue-500"
                                />
                            </div>

                            <!-- Fecha Fin -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Fecha Hasta:
                                </label>
                                <input 
                                    type="date" 
                                    v-model="maintenanceFilters.fecha_fin"
                                    class="w-full text-xs bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-blue-500"
                                />
                            </div>

                            <!-- Tipo de Mantenimiento -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Tipo de Mantenimiento:
                                </label>
                                <select 
                                    v-model="maintenanceFilters.tipo"
                                    class="w-full text-xs bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-blue-500 font-medium"
                                >
                                    <option value="all">Todos los tipos</option>
                                    <option value="preventivo">Preventivo</option>
                                    <option value="correctivo">Correctivo</option>
                                    <option value="predictivo">Predictivo</option>
                                    <option value="otro">Otro Tipo</option>
                                </select>
                            </div>

                            <!-- Área -->
                            <div>
                                <SearchSelect 
                                    ref="maintAreaRef"
                                    name="Área del Equipo"
                                    input-name="id_area_mnt"
                                    :model="'Area'"
                                    :required="false"
                                    select_name="id_area_mnt"
                                    v-model:select_value="maintenanceFilters.id_area"
                                />
                            </div>

                            <!-- Técnico Responsable (TI) -->
                            <div>
                                <SearchSelect 
                                    ref="maintTechRef"
                                    name="Técnico Responsable"
                                    input-name="id_encargado_mnt"
                                    :model="'User'"
                                    :required="false"
                                    select_name="id_encargado_mnt"
                                    v-model:select_value="maintenanceFilters.id_encargado"
                                />
                            </div>

                            <!-- Equipo -->
                            <div>
                                <SearchSelect 
                                    ref="maintEquipoRef"
                                    name="Equipo Intervenido"
                                    input-name="id_equipo_mnt"
                                    :model="'Equipo'"
                                    :required="false"
                                    select_name="id_equipo_mnt"
                                    v-model:select_value="maintenanceFilters.id_equipo"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </dashboard>
</template>
