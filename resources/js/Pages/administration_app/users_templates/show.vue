// This script handles the user template editing page.
<script setup>
    import dashboard from '../../layouts/dashboard.vue';
    import SubNavbar from '../../components/SubNavbar.vue';
    import DeleteModal from '../../components/DeleteModal.vue';
    import ExcelExport from '../../components/buttons/ExcelExport.vue';
    import searchSelect from '../../components/inputs/searchSelect.vue';
    import DeleteButton from '../../components/buttons/DeleteButton.vue';
    import Tabs from '../../components/Tabs.vue';
    import Modal from '../../components/Modal.vue';

    import { usePage, useForm, Link } from '@inertiajs/vue3';
    import { computed, ref, watch } from 'vue';

    const deleteModal = ref(null);
    const modalRef = ref(null);
    const filterModalRef = ref(null);

    const user_template = computed(() => {
        return usePage().props.user_template;
    });

    const filters = usePage().props.available_user_filters;

    const links = [
        { route: 'users.index', name: 'Usuarios', active: false },
        { route: 'users.templates.index', name: 'Plantillas de Usuarios', active: true },
    ];

    const updateForm = useForm({
        name: user_template.value.name,
        description: user_template.value.description,
    });

    const submitUpdateForm = () => {
        updateForm.put(route('users.templates.update', user_template.value.id));
    };

    const addModuleForm = useForm({
        user_template_id: user_template.value.id,
        module_id: null,
        actions: [],
    });

    const submitAddModuleForm = () => {
        addModuleForm.post(route('users.templates.addModule'), {
            onSuccess: () => {
                modalRef.value.closeModal();
            },
        });
    };

    function showDeleteModal(url) {
        document.querySelector('#delete_modal form').action = url;
        let modalInstance = new bootstrap.Modal(document.getElementById('delete_modal'));
        modalInstance.show();
    }

    //Filtro según modelo
    const selected_filter = ref(null);
    // Almacena los filtros disponibles para el modelo seleccionado
    const available_filters = ref(null);
    // Almacena el tipo de filtro seleccionado (simple, relations, etc.)
    const selected_filter_type = ref(null);
    
    const searchFilters = () => {
        if (!selected_filter.value) {
            available_filters.value = null;
            return;
        }
        fetch(route('api.modelFilters.getAvailableFilterByModel', {model: selected_filter.value}), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            },
        })
        .then(async response => {
            if (!response.ok) throw new Error('No se encontraron filtros disponibles para el modelo seleccionado.');
            const data = await response.json();
            available_filters.value = data.data;
        })
        .catch(() => {
            showToast('No se encontraron filtros disponibles para el modelo seleccionado.');
        });
    };

    watch(selected_filter, (newFilter) => {
        searchFilters();
    });

    // Almacena los datos del campo seleccionado para el filtro simple
    const selected_filter_field_data = ref(null);

    function setSelectedFilterFieldData() {
        let selected_field = document.getElementById('filter_field').value;
        if (selected_filter_type.value === 'simple'){
            let data = available_filters.value.simple.fields[selected_field];
            if (data) {
                selected_filter_field_data.value = data;
            } else {
                selected_filter_field_data.value = null;
            }
        }
    }

    // Almacena el valor de la relación seleccionada para el filtro de relaciones
    const selected_relation = ref(null);
    // Almacena los datos del campo seleccionado para el filtro de relaciones
    const selected_relation_field_data = ref(null);
    // Almacena el valor de la relación seleccionada
    const selected_relation_value = ref(null);

    function setSelectedRelation() {
        let selected_relation_value = document.getElementById('filter_relation').value;
        if (available_filters.value && available_filters.value.relations) {
            let relation = available_filters.value.relations.relations[selected_relation_value];
            if (relation) {
                selected_relation.value = relation;
            } else {
                selected_relation.value = null;
            }
        }
    }

    function setSelectedRelationFieldData() {
        let selected_field = document.getElementById('filter_field').value;
        if (selected_relation.value && selected_relation.value.fields) {
            let data = selected_relation.value.fields[selected_field];
            if (data) {
                selected_relation_field_data.value = data;
            } else {
                selected_relation_field_data.value = null;
            }
        }
    }

    // Almacena el valor de la función seleccionada para el filtro de funciones
    const selected_function = ref(null);

    function setSelectedFunction() {
        let selected_function_value = document.getElementById('filter_relation').value;
        if (available_filters.value && available_filters.value.functions) {
            let functionData = available_filters.value.functions.functions[selected_function_value];
            if (functionData) {
                selected_function.value = functionData;
            } else {
                selected_function.value = null;
            }
        }
    }

    const addUserModelFilterForm = useForm({
        model: null,
        comparison_type: null,
        field: null,
        operator: null,
        value: null,
        relation: null,
        extra: null,
    });

    const submitAddUserModelFilterForm = () => {
        if(selected_filter_type.value === 'simple') {
            addUserModelFilterForm.comparison_type = selected_filter_type.value;
            addUserModelFilterForm.model = selected_filter.value;
            addUserModelFilterForm.field = document.getElementById('filter_field').value || null;
            addUserModelFilterForm.operator = document.getElementById('filter_operator').value || null;
            addUserModelFilterForm.value = document.getElementById('filter_value').value || null;
        }
        if(selected_filter_type.value === 'relations') {
            addUserModelFilterForm.comparison_type = selected_filter_type.value;
            addUserModelFilterForm.model = selected_filter.value;
            addUserModelFilterForm.field = document.getElementById('filter_field').value || null;
            addUserModelFilterForm.operator = document.getElementById('filter_operator').value || null;
            addUserModelFilterForm.value = selected_relation_value.value || null;
            addUserModelFilterForm.relation = selected_relation.value.relation_name || null;
        }
        if(selected_filter_type.value === 'functions') {
            addUserModelFilterForm.comparison_type = selected_filter_type.value;
            addUserModelFilterForm.model = selected_filter.value;
            addUserModelFilterForm.field = selected_function.value.field.name || null;
            addUserModelFilterForm.operator = selected_function.value.operator || null;
            addUserModelFilterForm.value = document.getElementById('filter_value').value || null;
            addUserModelFilterForm.extra = JSON.stringify({
                method: selected_function.value.method,
            });
        }
        if(selected_filter_type.value === 'user_own') {
            addUserModelFilterForm.comparison_type = selected_filter_type.value;
            addUserModelFilterForm.model = selected_filter.value;
            addUserModelFilterForm.extra = JSON.stringify({
                relation_name: available_filters.value.user_own.relation_name,
                foreign_key: available_filters.value.user_own.foreign_key,
            });
        }

        addUserModelFilterForm.post(route('users.templates.addUserTemplateModelFilter', {userTemplate: user_template.value.id}), {
            onSuccess: () => {
                filterModalRef.value.closeModal();
            },
        });
    };
