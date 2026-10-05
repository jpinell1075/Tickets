// This script defines a dynamic table component.
// It supports search, pagination, and delete functionality.
// The table data is fetched from an API, and the component allows adding or removing search options dynamically.
// It also integrates with global table instances for refreshing and managing data.
<script setup>
    import { ref, onMounted, watch, onUnmounted } from 'vue';
    import { Link } from '@inertiajs/vue3';
    import FormattedDateInput from './accounting/FormattedDateInput.vue';
    import FormattedDate from './accounting/FormattedDate.vue'; 
    import DeleteModal from './DeleteModal.vue';

    const deleteModal = ref(null);

    const props = defineProps({
        model: Object,
        options: Array,
        id: [String, Number],
        extraQueryParameter: [String, Number, Boolean],
        small: {
            type: Boolean,
            default: false,
        },
        defaultOptions: {
            type: Object,
            default: null,
        },
        defaultOrderByField: {
            type: String,
            default: 'created_at',
        },
        withRelations: { 
            type: Array,
            default: []
        },
    });

    const tableData = ref([]);
    const pagination = ref(20);
    const orderByField = ref(props.defaultOrderByField);
    const orderByDirection = ref('desc');
    const showSoftDeleted = ref(false);
    const isSearchOptionsOpen = ref(false);

    const searchOptions = ref([
        { field: props.model.table_fields_searchable?.[0] || '', search_type: 'like', search: '' }
    ]);

    const prevUrl = ref(null);
    const nextUrl = ref(null);

    function getUrl(page = null) {
        const base_url = `${api_url}search/${props.model.model}/`;
        const url = new URL(base_url);

        searchOptions.value.forEach((option, index) => {
            url.searchParams.append(`params[${index}][field]`, option.field);
            url.searchParams.append(`params[${index}][search_type]`, option.search_type);
            url.searchParams.append(`params[${index}][search]`, option.search);
        });
        url.searchParams.append('pagination', pagination.value);
        url.searchParams.append('extraQueryParameter', props.extraQueryParameter);
        url.searchParams.append('orderByField', orderByField.value);
        url.searchParams.append('orderByDirection', orderByDirection.value);
        url.searchParams.append('showSoftDeleted', showSoftDeleted.value);

        if (page) {
            url.searchParams.append('page', page);
        }

        if (props.withRelations.length > 0) {
            for (const relation of props.withRelations) {
                url.searchParams.append('withRelations[]', relation);
            }
        }

        return url;
    }

    function searchData(url) {
        if (!url) return;
        tableData.value = [];

        makeRequest(url, 'GET', (response) => {
            const links = response.data.links;
            const prev_page = links[0] && links[0].url ? new URL(links[0].url).searchParams.get('page') : null;
            const next_page = links[links.length - 1] && links[links.length - 1].url ? new URL(links[links.length - 1].url).searchParams.get('page') : null;
            prevUrl.value = prev_page ? getUrl(prev_page) : null;
            nextUrl.value = next_page ? getUrl(next_page) : null;
            tableData.value = response.data.data;
        }, () => {
            tableData.value = [props.model.table_fields.map(() => '')];
        });
    }

    let tableInstance = null;

    onMounted(() => {
        searchData(getUrl());
        tableInstance = {
            refresh: () => searchData(getUrl()),
            getData: () => tableData.value,
            setData: (data) => { tableData.value = data },
            getPagination: () => pagination.value,
            setPagination: (value) => { pagination.value = value },
        };
        if (!window.atm_tables) {
            window.atm_tables = [];
        }
        window.atm_tables.push(tableInstance);
    });

    onUnmounted(() => {
        if (window.atm_tables && tableInstance) {
            const idx = window.atm_tables.indexOf(tableInstance);
            if (idx !== -1) window.atm_tables.splice(idx, 1);
        }
    });

    function debounce(fn, delay) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => fn(...args), delay);
        };
    }

    const debouncedSearch = debounce(() => {
        searchData(getUrl());
    }, 500);

    watch([pagination, searchOptions, orderByField, orderByDirection, showSoftDeleted], () => {
        debouncedSearch();
    }, { deep: true });

    function addNewSearchOption() {
        searchOptions.value.push({ field: '', search_type: 'like', search: '' });
    }

    function removeSearchOption(index) {
        if (searchOptions.value.length > 1) {
            searchOptions.value.splice(index, 1);
        }
        searchData(getUrl());
    }

    function goToPage(url) {
        if (url) {
            searchData(url);
        }
    }

    function showDeleteModal(url) {
        deleteModal.value.openDeleteModal(url);
    }

    function resolveField(row, field){
        const parts = field.split('.');
        if(parts.length > 1){
            const f = parts[parts.length - 1];
            const relation = parts.slice(0, parts.length - 1).join('.');
            const relationData = row[relation];
            if(relationData){
                return relationData[f] || '';
            }
        }
        return row[field] || '';
    }
