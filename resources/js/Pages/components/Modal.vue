<script setup>
    import { ref } from 'vue';

    const props = defineProps({
        id: String,
        title: String,
        acceptCallback: {
            type: Function,
            default: null,
        },
    });

    const modalState = ref(false);

    function openModal() {
        modalState.value = true;
    }

    function closeModal() {
        modalState.value = false;
    }

    defineExpose({
        openModal,
        closeModal,
    });

</script>

<template>
    <div v-show="modalState" class="fixed inset-0 z-50 overflow-y-auto" :aria-labelledby="id + 'Label'" role="dialog" aria-modal="true">
        <div class="min-h-screen text-center p-3">
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-full max-w-[1200px]">
                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center bg-transparent">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white"
                            :id="id + 'Label'">{{ title }}</h3>
                        <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none"
                            @click="closeModal" aria-label="Cerrar">
                            <i class="bx bx-x text-2xl"></i>
                        </button>
                    </div>
                </div>

                <slot></slot>

                <div class="px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200 dark:border-gray-700">
                    <button type="button" @click="acceptCallback"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Aceptar
                    </button>
                    <button type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        @click="closeModal">
                        Cerrar
                    </button>
                </div>
            </div>

            <div class="fixed inset-0 bg-gray-500/85 transition-opacity z-[-1]" @click="closeModal"aria-hidden="true"></div>
        </div>
    </div>
</template>