</script>

<template>
    <dashboard :appName="'administration_app'">
        <SubNavbar :links="links" />

        <div class="container mx-auto px-4">
            <div class="w-full">
                <h4>Editar Plantilla Usuario</h4>
                <hr class="mb-4">
                <form class="space-y-4 p-3 shadow rounded" method="POST" autocomplete="off" @submit.prevent="submitUpdateForm">
                    <div class="grid grid-cols-12 gap-4 pt-3">
                        <div class="md:col-span-6 col-span-12">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre</label>
                            <input type="text" name="name" id="name" class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500 dark:disabled:bg-gray-800/50 dark:disabled:text-gray-500 transition-colors" v-model="updateForm.name" required>
                        </div>
                        <div class="md:col-span-6 col-span-12">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descripción</label>
                            <input type="text" name="description" id="description" class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500 dark:disabled:bg-gray-800/50 dark:disabled:text-gray-500 transition-colors" v-model="updateForm.description">
                        </div>

                        <div class="col-span-12 flex justify-end gap-1">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded transition-colors">Editar</button>
                            <div class="flex justify-content-end gap-1">
                                <ExcelExport :filename="user_template.name" :target="'modules_table'"/>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="container mx-auto px-4 mt-8">
            <Tabs :tabs="[
                { id: 'modules', label: 'Módulos' },
                { id: 'filters', label: 'Filtros de Información' }
            ]" storageKey="filter-management-tabs">
            
            <template #modules>
                <div class="w-full mt-2">
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                        <h5 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2 sm:mb-0">Módulos a los que posee acceso la Plantilla de Usuario</h5>
                        <button type="button" class="inline-flex items-center justify-center px-3 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white rounded-md shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @click="modalRef.openModal()"
                            aria-label="Agregar Módulo">
                            <i class='bx bx-plus'></i>
                        </button>
                    </div>
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm mt-4">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="modules_table">
                            <thead class="bg-blue-100 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider">Nombre</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider">Acciones</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider">Opciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="userModule in user_template.modules" :key="userModule.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ userModule.module.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                        {{ userModule.actions.map(action => action.action_name).join(', ') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex gap-2">
                                            <Link class="min-w-[35px] h-[35px] inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white rounded transition-colors" :href="route(userModule.module.access_route_name)">
                                                <i class='bx bxs-show'></i>
                                            </Link>
                                            <DeleteButton :url="route('users.templates.deleteModule', {userTemplateModule: userModule.id, userTemplate: userModule.user_template_id})" :deleteModal="deleteModal"/>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>

            <template #filters>
                <div class="w-full mt-2">
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                        <h5 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2 sm:mb-0">Filtros de Información de la plantilla de Usuario</h5>
                        <button type="button" class="inline-flex items-center justify-center px-3 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white rounded-md shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500" 
                            @click="filterModalRef.openModal()"
                            aria-label="Agregar Filtro">
                            <i class='bx bx-plus'></i>
                        </button>
                    </div>
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm mt-4">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="filters_table">
                            <thead class="bg-blue-100 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider">Información Filtrada</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider">Filtro</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider">Opciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="userModelFilter in user_template.filters" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ userModelFilter.model }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                        <span v-if="userModelFilter.comparison_type === 'simple'">
                                            {{ userModelFilter.field }} {{ userModelFilter.operator }} {{ userModelFilter.value }}
                                        </span>
                                        <span v-if="userModelFilter.comparison_type === 'relations'">
                                            {{ userModelFilter.relation }} {{ userModelFilter.field }} {{ userModelFilter.operator }} {{ userModelFilter.value }}
                                        </span>
                                        <span v-if="userModelFilter.comparison_type === 'functions'">
                                            {{ userModelFilter.field }} {{ userModelFilter.operator }} {{ userModelFilter.value }}
                                        </span>
                                        <span v-if="userModelFilter.comparison_type === 'user_own'">
                                            Pertenece al usuario
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex gap-2">
                                            <DeleteButton :url="route('users.templates.removeUserTemplateModelFilter', {userTemplateModelFilter: userModelFilter.id, userTemplate: userModelFilter.user_template_id})" :delete-modal="deleteModal"/>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
            </Tabs>
        </div>

        <!-- modal add module -->
        <Modal :title="'Añadir Módulo'" :id="'AddModuleModal'" ref="modalRef" :accept-callback="submitAddModuleForm">
            <form method="POST" @submit.prevent="submitAddModuleForm">
                <div class="grid grid-cols-12 gap-4 p-4">
                    <div class="col-span-12">
                        <searchSelect name="Módulos" input-name="module_id" :model="'Module'" :required="true" select_name="module_id" v-model:select_value="addModuleForm.module_id"/>
                    </div>
                    <div class="md:col-span-2 col-span-4">
                        <div class="w-full px-2 mb-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox"
                                    name="actions[]"
                                    v-model="addModuleForm.actions"
                                    value="create"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:checked:bg-blue-500 checkbox-modules transition-colors">
                                <small class="text-sm font-medium text-gray-800 dark:text-gray-200">Crear</small>
                            </label>
                        </div>
                    </div>
                    <div class="md:col-span-2 col-span-4">
                        <div class="w-full px-2 mb-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox"
                                    name="actions[]"
                                    v-model="addModuleForm.actions"
                                    value="read"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:checked:bg-blue-500 checkbox-modules transition-colors">
                                <small class="text-sm font-medium text-gray-800 dark:text-gray-200">Leer</small>
                            </label>
                        </div>
                    </div>
                    <div class="md:col-span-2 col-span-4">
                        <div class="w-full px-2 mb-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox"
                                    name="actions[]"
                                    v-model="addModuleForm.actions"
                                    value="search"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:checked:bg-blue-500 checkbox-modules transition-colors">
                                <small class="text-sm font-medium text-gray-800 dark:text-gray-200">Busqueda</small>
                            </label>
                        </div>
                    </div>
                    <div class="md:col-span-2 col-span-4">
                        <div class="w-full px-2 mb-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox"
                                    name="actions[]"
                                    v-model="addModuleForm.actions"
                                    value="update"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:checked:bg-blue-500 checkbox-modules transition-colors">
                                <small class="text-sm font-medium text-gray-800 dark:text-gray-200">Actualizar</small>
                            </label>
                        </div>
                    </div>
                    <div class="md:col-span-2 col-span-4">
                        <div class="w-full px-2 mb-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox"
                                    name="actions[]"
                                    v-model="addModuleForm.actions"
                                    value="delete"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:checked:bg-blue-500 checkbox-modules transition-colors">
                                <small class="text-sm font-medium text-gray-800 dark:text-gray-200">Eliminar</small>
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </Modal>

        <!-- modal add filter -->
        <Modal :title="'Añadir Filtro'" :id="'AddFilterModal'" ref="filterModalRef" :accept-callback="submitAddUserModelFilterForm">
            <form method="POST" @submit.prevent="submitAddUserModelFilterForm">
                <div class="w-full">
                    <div class="grid grid-cols-12 gap-4 p-4">
                        <div class="cmd:col-span-6 col-span-12">
                            <label for="model" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Seleccione Información a Filtrar</label>
                            <select name="model" id="model" class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500 dark:disabled:bg-gray-800/50 dark:disabled:text-gray-500 transition-colors" v-model="selected_filter">
                                <option :value="null" selected>Seleccione un filtro</option>
                                <option :value="filter.model" v-for="filter in filters" v-html="filter.label"></option>
                            </select>
                        </div>
                        <div class="md:col-span-6 col-span-12" v-if="available_filters">
                            <label for="filter_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de Filtro</label>
                            <select name="filter_type" id="filter_type" class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500 dark:disabled:bg-gray-800/50 dark:disabled:text-gray-500 transition-colors" v-model="selected_filter_type">
                                <option :value="null" selected>Seleccione un filtro</option>
                                <option :value="filter.type" v-for="filter in available_filters" v-html="filter.label"></option>
                            </select>
                        </div>
                    </div>

                    <hr v-if="selected_filter_type" class="my-3">


                    <div class="grid grid-cols-12 gap-4 p-4" v-if="selected_filter_type == 'simple'">
                        <div class="md:col-span-4 col-span-12 mb-2">
                            <label for="filter_field" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Campo del Filtro</label>
                            <select name="filter_field" id="filter_field" class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500 dark:disabled:bg-gray-800/50 dark:disabled:text-gray-500 transition-colors" @input="setSelectedFilterFieldData">
                                <option :value="null" selected>Seleccione un campo</option>
                                <option :value="fieldKey" v-for="(field, fieldKey) in available_filters.simple.fields" v-html="field.label"></option>
                            </select>
                        </div>
                        <div class="md:col-span-4 col-span-12">
                            <label for="filter_operator" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Operador</label>
                            <select name="filter_operator" id="filter_operator" class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500 dark:disabled:bg-gray-800/50 dark:disabled:text-gray-500 transition-colors">
                                <option :value="null" selected>Seleccione un campo</option>
                                <option :value="operator.value" v-for="operator in available_filters.simple.operators" v-html="operator.label"></option>
                            </select>
                        </div>
                        <div class="md:col-span-4 col-span-12" v-if="selected_filter_field_data">
                            <label for="filter_value" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor</label>
                            <select v-if="selected_filter_field_data && selected_filter_field_data.type == 'static_select'" class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-50  sm:text-sm disabled:bg-gray-100 disabled:text-gray-50₀ dark:disabled:bg-gray-8₀/5₀ dark:disabled:text-gray-5₀ transition-colors" id="filter_value" name="filter_value">
                                <option :value="null" selected>Seleccione un valor</option>
                                <option :value="valueKey" v-for="(label, valueKey) in selected_filter_field_data.values" v-html="label"></option>
                            </select>
                            <input v-else-if="selected_filter_field_data && selected_filter_field_data.type === 'open'"  type="text" class="form-control" id="filter_value" name="filter_value"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-12 gap-4 p-4" v-if="selected_filter_type == 'relations'">
                        <div class="md:col-span-12">
                            <label for="filter_relation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Relación con</label>
                            <select name="filter_relation" id="filter_relation" class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500 dark:disabled:bg-gray-800/50 dark:disabled:text-gray-500 transition-colors" @input="setSelectedRelation">
                                <option :value="null" selected>Seleccione un campo</option>
                                <option :value="relationKey" v-for="(relation, relationKey) in available_filters.relations.relations" v-html="relation.label"></option>
                            </select>
                        </div>
                        <div class="md:col-span-4 col-span-12" v-if="selected_relation">
                            <label for="filter_field" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Campo del Filtro</label>
                            <select name="filter_field" id="filter_field" class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500 dark:disabled:bg-gray-800/50 dark:disabled:text-gray-500 transition-colors" @input="setSelectedRelationFieldData">
                                <option :value="null" selected>Seleccione un campo</option>
                                <option :value="fieldKey" v-for="(field, fieldKey) in selected_relation.fields" v-html="field.label"></option>
                            </select>
                        </div>
                        <div class="md:col-span-4 col-span-12" v-if="selected_relation">
                            <label for="filter_operator" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Operador</label>
                            <select name="filter_operator" id="filter_operator" class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-50  sm:text-sm disabled:bg-gray-1₀ disabled:text-gray-5₀ dark:disabled:bg-gray-8₀/5₀ dark:disabled:text-gray-5₀ transition-colors">
                                <option :value="null" selected>Seleccione un campo</option>
                                <option :value="operator.value" v-for="operator in selected_relation.operators" v-html="operator.label"></option>
                            </select>
                        </div>
                        <div class="col-span-12" v-if="selected_relation_field_data">
                            <searchSelect name="Valor" input-name="filter_value" :model="selected_relation_field_data.model" :required="true" select_name="filter_value" v-model:select_value="selected_relation_value"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-12 gap-4 p-4" v-if="selected_filter_type == 'functions'">
                        <div class="col-span-12">
                            <label for="filter_relation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Función</label>
                            <select name="filter_relation" id="filter_relation" class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500 dark:disabled:bg-gray-800/50 dark:disabled:text-gray-500 transition-colors" @input="setSelectedFunction">
                                <option :value="null" selected>Seleccione un campo</option>
                                <option :value="fncKey" v-for="(fnc, fncKey) in available_filters.functions.functions" v-html="fnc.label"></option>
                            </select>
                        </div>
                        <div class="col-span-12 mt-3" v-if="selected_function">
                            <label for="filter_field" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" v-html="selected_function.field.label"></label>
                            <input class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500 dark:disabled:bg-gray-800/50 dark:disabled:text-gray-500 transition-colors" id="filter_value" name="filter_value"  v-bind="selected_function.field.attrs"/>
                        </div>
                    </div>
                </div>
            </form>
        </Modal>

        <DeleteModal ref="deleteModal"/>
    </dashboard>
</template>