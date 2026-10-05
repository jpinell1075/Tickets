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
    import INPUT from '../../components/inputs/input.vue';
    import SELECT from '../../components/inputs/select.vue';
    import Modal from '../../components/Modal.vue';
    import { usePage, useForm } from '@inertiajs/vue3';
    import { computed, onMounted, ref, watch, defineAsyncComponent } from 'vue';
    import { Caracteristics } from '../../../Composables/Caracteristics.js';
    const { available_caracteristics, available_components, types_available_values } = Caracteristics();

    const componentMap = {
        INPUT,
        SELECT,
    };
    
    const links = [
        { route: 'equipos.index', name: 'Equipos', active: true },
    ];

    const modalRef = ref(null);

    const form = useForm({
        id_usuario: null,
        id_area: null,
        nombre: null,
        marca: null,
        modelo: null,
        num_serie: null,
        cod_inventario: null,
        estado_funcional: null,
        estado: null,
        tipo: null,
        caracteristicas: {},
        componentes: {},
    });

    function getComponentGroup(groupKey) {
        if (!form.componentes[groupKey]) {
            form.componentes[groupKey] = {};
        }

        return form.componentes[groupKey];
    }

    function callback(){
        document.getElementById('AddForm').dispatchEvent(new Event('submit', { cancelable: true }));
    }

    function submitForm(){
        form.post(route('equipos.store'), {
            onSuccess: () => {
                window.atm_tables?.forEach((table) => {
                    table.refresh();
                });
                modalRef.value.closeModal();
            },
        });
    }

    watch(() => form.tipo, (newTipo) => {
        if (newTipo) {
            form.caracteristicas = {};
            form.componentes = {};
        }
    });
</script>

<template>
    <dashboard :appName="'inventory_app'">
        <SubNavbar :links="links" />
        <div class="container px-4 pt-4 mx-auto">
            <Modal :title="'Crear Equipo'" :id="'AddModal'" ref="modalRef" :accept-callback="callback">
                <form id="AddForm" @submit.prevent="submitForm" class="space-y-4 px-2 pb-2">
                    <div class="flex flex-wrap -mx-2">
                        <div class="w-full md:w-1/2 mt-3 px-2">
                            <SearchSelect 
                                name="Usuario" 
                                input-name="id_usuario" 
                                :model="'User'" 
                                :required="false" 
                                select_name="id_usuario"
                                v-model:select_value="form.id_usuario"
                            />
                        </div>
                        <div class="w-full md:w-1/2 mt-3 px-2">
                            <SearchSelect 
                                name="Área" 
                                input-name="id_area" 
                                :model="'Area'" 
                                :required="false" 
                                select_name="id_area"
                                v-model:select_value="form.id_area"
                            />
                        </div>
                        <INPUT :field="{
                            id: 'nombre',
                            label: 'Nombre',
                            required: false,
                            type: 'text',
                            name: 'nombre',
                            readonly: false,
                        }" v-model:value="form.nombre"/>
                        <INPUT :field="{
                            id: 'marca',
                            label: 'Marca',
                            required: false,
                            type: 'text',
                            name: 'marca',
                            readonly: false,
                        }" v-model:value="form.marca"/>
                        <INPUT :field="{
                            id: 'modelo',
                            label: 'Modelo',
                            required: false,
                            type: 'text',
                            name: 'modelo',
                            readonly: false,
                        }" v-model:value="form.modelo"/>
                        <INPUT :field="{
                            id: 'num_serie',
                            label: 'Número de Serie',
                            required: false,
                            type: 'text',
                            name: 'num_serie',
                            readonly: false,
                        }" v-model:value="form.num_serie"/>
                        <INPUT :field="{
                            id: 'cod_inventario',
                            label: 'Código de Inventario',
                            required: false,
                            type: 'text',
                            name: 'cod_inventario',
                            readonly: false,
                        }" v-model:value="form.cod_inventario"/>
                        <SELECT :field="{
                            id: 'estado_funcional',
                            label: 'Estado Funcional',
                            required: true,
                            name: 'estado_funcional',
                            readonly: false,
                            options: {
                                'funcional': 'Funcional',
                                'no_funcional': 'No Funcional',
                            },
                        }" v-model:value="form.estado_funcional"/>
                        <SELECT :field="{
                            id: 'estado',
                            label: 'Estado',
                            required: true,
                            name: 'estado',
                            readonly: false,
                            options: {
                                'activo': 'Activo',
                                'inactivo': 'Inactivo',
                                'mantenimiento': 'Mantenimiento',
                            },
                        }" v-model:value="form.estado"/>
                        <SELECT :field="{
                            id: 'tipo',
                            label: 'Tipo',
                            required: true,
                            name: 'tipo',
                            readonly: false,
                            options: {
                                'cpu': 'CPU',
                                'monitor': 'MONITOR',
                                'ups': 'BATERIAS UPS / ESTABILIZADOR',
                                'switches': 'SWITCHES',
                                'aps': 'CONEXIÓN INALÁMBRICA (AP´S)',
                                'telefonia_ip': 'TELEFONIA IP',
                                'impresoras': 'IMPRESORA, FOTOCOPIADORA (IMPRESORAS MATRICIALES)',
                                'reloj_biometrico': 'RELOJ BIOMETRICO',
                                'data_show': 'DATA SHOW',
                                'pantalla_proyector': 'PANTALLA DE PROYECCIÓN',
                                'laptops': 'LAPTOPS',
                            },
                        }" v-model:value="form.tipo"/>
                        <template v-for="(caracteristica, key) in available_caracteristics" :key="key">
                            <component 
                                :is="componentMap[caracteristica.component]" 
                                :field="{
                                    id: caracteristica.name,
                                    label: caracteristica.label,
                                    required: false,
                                    type: caracteristica.type || 'text',
                                    name: caracteristica.name,
                                    readonly: false,
                                    options: caracteristica.options || null,
                                }" 
                                v-model:value="form.caracteristicas[key]"
                                v-if="form.tipo && types_available_values[form.tipo].characteristics.includes(key)"
                            />
                        </template>
                        <template v-for="(componente, key) in available_components" :key="key">
                            <template v-if="form.tipo && types_available_values[form.tipo].components.includes(key)">
                                <div class="w-full mt-3 px-2">
                                    <h5>{{ componente.label }}</h5>
                                </div>
                                <template v-for="(comp, k) in componente.options" :key="comp.name">
                                    <component 
                                        :is="componentMap[comp.component]" 
                                        :field="{
                                            id: comp.name,
                                            label: comp.label,
                                            required: false,
                                            type: comp.type || 'text',
                                            name: comp.name,
                                            readonly: false,
                                            options: comp.options || null,
                                        }" 
                                        v-model:value="getComponentGroup(key)[comp.name]"
                                    />
                                </template>
                            </template>
                        </template>
                    </div>
                </form>
            </Modal>

            <div class="w-full mt-4">
                <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4 sm:gap-0">
                    <h5 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Equipos</h5>
                    
                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded transition-colors" 
                            @click="$refs.modalRef.openModal()"
                            aria-label="Agregar Equipo"
                        >
                            <i class='bx bx-plus'></i>
                        </button>
                    </div>
                </div>
                
                <Table :model="usePage().props.model" :options="['delete']" :id="'equipos'"/>
            </div>
        </div>
    </dashboard>
</template>