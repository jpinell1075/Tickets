// This script handles the user listing page.
// It imports components for displaying a table of users and a form for adding new users.
// The template includes a form for creating users and a table for listing them.
// Additional features include exporting data to Excel and printing user lists.
<script setup>

    import dashboard from '../../layouts/dashboard.vue';
    import SubNavbar from '../../components/SubNavbar.vue';
    import DeleteModal from '../../components/DeleteModal.vue';
    import Table from '../../components/Table.vue';
    import Form from '../../components/Form.vue';
    import ExcelExport from '../../components/buttons/ExcelExport.vue';
    import Print from '../../components/buttons/Print.vue';
    import SearchSelect from '../../components/inputs/searchSelect.vue';
    import Modal from '../../components/Modal.vue';
    import { usePage } from '@inertiajs/vue3';
    import { computed, onMounted, ref, reactive, watch } from 'vue';
    
    const links = [
        { route: 'users.index', name: 'Usuarios', active: true },
        /* { route: 'users.templates.index', name: 'Plantillas de Usuarios', active: false }, */
    ];

    const modalRef = ref(null);
    const userForm = ref(null);

    const form_modules = computed(() => {
        return usePage().props.form_modules;
    });

    const modules = reactive({
        selected_modules: [],
    });

    const form = reactive({
        modules: [],
        user_template_id: null,
        id_area: null,        
    });

    const user_template = ref(false);

    watch(user_template, (newValue) => {
        document.getElementById('role').disabled = newValue;
        document.getElementById('role').required = !newValue;
        if (!newValue) {
            form.user_template_id = null;
        }
    });

    function updateSelectedModules() {
        let data = [];
        const moduleCheckboxes = document.querySelectorAll('.checkbox-modules');
        moduleCheckboxes.forEach((checkbox) => {
            if (checkbox.checked) {
                const moduleId = checkbox.value;
                const actions = [];
                const actionCheckboxes = document.querySelectorAll(`.checkbox-modules-actions[module-id="${moduleId}"]`);
                actionCheckboxes.forEach((actionCheckbox) => {
                    if (actionCheckbox.checked) {
                        actions.push(actionCheckbox.value);
                    }
                });
                data.push({ module_id: moduleId, actions: actions });
            }
        });
        form.modules = data;
    }

    const role = ref(1);
    onMounted(() => {
        const roleElement = document.querySelector('select[name="role"]');
        if (roleElement) {
            role.value = roleElement.value;
            roleElement.addEventListener('change', (event) => {
                role.value = event.target.value;
            });
        }

        const moduleCheckboxes = document.querySelectorAll('.checkbox-modules, .checkbox-modules-actions');
        moduleCheckboxes.forEach((checkbox) => {
            checkbox.addEventListener('input', () => {
                updateSelectedModules();
            });
        });
    });

    function callback(){
        userForm.value.submitFormData();
    }
</script>

<template>
    <dashboard :appName="'administration_app'">
        <SubNavbar :links="links" />
        <div class="container mx-auto px-4 pt-4">
            <Modal :title="'Crear Usuario'" :id="'AddUserModal'" ref="modalRef" :accept-callback="callback">
                <Form :route="route('users.store')" :method="'POST'" :model="usePage().props.model" :form="form" :isModal="modalRef" :show-submit-button="false" ref="userForm">
                    <template #extraContent>
                        <!-- <div class="w-full px-2 mt-4">
                            <label for="user_template" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Usar Plantilla de Usuario</label>
                            <select id="user_template" name="user_template" class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-colors" v-model="user_template">
                                <option :value="false">No</option>
                                <option :value="true">Sí</option>
                            </select>
                        </div>

                        <div class="w-full px-2 mt-4" v-if="user_template">
                            <searchSelect 
                                name="Plantilla de Usuario" 
                                input-name="user_template_id" 
                                :model="'UserTemplate'" 
                                :required="true" 
                                select_name="user_template_id" 
                                v-model:select_value="form.user_template_id"
                            />
                        </div> -->

                        <div class="w-full md:w-1/2 mt-3 px-2">
                            <searchSelect 
                                name="Área" 
                                input-name="id_area" 
                                :model="'Area'" 
                                :required="false" 
                                select_name="id_area" 
                                v-model:select_value="form.id_area"
                            />
                        </div>

                        <div class="w-full px-2 mt-6" id="modules" v-show="role == 1 && !user_template">
                            <h6 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3 border-b border-gray-200 dark:border-gray-700 pb-2">
                                Módulos
                            </h6>
                            
                            <div class="flex flex-wrap -mx-2 select-none">
                                <template v-for="(module, index) in form_modules" :key="index">
                                    
                                    <div class="w-full px-2 mb-2">
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="selected_modules[]" 
                                                :id="'module_' + module.id"
                                                :value="module.id"
                                                v-model="modules.selected_modules"
                                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:checked:bg-blue-500 checkbox-modules transition-colors">
                                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ module.name }}</span>
                                        </label>
                                    </div>

                                    <div class="w-full px-2 mb-4" v-show="modules.selected_modules.includes(module.id)">
                                        <div class="flex flex-wrap gap-4 pl-6">
                                            
                                            <div>
                                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox"
                                                        value="create"
                                                        :module-id="module.id"
                                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:checked:bg-blue-500 checkbox-modules-actions transition-colors">
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">Crear</span>
                                                </label>
                                            </div>
                                            
                                            <div>
                                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox"
                                                        value="read"
                                                        :module-id="module.id"
                                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:checked:bg-blue-500 checkbox-modules-actions transition-colors">
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">Leer</span>
                                                </label>
                                            </div>
                                            
                                            <div>
                                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox"
                                                        value="search"
                                                        :module-id="module.id"
                                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:checked:bg-blue-500 checkbox-modules-actions transition-colors">
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">Búsqueda</span>
                                                </label>
                                            </div>
                                            
                                            <div>
                                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox"
                                                        value="update"
                                                        :module-id="module.id"
                                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:checked:bg-blue-500 checkbox-modules-actions transition-colors">
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">Actualizar</span>
                                                </label>
                                            </div>
                                            
                                            <div>
                                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox"
                                                        value="delete"
                                                        :module-id="module.id"
                                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:checked:bg-blue-500 checkbox-modules-actions transition-colors">
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">Eliminar</span>
                                                </label>
                                            </div>

                                        </div>
                                        
                                        <hr class="mt-3 mb-1 border-t border-gray-200 dark:border-gray-700">
                                    </div>
                                    
                                </template>
                            </div>
                        </div>
                    </template>
                </Form>
            </Modal>

            <div class="w-full mt-4">
                <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4 sm:gap-0">
                    <h5 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Usuarios</h5>
                    
                    <div class="flex items-center gap-2">
                        <!-- <ExcelExport :filename="'Usuarios'" :target="'users_table'"/>
                        
                        <Print :view-name="'users.print.index'" :title="'Usuarios'" :page-properties="{'pagedjs': true, 'pagecounter': true, 'openInNewTab': true, 'loadBootstrap': true}" :params="{}"/> -->
                        
                        <button 
                            type="button" 
                            class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded transition-colors" 
                            @click="$refs.modalRef.openModal()"
                            aria-label="Agregar Usuario"
                        >
                            <i class='bx bx-plus'></i>
                        </button>
                    </div>
                </div>
                
                <Table :model="usePage().props.model" :options="['delete']" :id="'users'" />
            </div>
        </div>
    </dashboard>
</template>