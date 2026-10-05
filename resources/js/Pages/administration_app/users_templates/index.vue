// This script handles the user templates listing page.
// It imports components for displaying a table of users and a form for adding new users templates.
<script setup>

    import dashboard from '../../layouts/dashboard.vue';
    import SubNavbar from '../../components/SubNavbar.vue';
    import DeleteModal from '../../components/DeleteModal.vue';
    import Table from '../../components/Table.vue';
    import Form from '../../components/Form.vue';
    import ExcelExport from '../../components/buttons/ExcelExport.vue';
    import Modal from '../../components/Modal.vue';
    import { usePage } from '@inertiajs/vue3';
    import { ref } from 'vue';
    
    const links = [
        { route: 'users.index', name: 'Usuarios', active: false },
        { route: 'users.templates.index', name: 'Plantillas de Usuarios', active: true },
    ];

    const modalRef = ref(null);
    const addTemplateFormRef = ref(null);

    function callback(){
        addTemplateFormRef.value.submitFormData();
    }
</script>

<template>
    <dashboard :appName="'administration_app'">
        <SubNavbar :links="links" />
        <div class="container px-4 pt-4">
            <Modal :title="'Crear Plantilla Usuario'" :id="'AddTemplateModal'" ref="modalRef" :accept-callback="callback">
                <Form :route="route('users.templates.store')" :method="'POST'" :model="usePage().props.model" :is-modal="modalRef" :show-submit-button="false" ref="addTemplateFormRef"></Form>
            </Modal>

            <div class="w-full mt-4">
                <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4 sm:gap-0">
                    <h5 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Plantillas de Usuarios</h5>
                    <div class="flex items-center gap-2">
                        <ExcelExport :filename="'Plantillas de Usuarios'" :target="'users_table'"/>
                        <button type="button" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded transition-colors"
                            @click="modalRef.openModal()"
                            aria-label="Agregar Plantilla Usuario">
                            <i class='bx bx-plus'></i>
                        </button>
                    </div>
                </div>
                <Table :model="usePage().props.model" :options="['delete']" :id="'users'"/>
            </div>
        </div>
        <DeleteModal />
    </dashboard>
</template>