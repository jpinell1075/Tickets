<script setup>

    import dashboard from '../../layouts/dashboard.vue';
    import SubNavbar from '../../components/SubNavbar.vue';
    import INPUT from '../../components/inputs/input.vue';
    import SELECT from '../../components/inputs/select.vue';
    import TEXTAREA from '../../components/inputs/textarea.vue';
    import { useForm , Link} from '@inertiajs/vue3';
    
    const links = [
        { route: 'requests.index', name: 'Solicitudes', active: false },
        { route: 'requests.history', name: 'Historial', active: true },
    ];

    const props = defineProps({
        tickets: {
            type: Object,
            required: true,
        },
    });

    const types = {
        hardware: 'Problemas con los dispositivos físicos (computadoras, impresoras, etc.)',
        software: 'Problemas con aplicaciones del sistema (word, correo, navegadores, etc.)',
        network: 'Problemas con la conexión a internet',
        other: 'Otro tipo de problema',
    };

    const paginationLabels = {
        'pagination.next': 'Siguiente',
        'pagination.previous': 'Anterior',
    };
</script>

<template>
    <dashboard :appName="'tickets_app'">
        <SubNavbar :links="links" />
        <div class="container px-4 pt-4 flex flex-col items-center justify-center mx-auto">
            <div class="w-full mt-4" style="max-width: 900px;">
                <div class="flex flex-col sm:flex-row justify-center items-center mb-4 gap-4 sm:gap-0">
                    <h5 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Historial de Solicitudes</h5>
                </div>
                <div class="grid grid-cols-1 gap-4">
                    <div v-for="ticket in tickets.data" :key="ticket.id" class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                        <h6 class="font-medium text-gray-900 dark:text-gray-100">{{ ticket.titulo }}</h6>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ ticket.descripcion }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <strong>Estado:</strong> <span class="capitalize">{{ ticket.estado_actual }}</span>
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <strong> Tipo:</strong> {{ types[ticket.tipo] }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <strong>Equipo:</strong> {{ ticket.equipo?.nombre || 'No asignado' }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <strong>Fecha de creación:</strong> {{ new Date(ticket.fecha_creacion).toLocaleString('es-Ni',{ year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: '2-digit' }) }}
                        </p>
                    </div>
                </div>
                <div v-if="tickets.links.length > 3" class="pagination-container">
                    <template v-for="(link, key) in tickets.links" :key="key">
                        <div v-if="link.url === null" class="page-link disabled"
                            v-html="paginationLabels[link.label] ?? link.label">
                        </div>

                        <Link v-else :href="link.url" class="page-link" :class="{ 'active': link.active }"
                            v-html="paginationLabels[link.label] ?? link.label" />
                    </template>
                </div>
            </div>
        </div>
    </dashboard>
</template>

<style scoped>
    .pagination-container {
        display: flex;
        justify-content: end;
        gap: 5px;
        margin-top: 20px;
    }

    .page-link {
        padding: 8px 12px;
        border: 1px solid #ddd;
        color: #333;
        text-decoration: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .page-link.active {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }

    .page-link.disabled {
        color: #aaa;
        cursor: not-allowed;
        background-color: #f9f9f9;
    }
</style>