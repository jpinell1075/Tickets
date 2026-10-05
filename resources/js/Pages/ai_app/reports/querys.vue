<script setup>

    import dashboard from '../../layouts/dashboard.vue';
    import Tabs from '../../components/Tabs.vue';

</script>

<template>
    <dashboard :appName="'ai_assistant_app'">
        <div class="container mx-auto px-4 mt-8">
            <Tabs :tabs="[
                { label: 'Consultas', id: 'queries' },
                { label: 'Reportes', id: 'reports' }
            ]" :storage-key="'ia-panel'">
                <template #queries>
                    <div class="flex flex-col sm:flex-row gap-3 mb-4">
                        <div class="flex-grow">
                            <input type="text" name="query" id="query" class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none px-4 py-2 sm:text-sm transition-colors" placeholder="Escribe tu consulta...">
                        </div>
                        <div class="w-full sm:w-auto flex-shrink-0">
                            <button class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white font-medium rounded-md shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500">Enviar</button>
                        </div>
                    </div>

                    <div class="mt-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 h-[70lvh] overflow-auto transition-colors" style="height: 70lvh;">
                        <!-- {{ response }} -->
                    </div>
                </template>

                <template #reports>
                    <div class="row">
                    </div>
                </template>
            </Tabs>
        </div>
    </dashboard>
</template>