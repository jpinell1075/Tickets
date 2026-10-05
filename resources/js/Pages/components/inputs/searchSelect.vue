// This script defines a search and select input component.
// It allows users to search for options dynamically and select one from the results.
// The component fetches data from an API based on the search query and updates the options list.

<script setup>
    import { ref, watch, onMounted, onUnmounted } from 'vue';

    const props = defineProps({
        name: { type: String, required: true },
        inputName: { type: String, required: true },
        model: { type: String, required: true },
        required: { type: Boolean, default: false },
        allowNew: { type: Boolean, default: false },
        defaultValue: {
            type: Object,
            default: null
        },
        extraQueryParameter: {
            type: [String, Boolean, Number],
            default: null
        },
        showLabel: { type: Boolean, default: true },
        withRelations: { type: Array, default: [] },
    });

    const searchQuery = ref('');
    const options = ref([]);
    const lastOptions = ref([]);
    const isLoading = ref(false);
    const highlightedIndex = ref(-1);
    const skipWatch = ref(false);
    const optionSelected = ref(false);

    const select_value = defineModel('select_value', { default: null });
    const select_data = defineModel('select_data', { default: null });

    function debounce(fn, delay) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => fn(...args), delay);
        };
    }

    const debouncedSearch = debounce((query) => {
        searchSelect(query);
    }, 400);

    async function searchSelect(query) {
        if (!query) {
            options.value = [];
            lastOptions.value = [];
            return;
        }

        isLoading.value = true;
        let url = `${api_url}select/${props.model}/${query}`;
        if (props.extraQueryParameter) {
            url += `/${props.extraQueryParameter}`;
        }

        if (props.withRelations.length > 0) {
            for (const relation of props.withRelations) {
                url += (url.includes('?') ? '&' : '?') + `withRelations[]=${relation}`;
            }
        }

        try {
            const response = await fetch(url);
            const data = await response.json();
            options.value = data.data;
            lastOptions.value = data.data;
        } catch (error) {
            options.value = [];
            lastOptions.value = [];
        } finally {
            isLoading.value = false;
        }
    }

    watch(searchQuery, (newQuery) => {
        if (skipWatch.value) {
            optionSelected.value = false;
            skipWatch.value = false;
            return;
        }
        debouncedSearch(newQuery);
    });

    function selectOption(option) {
        select_data.value = option;
        skipWatch.value = true;
        searchQuery.value = option.name;
        select_value.value = option.id;
        optionSelected.value = true;
        options.value = [];
    }
    defineExpose({ selectOption });

    function handleEnter() {
        if (highlightedIndex.value >= 0 && highlightedIndex.value < options.value.length) {
            selectOption(options.value[highlightedIndex.value]);
        }
    }

    function clearOptions() {
        setTimeout(() => {
            if (lastOptions.value) {
                lastOptions.value.forEach((option, index) => {
                    if (option.name == searchQuery.value) {
                        selectOption(option);
                    }
                });
            }

            options.value = [];
            isLoading.value = false;

            if (!optionSelected.value && !props.allowNew) {
                searchQuery.value = '';
                select_value.value = null;
            }
            else if (!optionSelected.value && props.allowNew) {
                select_value.value = searchQuery.value;
            }
        }, 500);

    }

    function setOptionOnChange() {
        let selectFound = false;
        if (lastOptions.value) {
            lastOptions.value.forEach((option, index) => {
                if (option.name == searchQuery.value) {
                    selectOption(option);
                    selectFound = true;
                }
            });
        }
        if (!selectFound && props.allowNew) {
            select_value.value = searchQuery.value;
        }
    }

    onMounted(() => {
        window.addEventListener('resize', updateDropdownPosition);
        if (props.defaultValue) {
            options.value = [props.defaultValue];
            lastOptions.value = [props.defaultValue];
            selectOption(props.defaultValue);
        }
    });

    onUnmounted(() => {
        window.removeEventListener('resize', updateDropdownPosition);
    });

    const inputRef = ref(null);
    const listRef = ref(null);
    const dropdownStyle = ref({});

    function updateDropdownPosition() {
        if (inputRef.value) {
            const rect = inputRef.value.getBoundingClientRect();
            dropdownStyle.value = {
                position: 'fixed',
                top: `${rect.bottom}px`,
                left: `${rect.left}px`,
                width: `${rect.width}px`,
                overflowY: 'auto',
                maxHeight: '200px'
            };
        }
    }

    watch(options, (newOptions) => {
        if (newOptions.length > 0) {
            updateDropdownPosition();
        }
    });
</script>

<template>
    <div class="mb-4 relative">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" v-if="showLabel">
            {{ name }} <span class="text-red-500 dark:text-red-400" v-if="required">*</span>
        </label>
        
        <input type="text" 
            class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none sm:text-sm transition-colors" 
            v-model="searchQuery" autocomplete="off"
            @keydown.enter.prevent="handleEnter"
            @keydown.arrow-down.prevent="highlightedIndex = (highlightedIndex + 1) % options.length"
            @keydown.arrow-up.prevent="highlightedIndex = (highlightedIndex - 1 + options.length) % options.length"
            :placeholder="`Escriba para buscar ${name}...`" :required="required" :name="inputName"
            @focus="searchSelect(searchQuery)"
            @blur="clearOptions"
            @input="setOptionOnChange"
            ref="inputRef"
        />

        <ul 
            class="z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg transition-colors" 
            :style="dropdownStyle"
            :class="options.length > 0 ? 'block' : 'hidden'"
            ref="listRef"
        >
            <li
                v-for="(option, index) in options"
                :key="option.id"
                @mousedown.prevent="selectOption(option)"
                class="cursor-pointer"
            >
                <span 
                    class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 transition-colors"
                    :class="highlightedIndex === index ? 'bg-blue-100 dark:bg-blue-900/50' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
                >
                    {{ option.search_name || option.name }}
                </span>
            </li>   
        </ul>
    </div>
</template>