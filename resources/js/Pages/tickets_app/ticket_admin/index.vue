<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';
import dashboard from '../../layouts/dashboard.vue';
import { getEcho, disconnectEcho } from '../../../echo.js';

const props = defineProps({
    initialTickets: {
        type: Array,
        default: () => [],
    },
    planificaciones: {
        type: Array,
        default: () => [],
    },
    encargados: {
        type: Array,
        default: () => [],
    },
    areas: {
        type: Array,
        default: () => [],
    },
    equipos: {
        type: Array,
        default: () => [],
    },
    initialStatuses: {
        type: Array,
        default: () => ['abierto', 'en_proceso'],
    },
    globalStatusCounts: {
        type: Object,
        default: () => ({}),
    },
});

// Metadatos de Columnas de Estado
const statusColumns = [
    { key: 'abierto', label: 'Abierto', color: 'blue', icon: 'bx bx-folder-open', bgLight: 'bg-blue-50/40 dark:bg-blue-950/20', border: 'border-blue-500', badge: 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200' },
    { key: 'en_proceso', label: 'En Proceso', color: 'amber', icon: 'bx bx-loader-circle', bgLight: 'bg-amber-50/40 dark:bg-amber-950/20', border: 'border-amber-500', badge: 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200' },
    { key: 'resuelto', label: 'Resuelto', color: 'emerald', icon: 'bx bx-check-circle', bgLight: 'bg-emerald-50/40 dark:bg-emerald-950/20', border: 'border-emerald-500', badge: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200' },
    { key: 'cerrado', label: 'Cerrado', color: 'purple', icon: 'bx bx-lock-alt', bgLight: 'bg-purple-50/40 dark:bg-purple-950/20', border: 'border-purple-500', badge: 'bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-200' },
    { key: 'cancelado', label: 'Cancelado', color: 'rose', icon: 'bx bx-x-circle', bgLight: 'bg-rose-50/40 dark:bg-rose-950/20', border: 'border-rose-500', badge: 'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-200' },
];

// Estado de Columnas Visibles (Cargado desde localStorage para persistencia)
const getInitialVisibleColumns = () => {
    try {
        const saved = localStorage.getItem('kanban_visible_columns');
        if (saved) {
            const parsed = JSON.parse(saved);
            if (Array.isArray(parsed) && parsed.length > 0) return parsed;
        }
    } catch (e) {}
    return props.initialStatuses || ['abierto', 'en_proceso'];
};

const visibleColumns = ref(getInitialVisibleColumns());
const isColumnsDropdownOpen = ref(false);

const displayedColumns = computed(() => {
    return statusColumns.filter(col => visibleColumns.value.includes(col.key));
});

// Estado reactivo principal
const tickets = ref([...props.initialTickets]);
const planificacionesList = ref([...props.planificaciones]);
const encargadosList = ref([...props.encargados]);
const globalCounts = ref({ ...props.globalStatusCounts });
const activeTab = ref('general'); // 'general' | planificacion_id | 'all'
const searchQuery = ref('');
const filterPriority = ref('all');
const filterType = ref('all');
const filterEncargado = ref('all');

// Estado de Sincronización Dual (Tiempo Real WebSockets vs Auto-Refresco Polling)
const syncMode = ref(localStorage.getItem('kanban_sync_mode') || 'websocket'); // 'websocket' | 'polling'
const wsStatus = ref('connecting'); // 'connected' | 'connecting' | 'disconnected' | 'unavailable'
const isSyncMenuOpen = ref(false);
const highlightedTicketId = ref(null);
const refreshIntervalSec = ref(10); // 5, 10, 30, 0 (pausa)
const isPollingActive = ref(true);
const isRefreshing = ref(false);
const lastUpdatedAt = ref(new Date());
let pollingTimer = null;
let echoChannel = null;

// Toast / Notificaciones sutiles
const toastMessage = ref('');
const toastVisible = ref(false);
let toastTimer = null;

function showToast(msg) {
    toastMessage.value = msg;
    toastVisible.value = true;
    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        toastVisible.value = false;
    }, 3000);
}

const priorityConfig = {
    baja: { label: 'Baja', color: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300 border-emerald-300 dark:border-emerald-700', dot: 'bg-emerald-500' },
    media: { label: 'Media', color: 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300 border-amber-300 dark:border-amber-700', dot: 'bg-amber-500' },
    alta: { label: 'Alta', color: 'bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300 border-orange-300 dark:border-orange-700', dot: 'bg-orange-500' },
    critica: { label: 'Crítica', color: 'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300 border-rose-300 dark:border-rose-700 animate-pulse', dot: 'bg-rose-500' },
};

const typeLabels = {
    hardware: 'Hardware',
    software: 'Software',
    network: 'Red',
    other: 'Otro',
    preventivo: 'Preventivo',
    correctivo: 'Correctivo',
};

// Menús desplegables activos (popovers de 1 clic)
const activeDropdown = ref({
    ticketId: null,
    type: null, // 'priority' | 'type' | 'encargado' | 'status'
});

// Modal de Resolución / Cierre / Mantenimiento
const showResolveModal = ref(false);
const resolveForm = ref({
    ticket: null,
    targetStatus: '',
    observacion_resolucion: '',
    create_maintenance: true,
    maintenance_observation: '',
    isSubmitting: false,
});

// Modal de Detalle Completo
const showDetailModal = ref(false);
const selectedTicket = ref(null);

// Filtro de técnicos para el selector con buscador (solo personal de TI)
const techSearchQuery = ref('');
const filteredEncargados = computed(() => {
    if (!techSearchQuery.value) return encargadosList.value;
    const q = techSearchQuery.value.toLowerCase();
    return encargadosList.value.filter(e => 
        (e.name && e.name.toLowerCase().includes(q)) ||
        (e.especialidad && e.especialidad.toLowerCase().includes(q))
    );
});

// Función explícita para cambiar de pestaña
function selectTab(tabKey) {
    activeTab.value = tabKey;
    closeDropdown();
}

// Conteo de tickets por pestaña
const tabCounts = computed(() => {
    const generalCount = tickets.value.filter(t => !t.id_programacion_mantenimiento).length;
    const allCount = tickets.value.length;
    const planCounts = {};
    planificacionesList.value.forEach(p => {
        planCounts[p.id] = tickets.value.filter(t => String(t.id_programacion_mantenimiento) === String(p.id)).length;
    });
    return { general: generalCount, all: allCount, plans: planCounts };
});

// Filtrado de tickets en cliente
const filteredTickets = computed(() => {
    return tickets.value.filter(ticket => {
        // Solo mostrar tickets de columnas activas
        if (!visibleColumns.value.includes(ticket.estado_actual)) {
            return false;
        }

        // Filtro por pestaña (comparación flexible y segura de tipos)
        if (String(activeTab.value) === 'general') {
            if (ticket.id_programacion_mantenimiento) return false;
        } else if (String(activeTab.value) !== 'all') {
            if (String(ticket.id_programacion_mantenimiento) !== String(activeTab.value)) return false;
        }

        // Filtro por búsqueda textual
        if (searchQuery.value) {
            const query = searchQuery.value.toLowerCase();
            const titleMatch = ticket.titulo?.toLowerCase().includes(query);
            const descMatch = ticket.descripcion?.toLowerCase().includes(query);
            const userMatch = ticket.usuario?.name?.toLowerCase().includes(query);
            const equipoMatch = ticket.equipo?.nombre?.toLowerCase().includes(query) || ticket.equipo?.cod_inventario?.toLowerCase().includes(query);
            const encargadoMatch = ticket.encargado?.name?.toLowerCase().includes(query);
            if (!titleMatch && !descMatch && !userMatch && !equipoMatch && !encargadoMatch) return false;
        }

        // Filtro por prioridad
        if (filterPriority.value !== 'all' && ticket.prioridad !== filterPriority.value) {
            return false;
        }

        // Filtro por tipo
        if (filterType.value !== 'all' && ticket.tipo !== filterType.value) {
            return false;
        }

        // Filtro por encargado
        if (filterEncargado.value === 'unassigned') {
            if (ticket.id_encargado !== null) return false;
        } else if (filterEncargado.value !== 'all') {
            if (String(ticket.id_encargado) !== String(filterEncargado.value)) return false;
        }

        return true;
    });
});

// Conteo por columna activa
const columnCounts = computed(() => {
    const counts = {};
    statusColumns.forEach(col => {
        counts[col.key] = filteredTickets.value.filter(t => t.estado_actual === col.key).length;
    });
    return counts;
});

// Métricas de cabecera
const metrics = computed(() => {
    const total = filteredTickets.value.length;
    const abiertas = filteredTickets.value.filter(t => t.estado_actual === 'abierto').length;
    const enProceso = filteredTickets.value.filter(t => t.estado_actual === 'en_proceso').length;
    const criticas = filteredTickets.value.filter(t => t.prioridad === 'critica' && ['abierto', 'en_proceso'].includes(t.estado_actual)).length;
    const resueltas = globalCounts.value['resuelto'] || tickets.value.filter(t => t.estado_actual === 'resuelto').length;
    return { total, abiertas, enProceso, criticas, resueltas };
});

// Alternar visibilidad de columna
function toggleColumn(colKey) {
    const idx = visibleColumns.value.indexOf(colKey);
    if (idx !== -1) {
        if (visibleColumns.value.length > 1) {
            visibleColumns.value.splice(idx, 1);
        } else {
            if (typeof showToast === 'function') showToast('Debes mantener al menos una columna visible');
            return;
        }
    } else {
        visibleColumns.value.push(colKey);
    }
    localStorage.setItem('kanban_visible_columns', JSON.stringify(visibleColumns.value));
    fetchTicketsData(false);
}

// Función para alternar menús desplegables rápidos
function toggleDropdown(ticketId, type, event) {
    if (event) event.stopPropagation();
    if (activeDropdown.value.ticketId === ticketId && activeDropdown.value.type === type) {
        closeDropdown();
    } else {
        isColumnsDropdownOpen.value = false;
        techSearchQuery.value = '';
        activeDropdown.value = { ticketId, type };
    }
}

function closeDropdown() {
    activeDropdown.value = { ticketId: null, type: null };
}

function toggleColumnsDropdown(event) {
    if (event) event.stopPropagation();
    isColumnsDropdownOpen.value = !isColumnsDropdownOpen.value;
    closeDropdown();
}

// Cerrar dropdowns al hacer clic fuera
function handleDocumentClick(e) {
    const columnsContainer = document.querySelector('.columns-dropdown-container');
    if (columnsContainer && columnsContainer.contains(e.target)) {
        return;
    }

    const syncContainer = document.querySelector('.sync-menu-container');
    if (syncContainer && syncContainer.contains(e.target)) {
        return;
    }

    const popovers = document.querySelectorAll('.fast-dropdown-popover');
    let isInside = false;
    popovers.forEach(p => {
        if (p.contains(e.target)) isInside = true;
    });

    if (!isInside) {
        closeDropdown();
        isColumnsDropdownOpen.value = false;
        isSyncMenuOpen.value = false;
    }
}

// Helper para saber si un ticket está bloqueado (finalizado)
function isTicketLocked(ticket) {
    if (!ticket) return false;
    return ['resuelto', 'cerrado', 'cancelado'].includes(ticket.estado_actual);
}

// Actualización rápida de Prioridad (1 Clic)
async function setTicketPriority(ticket, newPriority) {
    if (isTicketLocked(ticket)) {
        if (typeof showToast === 'function') showToast('Los tickets resueltos o cerrados no pueden ser modificados.');
        return;
    }
    closeDropdown();
    const oldPriority = ticket.prioridad;
    ticket.prioridad = newPriority; // Optimista

    try {
        await axios.put(route('ticket_admin.updatePriority', ticket.id), {
            prioridad: newPriority,
        });
        if (typeof showToast === 'function') showToast(`Prioridad cambiada a ${priorityConfig[newPriority]?.label || newPriority}`);
    } catch (err) {
        ticket.prioridad = oldPriority; // Rollback
        if (typeof showToast === 'function') showToast('Error al actualizar la prioridad');
    }
}

// Actualización rápida de Tipo (1 Clic)
async function setTicketType(ticket, newType) {
    if (isTicketLocked(ticket)) {
        if (typeof showToast === 'function') showToast('Los tickets resueltos o cerrados no pueden ser modificados.');
        return;
    }
    closeDropdown();
    const oldType = ticket.tipo;
    ticket.tipo = newType; // Optimista

    try {
        await axios.put(route('ticket_admin.updateType', ticket.id), {
            tipo: newType,
        });
        if (typeof showToast === 'function') showToast(`Tipo cambiado a ${typeLabels[newType] || newType}`);
    } catch (err) {
        ticket.tipo = oldType; // Rollback
        if (typeof showToast === 'function') showToast('Error al actualizar el tipo de solicitud');
    }
}

// Actualización rápida de Encargado (1 Clic)
async function setTicketEncargado(ticket, newEncargadoId) {
    if (isTicketLocked(ticket)) {
        if (typeof showToast === 'function') showToast('Los tickets resueltos o cerrados no pueden ser modificados.');
        return;
    }
    closeDropdown();
    const oldEncargado = ticket.encargado;
    const oldEncargadoId = ticket.id_encargado;
    
    ticket.id_encargado = newEncargadoId;
    ticket.encargado = encargadosList.value.find(e => e.id === newEncargadoId) || null;

    try {
        await axios.put(route('ticket_admin.updateEncargado', ticket.id), {
            id_encargado: newEncargadoId,
        });
        const name = ticket.encargado ? ticket.encargado.name : 'Sin asignar';
        if (typeof showToast === 'function') showToast(`Encargado asignado: ${name}`);
    } catch (err) {
        ticket.id_encargado = oldEncargadoId;
        ticket.encargado = oldEncargado;
        if (typeof showToast === 'function') showToast('Error al asignar el encargado');
    }
}

// Gestión de Cambio de Estado
function handleStatusChangeClick(ticket, targetStatus) {
    if (isTicketLocked(ticket)) {
        if (typeof showToast === 'function') showToast('Los tickets resueltos, cerrados o cancelados no pueden cambiar de estado.');
        return;
    }
    closeDropdown();
    if (['resuelto', 'cerrado', 'cancelado'].includes(targetStatus)) {
        const defaultMaintType = ['preventivo', 'correctivo', 'predictivo'].includes(ticket.tipo) 
            ? ticket.tipo 
            : (ticket.id_programacion_mantenimiento ? 'preventivo' : 'correctivo');

        resolveForm.value = {
            ticket: ticket,
            targetStatus: targetStatus,
            observacion_resolucion: ticket.observacion_resolucion || '',
            create_maintenance: !!ticket.id_equipo && targetStatus !== 'cancelado',
            maintenance_type: defaultMaintType,
            maintenance_observation: '',
            isSubmitting: false,
        };
        showResolveModal.value = true;
    } else {
        applyDirectStatusChange(ticket, targetStatus);
    }
}

async function applyDirectStatusChange(ticket, targetStatus) {
    const oldStatus = ticket.estado_actual;
    ticket.estado_actual = targetStatus; // Optimista

    try {
        await axios.put(route('ticket_admin.updateStatus', ticket.id), {
            estado_actual: targetStatus,
        });
        const label = statusColumns.find(s => s.key === targetStatus)?.label || targetStatus;
        if (typeof showToast === 'function') {
            showToast(visibleColumns.value.includes(targetStatus) ? `Ticket marcado como ${label}.` : `Ticket marcado como ${label} y archivado de la vista.`);
        }
        
        // Si el nuevo estado no está en columnas visibles, refrescamos silenciosamente
        if (!visibleColumns.value.includes(targetStatus)) {
            fetchTicketsData(true);
        }
    } catch (err) {
        ticket.estado_actual = oldStatus; // Rollback
        if (typeof showToast === 'function') showToast('Error al actualizar el estado del ticket');
    }
}

// Enviar formulario del modal de resolución / mantenimiento
async function submitResolveForm() {
    if (!resolveForm.value.ticket) return;
    resolveForm.value.isSubmitting = true;
    const t = resolveForm.value.ticket;
    const oldStatus = t.estado_actual;
    const targetStatus = resolveForm.value.targetStatus;

    try {
        const response = await axios.put(route('ticket_admin.updateStatus', t.id), {
            estado_actual: targetStatus,
            observacion_resolucion: resolveForm.value.observacion_resolucion,
            create_maintenance: resolveForm.value.create_maintenance,
            maintenance_type: resolveForm.value.maintenance_type,
            maintenance_observation: resolveForm.value.maintenance_observation,
        });

        t.estado_actual = targetStatus;
        t.observacion_resolucion = resolveForm.value.observacion_resolucion;
        t.fecha_resolucion = new Date().toISOString();

        if (response.data?.ticket?.mantenimientos) {
            t.mantenimientos = response.data.ticket.mantenimientos;
        }

        showResolveModal.value = false;
        
        const label = statusColumns.find(s => s.key === targetStatus)?.label || targetStatus;
        if (typeof showToast === 'function') {
            showToast(visibleColumns.value.includes(targetStatus) ? `Ticket marcado como ${label}.` : `Ticket marcado como ${label} y archivado de la vista.`);
        }

        // Si la columna de destino no está activa, refrescamos para mantener la lista limpia
        if (!visibleColumns.value.includes(targetStatus)) {
            fetchTicketsData(true);
        }
    } catch (err) {
        t.estado_actual = oldStatus;
        if (typeof showToast === 'function') showToast('Error al procesar el cambio de estado');
    } finally {
        resolveForm.value.isSubmitting = false;
    }
}

// Modal de Detalle
function openTicketDetail(ticket) {
    selectedTicket.value = ticket;
    showDetailModal.value = true;
}

// Polling asíncrono optimizado con columnas activas
async function fetchTicketsData(isSilent = false) {
    if (!isSilent) isRefreshing.value = true;
    try {
        const res = await axios.get(route('ticket_admin.data'), {
            params: {
                statuses: visibleColumns.value,
            }
        });
        if (res.data && res.data.tickets) {
            tickets.value = res.data.tickets;
            if (res.data.planificaciones) {
                planificacionesList.value = res.data.planificaciones;
            }
            if (res.data.encargados) {
                encargadosList.value = res.data.encargados;
            }
            if (res.data.globalStatusCounts) {
                globalCounts.value = res.data.globalStatusCounts;
            }
            lastUpdatedAt.value = new Date();
        }
    } catch (e) {
        console.error('Error fetching tickets realtime:', e);
    } finally {
        if (!isSilent) isRefreshing.value = false;
    }
}

// Métodos de Gestión de WebSockets
function initWebSockets() {
    try {
        const echo = getEcho();
        wsStatus.value = 'connecting';

        if (window.Pusher && echo.connector?.pusher?.connection) {
            const conn = echo.connector.pusher.connection;
            conn.bind('connected', () => {
                wsStatus.value = 'connected';
            });
            conn.bind('connecting', () => {
                wsStatus.value = 'connecting';
            });
            conn.bind('disconnected', () => {
                wsStatus.value = 'disconnected';
            });
            conn.bind('unavailable', () => {
                wsStatus.value = 'unavailable';
            });
            conn.bind('error', () => {
                wsStatus.value = 'disconnected';
            });
            if (conn.state === 'connected') {
                wsStatus.value = 'connected';
            }
        }

        echoChannel = echo.channel('tickets.kanban');
        echoChannel.listen('.TicketKanbanUpdated', (event) => {
            handleTicketBroadcast(event);
        });
    } catch (err) {
        console.warn('Error inicializando WebSockets:', err);
        wsStatus.value = 'unavailable';
    }
}

function cleanupWebSockets() {
    if (echoChannel) {
        try {
            const echo = getEcho();
            echo.leaveChannel('tickets.kanban');
        } catch (e) {}
        echoChannel = null;
    }
    disconnectEcho();
    wsStatus.value = 'disconnected';
}

function handleTicketBroadcast(event) {
    if (!event) return;
    const { ticket, action, globalStatusCounts } = event;

    if (globalStatusCounts) {
        globalCounts.value = { ...globalStatusCounts };
    }

    if (action === 'deleted') {
        const ticketId = ticket?.id;
        const idx = tickets.value.findIndex(t => t.id === ticketId);
        if (idx !== -1) {
            tickets.value.splice(idx, 1);
        }
        showToast('Ticket #' + ticketId + ' eliminado.');
        return;
    }

    if (!ticket || !ticket.id) return;

    const idx = tickets.value.findIndex(t => t.id === ticket.id);
    if (idx !== -1) {
        tickets.value[idx] = { ...tickets.value[idx], ...ticket };
        flashTicket(ticket.id);
    } else {
        if (visibleColumns.value.includes(ticket.estado_actual)) {
            tickets.value.unshift(ticket);
            flashTicket(ticket.id);
        }
    }

    lastUpdatedAt.value = new Date();
}

let flashTimer = null;
function flashTicket(id) {
    highlightedTicketId.value = id;
    if (flashTimer) clearTimeout(flashTimer);
    flashTimer = setTimeout(() => {
        highlightedTicketId.value = null;
    }, 2500);
}

function setSyncMode(mode) {
    syncMode.value = mode;
    localStorage.setItem('kanban_sync_mode', mode);
    isSyncMenuOpen.value = false;

    if (mode === 'websocket') {
        stopPolling();
        initWebSockets();
        fetchTicketsData(true);
        showToast('⚡ Modo Tiempo Real (WebSockets) activado');
    } else {
        cleanupWebSockets();
        startPolling();
        showToast('⏱️ Modo Auto-Refresco (Polling) activado');
    }
}

function toggleSyncMenu(event) {
    if (event) event.stopPropagation();
    isSyncMenuOpen.value = !isSyncMenuOpen.value;
    closeDropdown();
    isColumnsDropdownOpen.value = false;
}

function startPolling() {
    stopPolling();
    if (refreshIntervalSec.value > 0 && isPollingActive.value) {
        pollingTimer = setInterval(() => {
            if (!document.hidden) {
                fetchTicketsData(true);
            }
        }, refreshIntervalSec.value * 1000);
    }
}

function stopPolling() {
    if (pollingTimer) {
        clearInterval(pollingTimer);
        pollingTimer = null;
    }
}

function togglePollingState() {
    isPollingActive.value = !isPollingActive.value;
    if (isPollingActive.value) {
        startPolling();
        showToast('Auto-refresco reanudado');
    } else {
        stopPolling();
        showToast('Auto-refresco pausado');
    }
}

function changeInterval(sec) {
    refreshIntervalSec.value = sec;
    if (sec === 0) {
        isPollingActive.value = false;
        stopPolling();
    } else {
        isPollingActive.value = true;
        startPolling();
    }
}

// Visibilidad de pestaña
function handleVisibilityChange() {
    if (document.hidden) {
        if (syncMode.value === 'polling') {
            stopPolling();
        }
    } else {
        if (syncMode.value === 'polling' && isPollingActive.value && refreshIntervalSec.value > 0) {
            fetchTicketsData(true);
            startPolling();
        } else if (syncMode.value === 'websocket') {
            fetchTicketsData(true);
        }
    }
}

// Utilidad para formatear fechas
function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    } catch (e) {
        return dateStr;
    }
}

function formatRelativeTime(dateStr) {
    if (!dateStr) return '';
    const diff = (new Date() - new Date(dateStr)) / 1000;
    if (diff < 60) return 'ahora';
    if (diff < 3600) return `${Math.floor(diff / 60)}m`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h`;
    return `${Math.floor(diff / 86400)}d`;
}

function getInitials(name) {
    if (!name) return '?';
    const parts = name.trim().split(' ');
    if (parts.length >= 2) return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
    return name.substring(0, 2).toUpperCase();
}

onMounted(() => {
    document.addEventListener('click', handleDocumentClick);
    document.addEventListener('visibilitychange', handleVisibilityChange);
    
    // Si localStorage tenía columnas distintas a las iniciales del servidor, sincronizamos
    const saved = localStorage.getItem('kanban_visible_columns');
    if (saved) {
        fetchTicketsData(true);
    }

    // Inicializar modo de sincronización
    if (syncMode.value === 'websocket') {
        initWebSockets();
    } else {
        startPolling();
    }
});

onUnmounted(() => {
    document.removeEventListener('click', handleDocumentClick);
    document.removeEventListener('visibilitychange', handleVisibilityChange);
    cleanupWebSockets();
    stopPolling();
});

watch(activeTab, () => {
    closeDropdown();
});
</script>

<template>
    <dashboard :appName="'tickets_app'">

        <div class="px-2 sm:px-3 pb-4 max-w-full mx-auto">
            <!-- Barra Superior Unificada: Título, Métricas Compactas y Controles -->
            <div class="flex flex-wrap items-center justify-between gap-2 mb-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700/80 rounded-lg px-3 py-2 shadow-xs">
                <!-- Título + Indicador En Vivo -->
                <div class="flex items-center gap-2">
                    <h1 class="text-base font-bold text-gray-900 dark:text-gray-100 flex items-center gap-1.5">
                        <i class='bx bx-task text-blue-600 dark:text-blue-400 text-xl'></i>
                        <span>Tickets</span>
                    </h1>
                </div>

                <!-- Métricas Compactas en Píldoras -->
                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                    <span class="px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium">
                        Activos: <strong>{{ metrics.total }}</strong>
                    </span>
                    <span class="px-2 py-0.5 rounded-md bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 font-medium">
                        Abiertos: <strong>{{ metrics.abiertas }}</strong>
                    </span>
                    <span class="px-2 py-0.5 rounded-md bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 font-medium">
                        En Proceso: <strong>{{ metrics.enProceso }}</strong>
                    </span>
                    <span 
                        class="px-2 py-0.5 rounded-md font-medium"
                        :class="metrics.criticas > 0 ? 'bg-rose-100 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 animate-pulse' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                    >
                        Críticos: <strong>{{ metrics.criticas }}</strong>
                    </span>
                    <span class="px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-medium" title="Histórico total de resueltos en BD">
                        Resueltos: <strong>{{ metrics.resueltas }}</strong>
                    </span>
                </div>

                <!-- Controles de Sincronización Dual (WebSockets / Polling) -->
                <div class="flex items-center gap-1.5">
                    <!-- Dropdown de Modo de Sincronización -->
                    <div class="relative sync-menu-container">
                        <button
                            type="button"
                            @click="toggleSyncMenu($event)"
                            class="inline-flex items-center gap-1.5 px-2 py-1 text-xs font-medium rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-200 transition-colors shadow-2xs cursor-pointer"
                            :title="syncMode === 'websocket' ? 'Sincronización en Tiempo Real (WebSockets)' : 'Auto-Refresco Periódico (Polling)'"
                        >
                            <!-- Ícono / Indicador de Modo -->
                            <template v-if="syncMode === 'websocket'">
                                <span class="relative flex h-2 w-2">
                                    <span 
                                        v-if="wsStatus === 'connected'" 
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"
                                    ></span>
                                    <span 
                                        class="relative inline-flex rounded-full h-2 w-2"
                                        :class="{
                                            'bg-emerald-500': wsStatus === 'connected',
                                            'bg-amber-500 animate-pulse': wsStatus === 'connecting',
                                            'bg-rose-500': wsStatus === 'disconnected' || wsStatus === 'unavailable'
                                        }"
                                    ></span>
                                </span>
                                <span class="font-semibold text-emerald-700 dark:text-emerald-400 hidden sm:inline">Tiempo Real</span>
                            </template>

                            <template v-else>
                                <i class='bx bx-time text-blue-500'></i>
                                <span class="hidden sm:inline">{{ isPollingActive ? `${refreshIntervalSec}s` : 'Pausado' }}</span>
                            </template>

                            <i class='bx bx-chevron-down text-gray-400 text-xs'></i>
                        </button>

                        <!-- Menú Desplegable de Modos -->
                        <div
                            v-if="isSyncMenuOpen"
                            class="fast-dropdown-popover absolute right-0 top-full mt-1 z-50 w-56 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl py-1.5 text-xs"
                            @click.stop
                        >
                            <div class="px-3 py-1 text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700/60 mb-1">
                                Modo de Sincronización
                            </div>

                            <!-- Opción 1: Tiempo Real (WebSockets) -->
                            <button
                                type="button"
                                @click="setSyncMode('websocket')"
                                class="w-full text-left px-3 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-700/60 flex items-center justify-between transition-colors cursor-pointer"
                                :class="{ 'bg-blue-50/70 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 font-semibold': syncMode === 'websocket' }"
                            >
                                <div class="flex items-center gap-2">
                                    <span class="p-1 rounded bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-300">
                                        <i class='bx bx-broadcast text-sm'></i>
                                    </span>
                                    <div>
                                        <div class="font-medium text-gray-800 dark:text-gray-100 flex items-center gap-1.5">
                                            <span>Tiempo Real</span>
                                            <span 
                                                class="text-[9px] px-1 py-0.2 rounded font-normal"
                                                :class="{
                                                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200': wsStatus === 'connected',
                                                    'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200': wsStatus === 'connecting',
                                                    'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-200': wsStatus === 'disconnected' || wsStatus === 'unavailable'
                                                }"
                                            >
                                                {{ wsStatus === 'connected' ? 'En línea' : (wsStatus === 'connecting' ? 'Conectando...' : 'Offline') }}
                                            </span>
                                        </div>
                                        <div class="text-[10px] text-gray-500 dark:text-gray-400">WebSockets (Sin demora)</div>
                                    </div>
                                </div>
                                <i v-if="syncMode === 'websocket'" class='bx bx-check text-blue-600 dark:text-blue-400 text-base'></i>
                            </button>

                            <!-- Opción 2: Polling / Auto-refresco -->
                            <button
                                type="button"
                                @click="setSyncMode('polling')"
                                class="w-full text-left px-3 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-700/60 flex items-center justify-between transition-colors cursor-pointer"
                                :class="{ 'bg-blue-50/70 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 font-semibold': syncMode === 'polling' }"
                            >
                                <div class="flex items-center gap-2">
                                    <span class="p-1 rounded bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-300">
                                        <i class='bx bx-refresh text-sm'></i>
                                    </span>
                                    <div>
                                        <div class="font-medium text-gray-800 dark:text-gray-100">Auto-Refresco (Polling)</div>
                                        <div class="text-[10px] text-gray-500 dark:text-gray-400">Para entornos estándar/básicos</div>
                                    </div>
                                </div>
                                <i v-if="syncMode === 'polling'" class='bx bx-check text-blue-600 dark:text-blue-400 text-base'></i>
                            </button>

                            <!-- Intervalos cuando polling está seleccionado -->
                            <div v-if="syncMode === 'polling'" class="mt-1.5 pt-1.5 border-t border-gray-100 dark:border-gray-700/60 px-3">
                                <div class="text-[10px] font-semibold text-gray-400 mb-1">Intervalo de Consulta:</div>
                                <div class="grid grid-cols-4 gap-1">
                                    <button 
                                        v-for="sec in [5, 10, 30]" 
                                        :key="sec"
                                        @click="changeInterval(sec)"
                                        class="px-1.5 py-1 text-[10px] font-semibold rounded text-center transition-colors cursor-pointer"
                                        :class="refreshIntervalSec === sec && isPollingActive ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                                    >
                                        {{ sec }}s
                                    </button>
                                    <button 
                                        @click="togglePollingState"
                                        class="px-1.5 py-1 text-[10px] font-semibold rounded text-center transition-colors cursor-pointer"
                                        :class="!isPollingActive ? 'bg-amber-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                                        :title="isPollingActive ? 'Pausar' : 'Reanudar'"
                                    >
                                        {{ isPollingActive ? 'Pausa' : 'Play' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botón de Refresco Manual Rápido -->
                    <button 
                        @click="fetchTicketsData(false)" 
                        :disabled="isRefreshing"
                        class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-xs font-medium transition-all shadow-2xs disabled:opacity-50 cursor-pointer"
                        title="Refrescar ahora manualmente"
                    >
                        <i class='bx bx-refresh text-sm' :class="{ 'animate-spin': isRefreshing }"></i>
                        <span class="hidden sm:inline">Refrescar</span>
                    </button>
                </div>
            </div>

            <!-- Fila Combinada: Pestañas + Selector de Columnas + Filtros Rápidos -->
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-2 mb-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700/80 rounded-lg p-1.5 shadow-xs">
                <!-- Pestañas (Tabs) en Píldoras -->
                <div class="flex items-center gap-1 overflow-x-auto custom-kanban-scroll pb-1 lg:pb-0">
                    <button
                        type="button"
                        @click="selectTab('general')"
                        class="flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-md whitespace-nowrap transition-colors cursor-pointer"
                        :class="String(activeTab) === 'general'
                            ? 'bg-blue-600 text-white shadow-xs'
                            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                    >
                        <i class='bx bx-file'></i>
                        <span>Generales</span>
                        <span 
                            class="ml-1 px-1.5 py-0.2 text-[10px] rounded-full font-bold"
                            :class="String(activeTab) === 'general' ? 'bg-blue-800 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                        >
                            {{ tabCounts.general }}
                        </span>
                    </button>

                    <button
                        v-for="plan in planificacionesList"
                        :key="plan.id"
                        type="button"
                        @click="selectTab(plan.id)"
                        class="flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-md whitespace-nowrap transition-colors cursor-pointer"
                        :class="String(activeTab) === String(plan.id)
                            ? 'bg-indigo-600 text-white shadow-xs'
                            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                    >
                        <i class='bx bx-calendar'></i>
                        <span>Plan: {{ plan.area?.nombre || `#${plan.id_area}` }}</span>
                        <span 
                            class="ml-1 px-1.5 py-0.2 text-[10px] rounded-full font-bold"
                            :class="String(activeTab) === String(plan.id) ? 'bg-indigo-800 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                        >
                            {{ tabCounts.plans[plan.id] || 0 }}
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="selectTab('all')"
                        class="flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-md whitespace-nowrap transition-colors cursor-pointer"
                        :class="String(activeTab) === 'all'
                            ? 'bg-purple-600 text-white shadow-xs'
                            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                    >
                        <i class='bx bx-layer'></i>
                        <span>Todos</span>
                        <span 
                            class="ml-1 px-1.5 py-0.2 text-[10px] rounded-full font-bold"
                            :class="String(activeTab) === 'all' ? 'bg-purple-800 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                        >
                            {{ tabCounts.all }}
                        </span>
                    </button>
                </div>

                <!-- Filtros Inline Rápidos + Selector de Columnas Visibles -->
                <div class="flex flex-wrap items-center gap-1.5">
                    <!-- Selector de Columnas Visibles (Optimización de Carga) -->
                    <div class="relative columns-dropdown-container">
                        <button 
                            type="button"
                            @click="toggleColumnsDropdown($event)"
                            class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs rounded font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors cursor-pointer"
                            title="Ocultar o mostrar columnas para aligerar la vista"
                        >
                            <i class='bx bx-columns text-sm text-blue-600 dark:text-blue-400'></i>
                            <span>Columnas ({{ visibleColumns.length }}/5)</span>
                            <i class='bx bx-chevron-down text-xs'></i>
                        </button>

                        <!-- Popover de Selección de Columnas -->
                        <div 
                            v-if="isColumnsDropdownOpen"
                            class="fast-dropdown-popover absolute right-0 top-full mt-1 z-40 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl p-2 text-xs"
                            @click.stop
                        >
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 px-1">
                                Columnas Activas
                            </div>
                            <div class="space-y-1">
                                <label 
                                    v-for="col in statusColumns" 
                                    :key="col.key"
                                    class="flex items-center justify-between px-1.5 py-1 rounded hover:bg-gray-50 dark:hover:bg-gray-700/60 cursor-pointer text-xs transition-colors"
                                >
                                    <span class="flex items-center gap-1.5 font-medium text-gray-800 dark:text-gray-200">
                                        <i :class="col.icon"></i>
                                        <span>{{ col.label }}</span>
                                    </span>
                                    <input 
                                        type="checkbox" 
                                        :checked="visibleColumns.includes(col.key)"
                                        @change="toggleColumn(col.key)"
                                        class="w-3.5 h-3.5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer"
                                    />
                                </label>
                            </div>
                            <div class="mt-2 pt-1.5 border-t border-gray-100 dark:border-gray-700 text-[10px] text-gray-400 px-1">
                                💡 Desactivar columnas no carga tickets inactivos.
                            </div>
                        </div>
                    </div>

                    <!-- Buscador -->
                    <div class="relative w-full sm:w-40 lg:w-44">
                        <span class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none text-gray-400">
                            <i class='bx bx-search text-sm'></i>
                        </span>
                        <input 
                            type="text" 
                            v-model="searchQuery"
                            placeholder="Buscar..."
                            class="w-full pl-7 pr-2 py-1 text-xs bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-gray-100 focus:ring-1 focus:ring-blue-500"
                        />
                    </div>

                    <!-- Prioridad -->
                    <select 
                        v-model="filterPriority"
                        class="text-xs bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded py-1 px-1.5 text-gray-700 dark:text-gray-200 focus:ring-1 focus:ring-blue-500"
                    >
                        <option value="all">Prioridad</option>
                        <option value="baja">🟢 Baja</option>
                        <option value="media">🟡 Media</option>
                        <option value="alta">🟠 Alta</option>
                        <option value="critica">🔴 Crítica</option>
                    </select>

                    <!-- Tipo -->
                    <select 
                        v-model="filterType"
                        class="text-xs bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded py-1 px-1.5 text-gray-700 dark:text-gray-200 focus:ring-1 focus:ring-blue-500"
                    >
                        <option value="all">Tipo</option>
                        <option value="hardware">Hardware</option>
                        <option value="software">Software</option>
                        <option value="network">Red</option>
                        <option value="other">Otro</option>
                        <option value="preventivo">Preventivo</option>
                        <option value="correctivo">Correctivo</option>
                    </select>

                    <!-- Encargado -->
                    <select 
                        v-model="filterEncargado"
                        class="text-xs bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded py-1 px-1.5 text-gray-700 dark:text-gray-200 focus:ring-1 focus:ring-blue-500 max-w-[120px]"
                    >
                        <option value="all">Encargado</option>
                        <option value="unassigned">⚠️ Sin asignar</option>
                        <option v-for="tech in encargadosList" :key="tech.id" :value="tech.id">
                            {{ tech.name }}
                        </option>
                    </select>

                    <!-- Limpiar -->
                    <button 
                        v-if="searchQuery || filterPriority !== 'all' || filterType !== 'all' || filterEncargado !== 'all'"
                        @click="searchQuery = ''; filterPriority = 'all'; filterType = 'all'; filterEncargado = 'all'"
                        class="p-1 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded"
                        title="Limpiar filtros"
                    >
                        <i class='bx bx-x text-base'></i>
                    </button>
                </div>
            </div>

            <!-- Tablero Kanban: Columnas Activas con Altura Calculada Dinámica -->
            <div class="flex gap-2 overflow-x-auto custom-kanban-scroll pb-2 items-start">
                <div 
                    v-for="col in displayedColumns" 
                    :key="col.key"
                    style="height: calc(100vh - 14.5rem); max-height: calc(100vh - 14.5rem);"
                    class="min-w-[240px] flex-1 max-w-[340px] rounded-lg border border-gray-200 dark:border-gray-700/80 bg-gray-50/60 dark:bg-gray-800/30 p-2 flex flex-col shadow-xs"
                >
                    <!-- Cabecera de Columna Fija -->
                    <div class="flex items-center justify-between pb-1.5 mb-2 border-b border-gray-200 dark:border-gray-700/80 flex-shrink-0">
                        <div class="flex items-center gap-1.5">
                            <span class="p-1 rounded text-sm" :class="col.badge">
                                <i :class="col.icon"></i>
                            </span>
                            <span class="font-bold text-xs text-gray-900 dark:text-gray-100 tracking-tight">{{ col.label }}</span>
                        </div>
                        <span class="px-1.5 py-0.2 rounded-full text-[11px] font-bold" :class="col.badge">
                            {{ columnCounts[col.key] }}
                        </span>
                    </div>

                    <!-- Lista de Tarjetas con Scroll Interno Ultradelgado -->
                    <div class="flex-1 min-h-0 space-y-2 overflow-y-auto pr-0.5 custom-kanban-scroll">
                        <template v-for="ticket in filteredTickets" :key="ticket.id">
                            <div 
                                v-if="ticket.estado_actual === col.key"
                                class="bg-white dark:bg-gray-800 rounded-lg p-2.5 border border-gray-200 dark:border-gray-700/90 shadow-xs hover:shadow-sm transition-all duration-100 relative group"
                                :class="{ 
                                    'border-l-3 border-l-rose-500': ticket.prioridad === 'critica', 
                                    'z-30': activeDropdown.ticketId === ticket.id,
                                    'ring-2 ring-blue-500 shadow-md scale-[1.01] transition-all duration-300': highlightedTicketId === ticket.id
                                }"
                            >
                                <!-- Fila Superior: Código, Tipo y Prioridad Rápida -->
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <div class="flex items-center gap-1">
                                        <span class="text-[10px] font-bold text-gray-400">#{{ ticket.id }}</span>
                                        
                                        <!-- Tipo: Interactivo si está activo, Estático si está bloqueado -->
                                        <div v-if="!isTicketLocked(ticket)" class="relative">
                                            <button 
                                                type="button"
                                                @click="toggleDropdown(ticket.id, 'type', $event)"
                                                class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors flex items-center gap-0.5 cursor-pointer"
                                                title="Cambiar tipo"
                                            >
                                                {{ typeLabels[ticket.tipo] || ticket.tipo }}
                                                <i class='bx bx-chevron-down text-[9px]'></i>
                                            </button>

                                            <!-- Popover de Tipo -->
                                            <div 
                                                v-if="activeDropdown.ticketId === ticket.id && activeDropdown.type === 'type'"
                                                class="fast-dropdown-popover absolute left-0 top-full mt-1 z-50 w-36 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl py-1 text-xs"
                                                @click.stop
                                            >
                                                <button 
                                                    v-for="(label, key) in typeLabels" 
                                                    :key="key"
                                                    @click="setTicketType(ticket, key)"
                                                    class="w-full text-left px-2.5 py-1 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between text-[11px] transition-colors"
                                                    :class="{ 'font-bold text-blue-600 dark:text-blue-400': ticket.tipo === key }"
                                                >
                                                    <span>{{ label }}</span>
                                                    <i v-if="ticket.tipo === key" class='bx bx-check text-sm'></i>
                                                </button>
                                            </div>
                                        </div>
                                        <span v-else class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                            {{ typeLabels[ticket.tipo] || ticket.tipo }}
                                        </span>
                                    </div>

                                    <!-- Prioridad: Interactiva si está activo, Estática si está bloqueado -->
                                    <div v-if="!isTicketLocked(ticket)" class="relative">
                                        <button 
                                            type="button"
                                            @click="toggleDropdown(ticket.id, 'priority', $event)"
                                            class="px-1.5 py-0.2 rounded-full text-[10px] font-semibold border flex items-center gap-1 transition-transform hover:scale-105 cursor-pointer"
                                            :class="priorityConfig[ticket.prioridad]?.color || 'bg-gray-100 text-gray-800'"
                                            title="Cambiar prioridad"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full" :class="priorityConfig[ticket.prioridad]?.dot"></span>
                                            {{ priorityConfig[ticket.prioridad]?.label || ticket.prioridad }}
                                            <i class='bx bx-chevron-down text-[9px]'></i>
                                        </button>

                                        <!-- Popover de Prioridad -->
                                        <div 
                                            v-if="activeDropdown.ticketId === ticket.id && activeDropdown.type === 'priority'"
                                            class="fast-dropdown-popover absolute right-0 top-full mt-1 z-50 w-32 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl py-1 text-xs"
                                            @click.stop
                                        >
                                            <button 
                                                v-for="(cfg, prio) in priorityConfig" 
                                                :key="prio"
                                                @click="setTicketPriority(ticket, prio)"
                                                class="w-full text-left px-2.5 py-1 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between text-[11px] transition-colors"
                                                :class="{ 'font-bold': ticket.prioridad === prio }"
                                            >
                                                <span class="flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 rounded-full" :class="cfg.dot"></span>
                                                    {{ cfg.label }}
                                                </span>
                                                <i v-if="ticket.prioridad === prio" class='bx bx-check text-sm text-blue-600'></i>
                                            </button>
                                        </div>
                                    </div>
                                    <span 
                                        v-else
                                        class="px-1.5 py-0.2 rounded-full text-[10px] font-semibold border flex items-center gap-1 opacity-80"
                                        :class="priorityConfig[ticket.prioridad]?.color || 'bg-gray-100 text-gray-800'"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="priorityConfig[ticket.prioridad]?.dot"></span>
                                        {{ priorityConfig[ticket.prioridad]?.label || ticket.prioridad }}
                                    </span>
                                </div>

                                <!-- Título del Ticket -->
                                <h4 
                                    @click="openTicketDetail(ticket)"
                                    class="font-semibold text-xs text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400 cursor-pointer line-clamp-2 leading-snug mb-1"
                                >
                                    {{ ticket.titulo }}
                                </h4>

                                <!-- Descripción corta -->
                                <p v-if="ticket.descripcion" class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-1 mb-1.5">
                                    {{ ticket.descripcion }}
                                </p>

                                <!-- Información de Contexto: Solicitante y Equipo -->
                                <div class="text-[10px] text-gray-500 dark:text-gray-400 mb-2 bg-gray-50/80 dark:bg-gray-900/40 p-1.5 rounded space-y-0.5">
                                    <div class="flex items-center gap-1 truncate">
                                        <i class='bx bx-user text-gray-400 text-xs'></i>
                                        <span class="truncate font-medium text-gray-700 dark:text-gray-300">
                                            {{ ticket.usuario?.name || 'Solicitante N/A' }}
                                        </span>
                                    </div>

                                    <div v-if="ticket.equipo" class="flex items-center gap-1 truncate">
                                        <i class='bx bx-desktop text-gray-400 text-xs'></i>
                                        <span class="truncate" :title="ticket.equipo.nombre">
                                            {{ ticket.equipo.nombre }} <span class="text-[9px] text-gray-400">({{ ticket.equipo.cod_inventario }})</span>
                                        </span>
                                    </div>

                                    <div v-if="ticket.fecha_planificada" class="flex items-center gap-1 text-indigo-600 dark:text-indigo-400 font-medium">
                                        <i class='bx bx-calendar-event text-xs'></i>
                                        <span>Plan: {{ ticket.fecha_planificada }}</span>
                                    </div>
                                </div>

                                <!-- Encargado + Acciones Rápidas -->
                                <div class="flex items-center justify-between pt-1.5 border-t border-gray-100 dark:border-gray-700/80">
                                    <!-- Encargado / Técnico: Interactivo si está activo, Estático si está bloqueado -->
                                    <div v-if="!isTicketLocked(ticket)" class="relative">
                                        <button 
                                            type="button"
                                            @click="toggleDropdown(ticket.id, 'encargado', $event)"
                                            class="flex items-center gap-1 text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors p-0.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                                            title="Reasignar técnico"
                                        >
                                            <div 
                                                class="w-4.5 h-4.5 rounded-full flex items-center justify-center text-[9px] font-bold"
                                                :class="ticket.encargado ? 'bg-blue-600 text-white' : 'bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300'"
                                            >
                                                {{ ticket.encargado ? getInitials(ticket.encargado.name) : '?' }}
                                            </div>
                                            <span class="truncate max-w-[75px] text-[10px] font-medium">
                                                {{ ticket.encargado ? (ticket.encargado.name) : 'Sin asignar' }}
                                            </span>
                                            <i class='bx bx-chevron-down text-[10px] text-gray-400'></i>
                                        </button>

                                        <!-- Popover Selector de Encargado (Abre hacia abajo con z-50) -->
                                        <div 
                                            v-if="activeDropdown.ticketId === ticket.id && activeDropdown.type === 'encargado'"
                                            class="fast-dropdown-popover absolute left-0 top-full mt-1 z-50 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl p-1 text-xs"
                                            @click.stop
                                        >
                                            <div class="p-1 border-b border-gray-100 dark:border-gray-700">
                                                <input 
                                                    type="text" 
                                                    v-model="techSearchQuery" 
                                                    placeholder="Buscar técnico..."
                                                    class="w-full text-xs px-1.5 py-0.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded"
                                                    @click.stop
                                                />
                                            </div>
                                            <div class="max-h-36 overflow-y-auto py-0.5 custom-kanban-scroll">
                                                <button 
                                                    @click="setTicketEncargado(ticket, null)"
                                                    class="w-full text-left px-1.5 py-1 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 flex items-center gap-1.5 rounded text-[11px]"
                                                >
                                                    <span class="w-4 h-4 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-[9px]">✕</span>
                                                    <span>Sin asignar</span>
                                                </button>
                                                <button 
                                                    v-for="tech in filteredEncargados" 
                                                    :key="tech.id"
                                                    @click="setTicketEncargado(ticket, tech.id)"
                                                    class="w-full text-left px-1.5 py-1 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between rounded text-[11px] transition-colors"
                                                    :class="{ 'bg-blue-50 dark:bg-blue-900/40 font-semibold': ticket.id_encargado === tech.id }"
                                                >
                                                    <div class="flex items-center gap-1.5 truncate">
                                                        <div class="w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center text-[8px] font-bold flex-shrink-0">
                                                            {{ getInitials(tech.name) }}
                                                        </div>
                                                        <span class="truncate">{{ tech.name }}</span>
                                                    </div>
                                                    <i v-if="ticket.id_encargado === tech.id" class='bx bx-check text-blue-600 text-sm'></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="flex items-center gap-1 text-gray-500 dark:text-gray-400 p-0.5" title="Encargado">
                                        <div 
                                            class="w-4.5 h-4.5 rounded-full flex items-center justify-center text-[9px] font-bold"
                                            :class="ticket.encargado ? 'bg-blue-600 text-white' : 'bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300'"
                                        >
                                            {{ ticket.encargado ? getInitials(ticket.encargado.name) : '?' }}
                                        </div>
                                        <span class="truncate max-w-[80px] text-[10px] font-medium">
                                            {{ ticket.encargado ? (ticket.encargado.name) : 'Sin asignar' }}
                                        </span>
                                    </div>

                                    <!-- Selector Rápido de Estado / Acciones (Solo para tickets no bloqueados) -->
                                    <div v-if="!isTicketLocked(ticket)" class="flex items-center gap-0.5">
                                        <button 
                                            v-if="ticket.estado_actual === 'abierto'"
                                            @click="applyDirectStatusChange(ticket, 'en_proceso')"
                                            class="px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 hover:bg-amber-100 text-[10px] font-semibold flex items-center gap-0.5 cursor-pointer"
                                            title="Atender"
                                        >
                                            <i class='bx bx-play'></i> Iniciar
                                        </button>

                                        <button 
                                            v-else-if="ticket.estado_actual === 'en_proceso'"
                                            @click="handleStatusChangeClick(ticket, 'resuelto')"
                                            class="px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 text-[10px] font-semibold flex items-center gap-0.5 cursor-pointer"
                                            title="Resolver"
                                        >
                                            <i class='bx bx-check'></i> Resolver
                                        </button>

                                        <!-- Menú desplegable de otros estados -->
                                        <div class="relative">
                                            <button 
                                                type="button"
                                                @click="toggleDropdown(ticket.id, 'status', $event)"
                                                class="p-0.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                                                title="Opciones de estado"
                                            >
                                                <i class='bx bx-dots-vertical-rounded text-sm'></i>
                                            </button>

                                            <!-- Popover de Estados (Abre hacia abajo con z-50) -->
                                            <div 
                                                v-if="activeDropdown.ticketId === ticket.id && activeDropdown.type === 'status'"
                                                class="fast-dropdown-popover absolute right-0 top-full mt-1 z-50 w-36 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl py-1 text-xs"
                                                @click.stop
                                            >
                                                <button 
                                                    v-for="st in statusColumns" 
                                                    :key="st.key"
                                                    @click="handleStatusChangeClick(ticket, st.key)"
                                                    class="w-full text-left px-2.5 py-1 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-1.5 text-[11px] transition-colors"
                                                    :class="{ 'font-bold text-blue-600 dark:text-blue-400': ticket.estado_actual === st.key }"
                                                >
                                                    <i :class="st.icon"></i>
                                                    <span>{{ st.label }}</span>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Tiempo relativo -->
                                        <span class="text-[9px] text-gray-400 ml-1">
                                            {{ formatRelativeTime(ticket.fecha_creacion) }}
                                        </span>
                                    </div>

                                    <!-- Indicador de Ticket Finalizado / Bloqueado -->
                                    <div v-else class="flex items-center gap-1 text-[10px] text-gray-400 font-medium">
                                        <span class="flex items-center gap-0.5 px-1.5 py-0.2 rounded bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-[9px]">
                                            <i class='bx bx-lock-alt text-[10px]'></i> Finalizado
                                        </span>
                                        <span class="text-[9px] text-gray-400">
                                            {{ formatRelativeTime(ticket.fecha_creacion) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Estado vacío en la columna -->
                        <div 
                            v-if="columnCounts[col.key] === 0"
                            class="h-24 border-2 border-dashed border-gray-200 dark:border-gray-700/60 rounded-lg flex flex-col items-center justify-center text-gray-400 text-xs gap-0.5"
                        >
                            <i :class="col.icon" class="text-base opacity-40"></i>
                            <span class="text-[10px]">Sin tickets</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL DE RESOLUCIÓN / CIERRE / MANTENIMIENTO -->
        <div 
            v-if="showResolveModal" 
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-3 overflow-y-auto"
            @click.self="showResolveModal = false"
        >
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-100">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span 
                            class="w-7 h-7 rounded-lg flex items-center justify-center text-base"
                            :class="resolveForm.targetStatus === 'resuelto' ? 'bg-emerald-100 text-emerald-700' : (resolveForm.targetStatus === 'cerrado' ? 'bg-purple-100 text-purple-700' : 'bg-rose-100 text-rose-700')"
                        >
                            <i :class="resolveForm.targetStatus === 'resuelto' ? 'bx bx-check-circle' : (resolveForm.targetStatus === 'cerrado' ? 'bx bx-lock-alt' : 'bx bx-x-circle')"></i>
                        </span>
                        <div>
                            <h3 class="font-bold text-sm text-gray-900 dark:text-gray-100">
                                {{ resolveForm.targetStatus === 'resuelto' ? 'Resolver Ticket' : (resolveForm.targetStatus === 'cerrado' ? 'Cerrar Ticket' : 'Cancelar Ticket') }} #{{ resolveForm.ticket?.id }}
                            </h3>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ resolveForm.ticket?.titulo }}</p>
                        </div>
                    </div>
                    <button @click="showResolveModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg">&times;</button>
                </div>

                <form @submit.prevent="submitResolveForm" class="p-4 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Observación de la Solución / Motivo:
                        </label>
                        <textarea 
                            v-model="resolveForm.observacion_resolucion"
                            rows="2"
                            placeholder="Describa el trabajo realizado o motivo..."
                            class="w-full text-xs bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg p-2 text-gray-900 dark:text-gray-100 focus:ring-1 focus:ring-blue-500"
                        ></textarea>
                    </div>

                    <!-- Opción de Registro Automático de Mantenimiento -->
                    <div v-if="resolveForm.ticket?.id_equipo" class="bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-lg p-2.5 space-y-2">
                        <label class="flex items-start gap-2 cursor-pointer">
                            <input 
                                type="checkbox" 
                                v-model="resolveForm.create_maintenance"
                                class="mt-0.5 w-3.5 h-3.5 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                            />
                            <div>
                                <span class="text-xs font-bold text-blue-900 dark:text-blue-200 block">
                                    Registrar Mantenimiento al Equipo
                                </span>
                                <span class="text-[10px] text-blue-700 dark:text-blue-300">
                                    {{ resolveForm.ticket?.equipo?.nombre }} ({{ resolveForm.ticket?.equipo?.cod_inventario }})
                                </span>
                            </div>
                        </label>

                        <div v-if="resolveForm.create_maintenance" class="pt-2 border-t border-blue-200/60 dark:border-blue-800/60 space-y-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-blue-950 dark:text-blue-200 mb-1">
                                    Tipo de Mantenimiento:
                                </label>
                                <select 
                                    v-model="resolveForm.maintenance_type"
                                    class="w-full text-xs bg-white dark:bg-gray-900 border border-blue-300 dark:border-blue-700 rounded-lg py-1.5 px-2.5 text-gray-900 dark:text-gray-100 focus:ring-1 focus:ring-blue-500 font-medium"
                                >
                                    <option value="preventivo">Preventivo (Planificado / Rutinario)</option>
                                    <option value="correctivo">Correctivo (Reparación / Falla)</option>
                                    <option value="predictivo">Predictivo (Monitoreo / Diagnóstico)</option>
                                    <option value="otro">Otro Tipo</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-blue-950 dark:text-blue-200 mb-1">
                                    Detalle / Trabajo Realizado (Opcional):
                                </label>
                                <input 
                                    type="text"
                                    v-model="resolveForm.maintenance_observation"
                                    placeholder="Detalle adicional del mantenimiento..."
                                    class="w-full text-xs bg-white dark:bg-gray-900 border border-blue-300 dark:border-blue-700 rounded-lg py-1.5 px-2.5 text-gray-900 dark:text-gray-100 focus:ring-1 focus:ring-blue-500"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <button 
                            type="button" 
                            @click="showResolveModal = false"
                            class="px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 rounded-lg transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            :disabled="resolveForm.isSubmitting"
                            class="px-3 py-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-xs transition-colors disabled:opacity-50 flex items-center gap-1"
                        >
                            <i v-if="resolveForm.isSubmitting" class='bx bx-loader-alt animate-spin'></i>
                            <span>Guardar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL DE DETALLE COMPLETO DEL TICKET -->
        <div 
            v-if="showDetailModal && selectedTicket" 
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-3 overflow-y-auto"
            @click.self="showDetailModal = false"
        >
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl w-full max-w-xl overflow-hidden animate-in fade-in zoom-in-95 duration-100">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">Ticket #{{ selectedTicket.id }}</span>
                        <span 
                            class="px-1.5 py-0.2 rounded-full text-[10px] font-semibold border"
                            :class="priorityConfig[selectedTicket.prioridad]?.color"
                        >
                            {{ priorityConfig[selectedTicket.prioridad]?.label }}
                        </span>
                        <span class="px-1.5 py-0.2 rounded text-[10px] bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium">
                            {{ typeLabels[selectedTicket.tipo] || selectedTicket.tipo }}
                        </span>
                    </div>
                    <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg">&times;</button>
                </div>

                <div class="p-4 space-y-3 max-h-[75vh] overflow-y-auto custom-kanban-scroll">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-gray-100 mb-0.5">{{ selectedTicket.titulo }}</h2>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">
                            Creado el {{ formatDate(selectedTicket.fecha_creacion) }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-0.5">Descripción:</label>
                        <div class="bg-gray-50 dark:bg-gray-900/60 p-2.5 rounded-lg text-xs text-gray-800 dark:text-gray-200 whitespace-pre-wrap border border-gray-200/60 dark:border-gray-700/60">
                            {{ selectedTicket.descripcion || 'Sin descripción detallada.' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                        <div class="bg-gray-50 dark:bg-gray-900/40 p-2.5 rounded-lg border border-gray-200/60 dark:border-gray-700/60 space-y-0.5">
                            <span class="font-bold text-gray-700 dark:text-gray-300 block mb-0.5">👤 Solicitante</span>
                            <p><strong>Nombre:</strong> {{ selectedTicket.usuario?.name || 'N/A' }}</p>
                            <p><strong>Email:</strong> {{ selectedTicket.usuario?.email || 'N/A' }}</p>
                            <p><strong>Teléfono:</strong> {{ selectedTicket.usuario?.telefono || 'N/A' }}</p>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-900/40 p-2.5 rounded-lg border border-gray-200/60 dark:border-gray-700/60 space-y-0.5">
                            <span class="font-bold text-gray-700 dark:text-gray-300 block mb-0.5">🖥️ Equipo</span>
                            <p v-if="selectedTicket.equipo">
                                <strong>Equipo:</strong> {{ selectedTicket.equipo.nombre }} ({{ selectedTicket.equipo.cod_inventario }})
                            </p>
                            <p v-if="selectedTicket.equipo?.area">
                                <strong>Área:</strong> {{ selectedTicket.equipo.area.nombre }}
                            </p>
                            <p v-else-if="!selectedTicket.equipo" class="text-gray-400">Sin equipo asignado</p>
                        </div>
                    </div>

                    <div v-if="selectedTicket.observacion_resolucion" class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 p-2.5 rounded-lg text-xs space-y-0.5">
                        <span class="font-bold text-emerald-800 dark:text-emerald-300 flex items-center gap-1">
                            <i class='bx bx-check-circle'></i> Observación de Resolución
                        </span>
                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ selectedTicket.observacion_resolucion }}</p>
                        <p class="text-[10px] text-emerald-600 dark:text-emerald-400 pt-0.5">
                            Resuelto: {{ formatDate(selectedTicket.fecha_resolucion) }}
                        </p>
                    </div>

                    <div v-if="selectedTicket.mantenimientos && selectedTicket.mantenimientos.length > 0" class="border-t border-gray-200 dark:border-gray-700 pt-2">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Mantenimientos Registrados:</label>
                        <div class="space-y-1.5">
                            <div v-for="m in selectedTicket.mantenimientos" :key="m.id" class="p-2 bg-gray-50 dark:bg-gray-900 rounded text-xs border border-gray-200 dark:border-gray-700 flex items-start justify-between">
                                <div>
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <span v-if="m.tipo === 'preventivo'" class="px-1.5 py-0.5 rounded text-[9px] bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-200 font-semibold border border-emerald-300 dark:border-emerald-700">
                                            Preventivo
                                        </span>
                                        <span v-else-if="m.tipo === 'correctivo'" class="px-1.5 py-0.5 rounded text-[9px] bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-200 font-semibold border border-amber-300 dark:border-amber-700">
                                            Correctivo
                                        </span>
                                        <span v-else-if="m.tipo === 'predictivo'" class="px-1.5 py-0.5 rounded text-[9px] bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-200 font-semibold border border-blue-300 dark:border-blue-700">
                                            Predictivo
                                        </span>
                                        <span v-else class="px-1.5 py-0.5 rounded text-[9px] bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 font-semibold border border-gray-300 dark:border-gray-600">
                                            {{ m.tipo || 'Preventivo' }}
                                        </span>
                                    </div>
                                    <span class="font-medium text-gray-800 dark:text-gray-200 text-[11px]">{{ m.observacion }}</span>
                                    <span class="text-[9px] text-gray-400 block mt-0.5">{{ formatDate(m.fecha) }}</span>
                                </div>
                                <span class="px-1.5 py-0.5 rounded text-[9px] bg-green-100 text-green-800 dark:bg-green-900/60 dark:text-green-300 font-semibold">
                                    Registrado
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                    <button 
                        @click="showDetailModal = false"
                        class="px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>

        <!-- Notificación Flotante Toast -->
        <transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="transform translate-y-4 opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="transform translate-y-0 opacity-100"
            leave-to-class="transform translate-y-4 opacity-0"
        >
            <div 
                v-if="toastVisible" 
                class="fixed bottom-4 right-4 z-50 flex items-center gap-2 bg-gray-900/95 dark:bg-gray-100 text-white dark:text-gray-900 px-3.5 py-2 rounded-lg shadow-xl text-xs font-medium backdrop-blur-xs border border-gray-700/50"
            >
                <i class='bx bx-info-circle text-base text-blue-400 dark:text-blue-600'></i>
                <span>{{ toastMessage }}</span>
            </div>
        </transition>
    </dashboard>
</template>

<style scoped>
.fast-dropdown-popover {
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
}

/* Custom ultrathin scrollbar */
.custom-kanban-scroll::-webkit-scrollbar {
    width: 4px;
    height: 4px;
}
.custom-kanban-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.custom-kanban-scroll::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.3);
    border-radius: 4px;
}
.custom-kanban-scroll:hover::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.6);
}
</style>
