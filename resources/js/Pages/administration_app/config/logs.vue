// This script handles the user listing page.
// It imports components for displaying a table of users and a form for adding new users.
// The template includes a form for creating users and a table for listing them.
// Additional features include exporting data to Excel and printing user lists.
<script setup>

    import dashboard from '../../layouts/dashboard.vue';
    import SubNavbar from '../../components/SubNavbar.vue';
    import DeleteModal from '../../components/DeleteModal.vue';
    import Table from '../../components/Table.vue';
    import ExcelExport from '../../components/buttons/ExcelExport.vue';
    import { ref } from 'vue';
    import { usePage } from '@inertiajs/vue3';
    
    const links = [
        { route: 'config.index', name: 'Configuraciones', active: false },
        { route: 'logs.index', name: 'Logs', active: true },
    ];

    const deleteModalRef = ref(null);

    function showDeleteModal() {
        deleteModalRef.value.openDeleteModal(route('logs.cleanUserLogs'));
    }
</script>

<template>
    <dashboard :appName="'administration_app'">
        <SubNavbar :links="links" />
        <div class="container mx-auto px-4">
            <div class="w-full mt-4">
                <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4 sm:gap-0">
                    <h5 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Logs</h5>
                    
                    <div class="flex items-center gap-2">
                        <button 
                            class="inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600 text-white text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors" 
                            @click="showDeleteModal"
                        >
                            <i class="bi bi-trash mr-1.5 text-lg"></i> Limpiar Logs
                        </button>
                        
                        <ExcelExport :filename="'Logs'" :target="'logs_table'"/>
                    </div>
                </div>
                
                <Table :model="usePage().props.model" :options="['delete']" :id="'logs'"/>
            </div>
        </div>
        <DeleteModal ref="deleteModalRef"/>
    </dashboard>
</template>