<script setup>

    import dashboard from '../../layouts/dashboard.vue';
    import SubNavbar from '../../components/SubNavbar.vue';
    import Modal from '../../components/Modal.vue';

    import { usePage, useForm } from '@inertiajs/vue3';
    import { ref } from 'vue';

    
    const links = [
        { route: 'config.index', name: 'Configuraciones', active: true },
        { route: 'logs.index', name: 'Logs', active: false },
    ];

    const configs = ref(usePage().props.configurations);
    const modalRef = ref(null);

    function openUpdateModal(config) {
        updateConfigForm.id = config.id;
        updateConfigForm.name = config.name;
        updateConfigForm.type = config.type;
        updateConfigForm.value = config.typed_value;

        modalRef.value.openModal();
    }

    const updateConfigForm = useForm({
        id: '',
        name: '',
        type: '',
        value: '',
    });

    function submitForm(){
        document.getElementById('updateConfigForm').dispatchEvent(new Event('submit', { cancelable: true }));
    }

    const submitUpdateConfig = () => {
        updateConfigForm.put(route('config.update', updateConfigForm.id), {
            onSuccess: (response) => {
                configs.value = response.props.configurations;
            },
            onFinish: () => {
                modalRef.value.closeModal();
            },
        });
    };
</script>

<template>
    <dashboard :appName="'administration_app'">
        <SubNavbar :links="links" />
        <div class="container mx-auto px-4">
            <div class="w-full mt-4">
                <div class="flex justify-between items-center mb-4">
                    <h5 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Configuraciones</h5>
                </div>
            </div>
            
            <div class="w-full">
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm transition-colors">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800 transition-colors">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 tracking-wider whitespace-nowrap">
                                    Nombre
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 tracking-wider whitespace-nowrap">
                                    Valor
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 tracking-wider whitespace-nowrap">
                                    Opciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700 transition-colors">
                            <tr v-for="config in configs" :key="config.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 even:bg-gray-50/50 dark:even:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ config.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ config.type == 'boolean' ? (config.typed_value ? 'Sí' : 'No') : config.typed_value }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button 
                                        class="inline-flex items-center justify-center px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 dark:bg-yellow-600 dark:hover:bg-yellow-700 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 transition-colors" 
                                        @click="openUpdateModal(config)" 
                                        title="Editar Configuración"
                                    >
                                        <i class="bx bx-edit text-lg"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </dashboard>

    <!-- Modal -->
    <Modal :title="'Actualizar Configuración'" :id="'updateConfigModal'" ref="modalRef" :accept-callback="submitForm">
        <form id="updateConfigForm" @submit.prevent="submitUpdateConfig" class="space-y-4 px-2 pb-2">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre</label>
                <input type="text" id="name" v-model="updateConfigForm.name" disabled
                    class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500 dark:disabled:bg-gray-800/50 dark:disabled:text-gray-500 cursor-not-allowed transition-colors">
            </div>
                        
            <div v-if="updateConfigForm.type === 'string'">
                <label for="value" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor</label>
                <input type="text" id="value" v-model="updateConfigForm.value"
                    class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none sm:text-sm transition-colors">
            </div>

            <div v-if="updateConfigForm.type === 'integer'">
                <label for="value" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor</label>
                <input type="number" id="value" v-model="updateConfigForm.value"
                    class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none sm:text-sm transition-colors">
            </div>

            <div v-if="updateConfigForm.type === 'boolean'">
                <label for="value" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor</label>
                <select id="value" v-model="updateConfigForm.value"
                    class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none sm:text-sm transition-colors">
                    <option :value="true">Sí</option>
                    <option :value="false">No</option>
                </select>
            </div>

            <div v-if="updateConfigForm.type === 'json'">
                <label for="value" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor (JSON)</label>
                <textarea id="value" v-model="updateConfigForm.value" rows="5"
                    class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none sm:text-sm transition-colors"></textarea>
            </div>
        </form>
    </Modal>
</template>