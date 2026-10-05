<script setup>

    import dashboard from '../../layouts/dashboard.vue';
    import SubNavbar from '../../components/SubNavbar.vue';
    import INPUT from '../../components/inputs/input.vue';
    import SELECT from '../../components/inputs/select.vue';
    import TEXTAREA from '../../components/inputs/textarea.vue';
    import { useForm } from '@inertiajs/vue3';
    
    const links = [
        { route: 'requests.index', name: 'Solicitudes', active: true },
        { route: 'requests.history', name: 'Historial', active: false },
    ];

    const props = defineProps({
        user_equipos: {
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

    const form = useForm({
        titulo: '',
        description: '',
        tipo: 'hardware',
        id_equipo: null,
    });

    const submitForm = () => {
        form.post(route('requests.store'), {
            preserveScroll: true,
            onSuccess: (response) => {
                form.reset();
            },
        });
    };
</script>

<template>
    <dashboard :appName="'tickets_app'">
        <SubNavbar :links="links" />
        <div class="container px-4 pt-4 flex flex-col items-center justify-center mx-auto">
            <div class="w-full mt-4" style="max-width: 900px;">
                <div class="flex flex-col sm:flex-row justify-center items-center mb-4 gap-4 sm:gap-0">
                    <h5 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Crear Solicitud</h5>
                </div>
            </div>

            <form action="" class="p-3 mt-2 w-full" style="max-width: 900px;" @submit.prevent="submitForm">
                <div class="flex flex-wrap -mx-2">
                    <INPUT 
                        v-model:value="form.titulo"
                        :field="{ label: 'Título', name: 'titulo', id: 'titulo', type: 'text', required: true, placeholder: 'Ingrese el título de la solicitud' }"
                    ></INPUT>

                    <TEXTAREA 
                        v-model:value="form.description"
                        :field="{ label: 'Descripción', name: 'description', id: 'description', required: true, placeholder: 'Describa detalladamente la solicitud' }"
                    ></TEXTAREA>

                    <SELECT 
                        v-model:value="form.tipo"
                        :field="{ options: types,
                            label: 'Tipo de Solicitud', name: 'tipo', id: 'tipo', required: true, placeholder: '¿Que tipo de problema está experimentando?' }"
                    ></SELECT>

                    <SELECT 
                        v-model:value="form.id_equipo"
                        :field="{ options: user_equipos,
                            label: 'Equipo', name: 'id_equipo', id: 'id_equipo', required: false, placeholder: 'Seleccione el equipo' }"
                    ></SELECT>

                    <div class="w-full px-2 mt-5 flex justify-between items-center">
                        <button 
                            type="submit" 
                            class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
                        >
                            Crear Solicitud
                        </button>
                    </div>    
                </div>
            </form>
        </div>
    </dashboard>
</template>