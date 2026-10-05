<script setup>

    import dashboard from '../../layouts/dashboard.vue';
    import SubNavbar from '../../components/SubNavbar.vue';
    import Print from '../../components/buttons/Print.vue';
    import { usePage } from '@inertiajs/vue3';
    import { ref, computed } from 'vue';
    const links = [
        { route: 'maintenance_plans.index', name: 'Planes de Mantenimiento', active: true },
    ];

    const planificacion = ref(usePage().props.planificacion);
    const tickets = computed(() => {
        return planificacion.value.tickets.reduce((acc, ticket) => {
            const fechaPlanificada = new Date(ticket.fecha_planificada).toLocaleDateString('es-Ni');
            if (!acc[fechaPlanificada]) {
                acc[fechaPlanificada] = [];
            }
            acc[fechaPlanificada].push(ticket);
            return acc;
        }, {});
    });

    const types = {
        hardware: 'Problemas con los dispositivos físicos (computadoras, impresoras, etc.)',
        software: 'Problemas con aplicaciones del sistema (word, correo, navegadores, etc.)',
        network: 'Problemas con la conexión a internet',
        other: 'Otro tipo de problema',
    };
</script>

<template>
    <dashboard :appName="'tickets_app'">
        <SubNavbar :links="links" />

        <div class="container mx-auto px-4">
            <div class="w-full">
                <div class="flex justify-between items-center mb-4">
                    <h4>
                        <strong>Área:</strong> 
                        {{ planificacion.area?.nombre }},
                        {{ new Date(planificacion.fecha_inicio).toLocaleString('es-Ni',{ year: 'numeric', month: 'long', day: 'numeric' }) }} - 
                        {{ new Date(planificacion.fecha_fin).toLocaleString('es-Ni',{ year: 'numeric', month: 'long', day: 'numeric' }) }}
                    </h4>
                    <Print 
                        :view-name="'mantenimientos.print.show'" 
                        :title="planificacion.area?.nombre ?? 'Planificación de Mantenimiento'" 
                        :page-properties="{
                            'pagedjs': true, 
                            'pagecounter': true, 
                            'openInNewTab': true, 
                            'loadBootstrap': true,
                            'margintop': '15px',
                            'marginbottom': '20px',
                            'marginleft': '15px',
                            'marginright': '15px',
                        }" 
                        :params="{planificacion_id: planificacion.id}"
                    />
                </div>
                <hr class="mb-4">
                <div v-for="(tickets, fecha) in tickets" :key="fecha" class="mb-4">
                    <h5 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ fecha }}</h5>
                    <div class="grid grid-cols-2 gap-1">
                        <div v-for="ticket in tickets" :key="ticket.id" class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                            <h6 class="font-medium text-gray-900 dark:text-gray-100">{{ ticket.titulo }}</h6>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ ticket.descripcion }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <strong>Estado:</strong> <span class="capitalize">{{ ticket.estado_actual }}</span>
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <strong>Equipo:</strong> {{ ticket.equipo?.nombre || ticket.equipo?.cod_inventario }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <strong>Usuario:</strong> {{ ticket.usuario?.name || 'No asignado' }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <strong>Encargado:</strong> {{ ticket.encargado?.name || 'No asignado' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </dashboard>
</template>