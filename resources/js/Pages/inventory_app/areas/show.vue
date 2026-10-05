// This script handles the areas listing page.
<script setup>

    import dashboard from '../../layouts/dashboard.vue';
    import SubNavbar from '../../components/SubNavbar.vue';
    import DeleteModal from '../../components/DeleteModal.vue';
    import Table from '../../components/Table.vue';
    import Form from '../../components/Form.vue';
    import ExcelExport from '../../components/buttons/ExcelExport.vue';
    import Print from '../../components/buttons/Print.vue';
    import SearchSelect from '../../components/inputs/searchSelect.vue';
    import Input from '../../components/inputs/input.vue';
    import Select from '../../components/inputs/select.vue';
    import Modal from '../../components/Modal.vue';
    import Tabs from '../../components/Tabs.vue';
    import FormattedDate from '../../components/accounting/FormattedDate.vue';
    import { usePage, useForm, Link } from '@inertiajs/vue3';
    import { computed, onMounted, ref, watch } from 'vue';
    
    const links = [
        { route: 'areas.index', name: 'Áreas', active: true },
    ];

    const area = ref(usePage().props.area);
    defineProps(['historialEncargados']);

    function submitForm(){
        let form = useForm({
            id_usuario_encargado: area.value.id_usuario_encargado,
            estado: area.value.estado,
            nombre: area.value.nombre,
            descripcion: area.value.descripcion,
        });

        form.put(route('areas.update', { area: area.value.id }), {
            onSuccess: (response) => {
                area.value = response.props.area;
            },
        });
    }
</script>

<template>
    <dashboard :appName="'inventory_app'">
        <SubNavbar :links="links" />
        
        <div class="container mx-auto px-4 mt-8">
            <Tabs :tabs="[
                { id: 'edit', label: 'Editar' },
                { id: 'history', label: 'Historial' }
            ]" storageKey="areas-management-tabs">
                <template #edit>
                    <div class="container mx-auto px-4">
                        <div class="w-full">
                            <h4>Editar Área</h4>
                            <hr class="mb-4">
                            <form class="space-y-4 p-3 shadow rounded" method="POST" autocomplete="off" @submit.prevent="submitForm">
                                <div class="flex flex-wrap -mx-2">
                                    <Input :field="{
                                        id: 'nombre',
                                        label: 'Nombre',
                                        required: true,
                                        type: 'text',
                                        name: 'nombre',
                                        readonly: false,
                                    }" v-model:value="area.nombre"/>
                                    <Input :field="{
                                        id: 'descripcion',
                                        label: 'Descripción',
                                        required: false,
                                        type: 'text',
                                        name: 'descripcion',
                                        readonly: false,
                                    }" v-model:value="area.descripcion"/>
                                    <Select :field="{
                                        id: 'estado',
                                        label: 'Estado',
                                        required: true,
                                        name: 'estado',
                                        readonly: false,
                                        options: {
                                            'activo': 'Activo',
                                            'inactivo': 'Inactivo',
                                        },
                                    }" v-model:value="area.estado"/>
                                    <div class="w-full md:w-1/2 mt-3 px-2">
                                        <SearchSelect 
                                            name="Encargado" 
                                            input-name="id_usuario_encargado" 
                                            :model="'User'" 
                                            :required="false" 
                                            select_name="id_usuario_encargado"
                                            :default-value="area.encargado ? {id: area.encargado.id, name: area.encargado.name} : null"
                                            v-model:select_value="area.id_usuario_encargado"
                                        />
                                    </div>
                                </div>
                                <div class="px-3 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200 dark:border-gray-700">
                                    <button type="submit"
                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                        Aceptar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </template>

                <template #history>
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 transition-colors">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm" id="'history_table'">
                            <thead class="bg-blue-100 dark:bg-gray-900">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap transition-colors">
                                        Usuario Encargado
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap transition-colors">
                                        Fecha de Asignación
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap transition-colors">
                                        Fecha de Desasignación
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 transition-colors">
                                <tr v-for="historial in historialEncargados.data" :key="historial.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700 even:bg-gray-50/50 dark:even:bg-gray-800/50">
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300 transition-colors">
                                        {{ historial.encargado.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300 transition-colors">
                                        <FormattedDate :date="historial?.fecha_asignacion.split(' ')[0]" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300 transition-colors">
                                        <FormattedDate :date="historial?.fecha_fin.split(' ')[0]" v-if="historial.fecha_fin"/>
                                        <span v-else class="text-gray-500 dark:text-gray-400">N/A</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="m-2 flex justify-center sm:justify-start">
                            <nav aria-label="Navegación de páginas">
                                <ul class="inline-flex -space-x-px rounded-md shadow-sm select-none text-sm">
                                    <li>
                                        <Link
                                            v-if="historialEncargados.prev_page_url" :href="historialEncargados.prev_page_url"
                                            preserve-scroll preserve-state :only="['historialEncargados']" replace
                                            class="inline-flex items-center px-4 py-2 text-sm font-medium border rounded-l-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" 
                                            >
                                            Anterior
                                        </Link>
                                        <button
                                            v-else
                                            class="inline-flex items-center px-4 py-2 text-sm font-medium border rounded-l-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors opacity-50 cursor-not-allowed" 
                                            >
                                            Anterior
                                        </button>
                                    </li>
                                    <li>
                                        <Link 
                                            v-if="historialEncargados.next_page_url" :href="historialEncargados.next_page_url"
                                            preserve-scroll preserve-state :only="['historialEncargados']" replace
                                            class="inline-flex items-center px-4 py-2 text-sm font-medium border border-l-0 rounded-r-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                            >
                                            Siguiente
                                        </Link>
                                        <button 
                                            v-else
                                            class="inline-flex items-center px-4 py-2 text-sm font-medium border border-l-0 rounded-r-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors opacity-50 cursor-not-allowed"
                                            >
                                            Siguiente
                                        </button>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </template>
            </Tabs>
        </div>
    </dashboard>
</template>