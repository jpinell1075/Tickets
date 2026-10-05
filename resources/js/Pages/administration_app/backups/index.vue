<script setup>
    import { ref } from 'vue';
    import dashboard from '../../layouts/dashboard.vue';
    import DeleteModal from '../../components/DeleteModal.vue';
    import Table from '../../components/Table.vue';
    import ExcelExport from '../../components/buttons/ExcelExport.vue';
    import Modal from '../../components/Modal.vue';

    import { useForm, usePage } from '@inertiajs/vue3';

    const backupform = useForm();

    const sendBackupForm = () => {
        backupform.post(route('backups.store'), {
            onSuccess: () => {
                backupform.reset();
                window.atm_tables?.forEach((table) => {
                    table.refresh();
                });
            },
        });
    };

    const addHourField = () => {
        updateScheduleForm.hours.push('')
    }

    const removeHourField = (index) => {
        updateScheduleForm.hours.splice(index, 1)
    }

    const schedules = usePage().props.schedules;
    const modalRef = ref(null);

    const updateScheduleForm = useForm({
        days: schedules.days,
        hours: schedules.times,
        active: schedules.active,
    });

    function submitForm(){
        document.getElementById('updateScheduleForm').dispatchEvent(new Event('submit', { cancelable: true }));
    }

    const updateSchedule = () => {
        updateScheduleForm.put(route('backups.schedules.update'), {
            onSuccess: () => {
                modalRef.value.closeModal();
            },
        });
    };
</script>

<template>
    <dashboard :appName="'administration_app'">
        <div class="container px-4 pt-4 mx-auto">
            
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mt-2">
                <div class="flex flex-col md:flex-row justify-between gap-4 mt-2">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition-colors disabled:opacity-50" @click="sendBackupForm" :disabled="backupform.processing">
                        Crear Respaldo
                    </button>

                    <div class="flex gap-2">
                        <!-- <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded transition-colors" @click="modalRef.openModal()" aria-label="Configurar Respaldos">
                            <i class="bx bx-cog"></i>
                        </button>
                        <ExcelExport :filename="'backups'" :target="'backups_table'"/> -->
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <h5 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">Respaldos</h5>
                <div class="w-full overflow-x-auto">
                    <Table :model="usePage().props.model" :options="['delete']" :id="'backups'"/>
                </div>
            </div>
        </div>
        <!-- <DeleteModal /> -->

        <!--modal configurar backups-->
        <Modal :title="'Configurar Respaldos'" :id="'configBackupsModal'" ref="modalRef" :accept-callback="submitForm">
            <form class="px-4 pt-5 pb-4 sm:p-6" @submit.prevent="updateSchedule" id="updateScheduleForm">
                <h5 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Activar Respaldo Automático</h5>
                <div class="mb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input class="form-checkbox h-5 w-5 text-blue-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded focus:ring-blue-500" type="checkbox" value="true" name="active" v-model="updateScheduleForm.active">
                        <span class="text-gray-700 dark:text-gray-300">Activo</span>
                    </label>
                </div>

                <h5 class="font-semibold text-gray-800 dark:text-gray-200 mt-4 mb-2">Días</h5>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input class="form-checkbox h-5 w-5 text-blue-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded focus:ring-blue-500" type="checkbox" value="1" name="days[]" v-model="updateScheduleForm.days">
                        <span class="text-gray-700 dark:text-gray-300">Lunes</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input class="form-checkbox h-5 w-5 text-blue-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded focus:ring-blue-500" type="checkbox" value="2" name="days[]" v-model="updateScheduleForm.days">
                        <span class="text-gray-700 dark:text-gray-300">Martes</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input class="form-checkbox h-5 w-5 text-blue-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded focus:ring-blue-500" type="checkbox" value="3" name="days[]" v-model="updateScheduleForm.days">
                        <span class="text-gray-700 dark:text-gray-300">Miércoles</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input class="form-checkbox h-5 w-5 text-blue-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded focus:ring-blue-500" type="checkbox" value="4" name="days[]" v-model="updateScheduleForm.days">
                        <span class="text-gray-700 dark:text-gray-300">Jueves</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input class="form-checkbox h-5 w-5 text-blue-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded focus:ring-blue-500" type="checkbox" value="5" name="days[]" v-model="updateScheduleForm.days">
                        <span class="text-gray-700 dark:text-gray-300">Viernes</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input class="form-checkbox h-5 w-5 text-blue-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded focus:ring-blue-500" type="checkbox" value="6" name="days[]" v-model="updateScheduleForm.days">
                        <span class="text-gray-700 dark:text-gray-300">Sábado</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input class="form-checkbox h-5 w-5 text-blue-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded focus:ring-blue-500" type="checkbox" value="7" name="days[]" v-model="updateScheduleForm.days">
                        <span class="text-gray-700 dark:text-gray-300">Domingo</span>
                    </label>
                </div>
                        
                <hr class="border-t border-gray-200 dark:border-gray-700 my-6">
                        
                <div class="flex justify-between items-center mb-4">
                    <h5 class="font-semibold text-gray-800 dark:text-gray-200">Hora</h5>
                    <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-1 px-3 rounded transition-colors" @click="addHourField">
                        <i class='bx bx-plus'></i>
                    </button>
                </div>
                        
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div v-for="(hora, index) in updateScheduleForm.hours" :key="index">
                        <div class="flex gap-2">
                            <input type="time" class="form-input flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Hora"
                                v-model="updateScheduleForm.hours[index]"
                            />
                            <button v-if="index > 0" type="button"
                                class="bg-red-600 hover:bg-red-700 text-white px-3 rounded transition-colors"
                                @click="removeHourField(index)"
                            >
                                <i class="bx bx-x"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </Modal>
    </dashboard>
</template>