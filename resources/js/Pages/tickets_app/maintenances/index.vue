<script setup>

    import dashboard from '../../layouts/dashboard.vue';
    import SubNavbar from '../../components/SubNavbar.vue';
    import INPUT from '../../components/inputs/input.vue';
    import searchSelect from '../../components/inputs/searchSelect.vue';
    import Modal from '../../components/Modal.vue';
    import Table from '../../components/Table.vue';
    import { useForm, usePage } from '@inertiajs/vue3';
    import { ref } from 'vue';
    
    const links = [
        { route: 'maintenance_plans.index', name: 'Planes de Mantenimiento', active: true },
    ];

    const form = useForm({
        fecha_inicio: '',
        fecha_fin: '',
        id_area: null,
    });

    const modalRef = ref(null);

    function callback(){
        document.getElementById('AddForm').dispatchEvent(new Event('submit', { cancelable: true }));
    }

    const submitForm = () => {
        form.post(route('maintenance_plans.store'), {
            preserveScroll: true,
            onSuccess: (response) => {
                form.reset();
                modalRef.value.closeModal();
                window.atm_tables.forEach((table) => {
                    table.refresh();
                });
            },
        });
    };
</script>

<template>
    <dashboard :appName="'tickets_app'">
        <SubNavbar :links="links" />
        <div class="container mx-auto px-4 pt-4">
            <div class="w-full mt-4">
                <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4 sm:gap-0">
                    <h5 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Planes de Mantenimiento</h5>
    
                    <div class="flex items-center gap-2">
                        <button type="button"
                            class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded transition-colors"
                            @click="modalRef.openModal()" aria-label="Agregar Plan de Mantenimiento">
                            <i class='bx bx-plus'></i>
                        </button>
                    </div>
                </div>
                <Table :model="usePage().props.model" :options="['delete']" :id="'equipos'" :with-relations="['area']"/>
            </div>
        </div>

        <Modal :title="'Crear Plan de Mantenimiento'" :id="'AddModal'" ref="modalRef" :accept-callback="callback">
            <form id="AddForm" class="p-3 mt-2 w-full" @submit.prevent="submitForm">
                <div class="flex flex-wrap -mx-2">
                    <div class="w-full mt-3 px-2">
                        <searchSelect name="Área" input-name="id_area" :model="'Area'" :required="false"
                            select_name="id_area" v-model:select_value="form.id_area" />
                    </div>

                    <INPUT 
                        v-model:value="form.fecha_inicio"
                        :field="{ label: 'Fecha de Inicio', name: 'fecha_inicio', id: 'fecha_inicio', type: 'date', required: true }"
                    ></INPUT>

                    <INPUT 
                        v-model:value="form.fecha_fin"
                        :field="{ label: 'Fecha de Fin', name: 'fecha_fin', id: 'fecha_fin', type: 'date', required: true }"
                    ></INPUT>
                </div>
            </form>
        </Modal>
    </dashboard>
</template>