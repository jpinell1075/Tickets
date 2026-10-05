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
    import { usePage, useForm } from '@inertiajs/vue3';
    import { computed, onMounted, ref, watch } from 'vue';
    
    const links = [
        { route: 'areas.index', name: 'Áreas', active: true },
    ];

    const modalRef = ref(null);

    const form = useForm({
        id_usuario_encargado: null,
        estado: 'activo',
        nombre: '',
        descripcion: '',
    });

    function callback(){
        document.getElementById('AddAreaForm').dispatchEvent(new Event('submit', { cancelable: true }));
    }

    function submitForm(){
        form.post(route('areas.store'), {
            onSuccess: () => {
                window.atm_tables?.forEach((table) => {
                    table.refresh();
                });
                modalRef.value.closeModal();
            },
        });
    }
</script>

<template>
    <dashboard :appName="'inventory_app'">
        <SubNavbar :links="links" />
        <div class="container px-4 pt-4 mx-auto">
            <Modal :title="'Crear Área'" :id="'AddAreaModal'" ref="modalRef" :accept-callback="callback">
                <form id="AddAreaForm" @submit.prevent="submitForm" class="space-y-4 px-2 pb-2">
                    <div class="flex flex-wrap -mx-2">
                        <Input :field="{
                            id: 'nombre',
                            label: 'Nombre',
                            required: true,
                            type: 'text',
                            name: 'nombre',
                            readonly: false,
                        }" v-model:value="form.nombre"/>
                        <Input :field="{
                            id: 'descripcion',
                            label: 'Descripción',
                            required: false,
                            type: 'text',
                            name: 'descripcion',
                            readonly: false,
                        }" v-model:value="form.descripcion"/>
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
                        }" v-model:value="form.estado"/>
                        <div class="w-full md:w-1/2 mt-3 px-2">
                            <SearchSelect 
                                name="Encargado" 
                                input-name="id_usuario_encargado" 
                                :model="'User'" 
                                :required="false" 
                                select_name="id_usuario_encargado"
                                v-model:select_value="form.id_usuario_encargado"
                            />
                        </div>
                    </div>
                </form>
            </Modal>

            <div class="w-full mt-4">
                <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4 sm:gap-0">
                    <h5 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Áreas</h5>
                    
                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded transition-colors" 
                            @click="$refs.modalRef.openModal()"
                            aria-label="Agregar Área"
                        >
                            <i class='bx bx-plus'></i>
                        </button>
                    </div>
                </div>
                
                <Table :model="usePage().props.model" :options="['delete']" :id="'areas'"/>
            </div>
        </div>
    </dashboard>
</template>