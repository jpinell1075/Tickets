// This script handles the areas listing page.
<script setup>

    import dashboard from '../../layouts/dashboard.vue';
    import SubNavbar from '../../components/SubNavbar.vue';
    import Loader from '../../components/Loader.vue';
    import Print from '../../components/buttons/Print.vue';
    import SearchSelect from '../../components/inputs/searchSelect.vue';
    import Modal from '../../components/Modal.vue';
    import { usePage, useForm } from '@inertiajs/vue3';
    import { computed, onMounted, ref, watch } from 'vue';

    
    const links = [
        { route: 'inventory_reports.index', name: 'Reportes de Inventario', active: true },
    ];

    function callback(){
        document.getElementById('AddForm').dispatchEvent(new Event('submit', { cancelable: true }));
    }

    function downloadBlob(blob, name = ''){
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `${name}.xlsx`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        a.remove();
        window.URL.revokeObjectURL(url);
    }

    const isLoading = ref(false);
    function submitForm(){
        isLoading.value = true;
        let url = route('inventory_reports.generateGeneralReport');

        fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        }).then(response => {
            if (response.ok) {
                return response.blob();
            }
        }).then(blob => {
            downloadBlob(blob, 'Reporte General de Inventario.xlsx');
        }).catch(error => {
            showToast('No se pudo generar el reporte.');
        }).finally(() => {
            isLoading.value = false;
        });
    }
</script>

<template>
    <dashboard :appName="'inventory_app'">
        <SubNavbar :links="links" />
        <div class="container px-4 pt-4 mx-auto">
            <div class="w-full mt-4">
                <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4 sm:gap-0">
                    <h5 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Equipos</h5>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <tbody class="bg-white dark:bg-gray-800">
                            <tr>
                                <td>Reporte General</td>
                                <td class="text-right">
                                    <button @click="submitForm" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded transition-colors">
                                        <i class='bx bxs-file-export'></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <Loader :show="isLoading" />
    </dashboard>
</template>