</script>

<template>
    <div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-md rounded-lg p-4 transition-colors" :id="id">
        <div class="flex flex-col lg:flex-row justify-end gap-4 mb-4">
            <div class="w-full lg:w-1/2">
                <div class="flex rounded-md shadow-sm w-full">
                    <label class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm whitespace-nowrap transition-colors" :for="'orderByField_' + id">
                        Ordenar Por
                    </label>
                    <select class="flex-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors" v-model="orderByField" :id="'orderByField_' + id">
                        <option value="created_at">Fecha de Creación</option>
                        <template v-for="field in props.model.table_fields_searchable" :key="field">
                            <option :value="field" v-if="props.model.table_fields_names[field]">{{ props.model.table_fields_names[field] }}</option>
                        </template>
                    </select>
                    <select class="flex-1 border border-l-0 border-gray-300 dark:border-gray-600 rounded-r-md bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors" v-model="orderByDirection" :id="'orderByDirection_' + id" aria-label="Dirección de ordenamiento">
                        <option value="asc">Ascendente</option>
                        <option value="desc">Descendente</option>
                    </select>
                </div>
            </div>

            <div class="w-full lg:w-1/3">
                <div class="flex rounded-md shadow-sm w-full">
                    <label class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm whitespace-nowrap transition-colors" :for="'pagination_' + id">
                        Paginación
                    </label>
                    <input type="number" class="flex-1 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors" placeholder="Paginación" v-model.number="pagination" :id="'pagination_' + id">
                </div>
            </div>

            <div class="w-full lg:w-auto flex justify-end gap-2">
                <button class="inline-flex items-center justify-center px-3 py-2 bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 transition" @click="searchData(getUrl())" aria-label="refrescar">
                    <i class='bx bx-refresh text-lg'></i>
                </button>
                <button class="inline-flex items-center justify-center px-3 py-2 bg-gray-600 hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-500 transition" @click="isSearchOptionsOpen = !isSearchOptionsOpen" aria-label="opciones de búsqueda">
                    <i class='bx bx-cog text-lg'></i>
                </button>
            </div>
        </div>

        <div v-show="isSearchOptionsOpen" :id="'search_options_' + id" class="transition-all duration-300 ease-in-out">
            
            <div class="flex flex-wrap items-end justify-end mb-4">
                <!-- <div class="flex items-center">
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" id="showSoftDeleted" v-model="showSoftDeleted" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 dark:bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white dark:after:bg-gray-200 after:border-gray-300 dark:after:border-gray-500 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300 transition-colors">Mostrar Eliminados Temporales</span>
                    </label>
                </div> -->
                <div>
                    <button class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition" :class="small ? 'px-2 py-1 text-sm' : 'px-4 py-2'" aria-label="agregar opción de búsqueda" @click="addNewSearchOption">
                        <i class='bx bx-plus'></i>
                    </button>
                </div>
            </div>

            <div class="flex flex-col gap-3 mb-4">
                <div v-for="(option, index) in searchOptions" :key="index" class="flex flex-col lg:flex-row gap-3 w-full">
                    
                    <div class="flex flex-col sm:flex-row gap-3 lg:w-1/2">
                        <div class="flex flex-1 rounded-md shadow-sm">
                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 transition-colors" :class="small ? 'text-xs' : 'text-sm'"><i class='bx bx-search'></i></span>
                            <select class="flex-1 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors" :class="small ? 'text-xs py-1' : 'text-sm'" v-model="option.field">
                                <template v-for="(field, i) in props.model.table_fields_searchable" :key="i">
                                    <option :value="field">{{ props.model.table_fields_names[field] }}</option>
                                </template>
                            </select>
                        </div>
                        <div class="flex flex-1 rounded-md shadow-sm">
                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 transition-colors" :class="small ? 'text-xs' : 'text-sm'"><i class='bx bx-search'></i></span>
                            <select class="flex-1 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors" :class="small ? 'text-xs py-1' : 'text-sm'" v-model="option.search_type">
                                <option value="like">Similar</option>
                                <option value="=">Igual</option>
                                <option value="!=">Diferente</option>
                                <option value=">">Mayor</option>
                                <option value=">=">Mayor o Igual</option>
                                <option value="<">Menor</option>
                                <option value="<=">Menor o Igual</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 lg:w-1/2 items-center">
                        <div class="flex flex-1 rounded-md shadow-sm w-full">
                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 transition-colors" :class="small ? 'text-xs' : 'text-sm'"><i class='bx bx-search'></i></span>
                            
                            <FormattedDateInput 
                                v-if="props.model.table_fields_types && props.model.table_fields_types[option.field] === 'formatted_date'"
                                v-model:value="option.search" 
                                placeholder="Buscar" :attrs="{ class: `flex-1 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors ${small ? 'text-xs py-1' : 'text-sm'}` }"/>
                                
                            <input v-else type="search" class="flex-1 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors" :class="small ? 'text-xs py-1' : 'text-sm'" placeholder="Buscar" v-model="option.search" autocomplete="off">
                        </div>
                        <div class="flex-shrink-0 w-full sm:w-auto flex justify-end">
                            <button class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 transition" :class="small ? 'px-2 py-1 text-sm' : 'px-4 py-2'" aria-label="eliminar opción de búsqueda" @click="removeSearchOption(index)">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 transition-colors">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" :id="id + '_table'" :class="{ 'text-sm': small }">
                <thead class="bg-blue-100 dark:bg-gray-900">
                    <tr>
                        <template v-for="(field, index) in props.model.table_fields" :key="index">
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap transition-colors">
                                {{ props.model.table_fields_names[field] }}
                            </th>
                        </template>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap transition-colors">Opciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 transition-colors">
                    <tr v-for="(row, rowIndex) in tableData" :key="rowIndex" :class="[row.deleted_at ? 'bg-cyan-50 dark:bg-cyan-900/30' : 'hover:bg-gray-50 dark:hover:bg-gray-700 even:bg-gray-50/50 dark:even:bg-gray-800/50']">
                        
                        <td v-for="(field, fieldIndex) in props.model.table_fields" :key="fieldIndex" class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300 transition-colors">
                            <FormattedDate v-if="props.model.table_fields_types && props.model.table_fields_types[field] === 'formatted_date'" :date="row[field]" />
                            <template v-else>
                                {{ resolveField(row, field) }}
                            </template>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div v-if="!row.deleted_at">
                                <div class="flex justify-center gap-2" v-if="!defaultOptions">
                                    <template v-for="(option, key) in row.options" :key="key">
                                        <Link v-if="option.type === 'link'" v-bind="option.attr" v-html="option.inner" :class="small ? 'text-xs px-2 py-1' : 'text-sm px-3 py-1.5'" class="inline-flex items-center rounded bg-blue-100 dark:bg-blue-700 text-blue-700 dark:text-blue-200 hover:bg-blue-200 dark:hover:bg-blue-600 transition-colors" aria-label="ver registro"></Link>
                                        <button v-else-if="option.type === 'button'" v-bind="option.attr" @click="key === 'delete' && showDeleteModal(option.attr['data-url'])" v-html="option.inner" :class="small ? 'text-xs px-2 py-1' : 'text-sm px-3 py-1.5'" class="inline-flex items-center rounded bg-red-100 dark:bg-red-700 text-red-700 dark:text-red-200 hover:bg-red-200 dark:hover:bg-red-600 transition-colors" aria-label="eliminar registro"></button>
                                        <a v-else-if="option.type === 'normal-link'" v-bind="option.attr" v-html="option.inner" :class="small ? 'text-xs px-2 py-1' : 'text-sm px-3 py-1.5'" class="inline-flex items-center rounded bg-blue-100 dark:bg-blue-700 text-blue-700 dark:text-blue-200 hover:bg-blue-200 dark:hover:bg-blue-600 transition-colors" aria-label="ver registro"></a>
                                    </template>
                                </div>
                                <div class="flex justify-center gap-2" v-if="defaultOptions">
                                    <template v-for="(option, key) in defaultOptions" :key="key">
                                        <Link v-if="option.type === 'link'" v-bind="option.attr" v-html="option.inner" :href="route(
                                            option.route_name,
                                            option.route_params.reduce((acc, field) => {
                                                acc[field] = row[field];
                                                return acc;
                                            }, {})
                                        )" :class="small ? 'text-xs px-2 py-1' : 'text-sm px-3 py-1.5'" class="inline-flex items-center rounded bg-blue-100 dark:bg-blue-700 text-blue-700 dark:text-blue-200 hover:bg-blue-200 dark:hover:bg-blue-600 transition-colors" aria-label="ver registro"></Link>

                                        <button v-else-if="option.type === 'button'" v-bind="option.attr" @click="key === 'delete' && showDeleteModal(option.attr['data-url'])" v-html="option.inner" :class="small ? 'text-xs px-2 py-1' : 'text-sm px-3 py-1.5'" class="inline-flex items-center rounded bg-red-100 dark:bg-red-700 text-red-700 dark:text-red-200 hover:bg-red-200 dark:hover:bg-red-600 transition-colors" aria-label="eliminar registro"></button>

                                        <a v-else-if="option.type === 'normal-link'" v-bind="option.attr" :href="route(
                                                option.attr.route_name,
                                                option.attr.route_params.reduce((acc, field) => {
                                                acc[field] = row[field];
                                                return acc;
                                            }, {})
                                        )"
                                            v-html="option.inner"
                                            :class="small ? 'text-xs px-2 py-1' : 'text-sm px-3 py-1.5'" class="inline-flex items-center rounded bg-blue-100 dark:bg-blue-700 text-blue-700 dark:text-blue-200 hover:bg-blue-200 dark:hover:bg-blue-600 transition-colors" aria-label="ver registro"></a>
                                    </template>
                                </div>
                            </div>
                        </td>

                    </tr>
                    <tr v-if="tableData.length === 0">
                        <td :colspan="props.model.table_fields.length + 1" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400 transition-colors">Cargando...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex justify-center sm:justify-start">
            <nav aria-label="Navegación de páginas">
                <ul class="inline-flex -space-x-px rounded-md shadow-sm select-none" :class="{ 'text-sm': small }">
                    <li>
                        <button class="inline-flex items-center px-4 py-2 text-sm font-medium border rounded-l-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" :class="{ 'opacity-50 cursor-not-allowed': !prevUrl }" :disabled="!prevUrl" @click.prevent="goToPage(prevUrl)">Anterior</button>
                    </li>
                    <li>
                        <button class="inline-flex items-center px-4 py-2 text-sm font-medium border border-l-0 rounded-r-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" :class="{ 'opacity-50 cursor-not-allowed': !nextUrl }" :disabled="!nextUrl" @click.prevent="goToPage(nextUrl)">Siguiente</button>
                    </li>
                </ul>
            </nav>
        </div>
        
    </div>

    <DeleteModal ref="deleteModal"/>
</template>