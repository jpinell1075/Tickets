<script setup>
    import { onMounted, onUnmounted, ref, watch } from 'vue';
    import { Link } from '@inertiajs/vue3';

    defineProps({
        userName: String,
    });

    let config = ref({
        theme: window.localStorage.getItem('theme') || 'light',
        highlight: window.localStorage.getItem('highlight') === 'true',
        clickAsist: window.localStorage.getItem('clickAsist') === 'true',
        fontSize: window.localStorage.getItem('fontSize') || 'medium',
    });

    const isOpen = ref(false);

    function globalUpdateConfig(key, value){
        config.value[key] = value;
        updateConfig();
        emitConfigUpdated({ ...config.value });
    }

    function updateConfig() {
        Object.entries(config.value).forEach(([key, value]) => {
            window.localStorage.setItem(key, value);
        });
    }

    function emitConfigUpdated(newConfig) {
        window.dispatchEvent(new CustomEvent('config-updated'));
    }

    const closeDropdown = (e) => {
        if (!e.target.closest('.dropdown-container')) {
            isOpen.value = false;
        }
    };

    watch(config, (newVal) => {
        updateConfig();
        emitConfigUpdated({ ...newVal });
    }, { deep: true });

    onMounted(() => {
        window.globalUpdateConfig = globalUpdateConfig;
        document.addEventListener('click', closeDropdown);
    });

    onUnmounted(() => {
        delete window.globalUpdateConfig;
        document.removeEventListener('click', closeDropdown);
    });
</script>

<template>
    <div class="relative dropdown-container" style="z-index: 50;">
        <button @click="isOpen = !isOpen" type="button" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-900 dark:text-gray-100 font-medium py-2 px-4 rounded shadow transition-colors">
            {{ userName }}
        </button>
        
        <div v-show="isOpen" class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden py-2" @click.stop>
            <!-- <Link :href="route('profile.index')" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-900 dark:text-gray-100 text-decoration-none">
                <strong>Perfil</strong>
            </Link>
            
            <hr class="border-t border-gray-200 dark:border-gray-700 my-2"> -->
            
            <div class="px-4 py-1 text-sm text-gray-500 dark:text-gray-400"><strong>Tema</strong></div>
            
            <label class="flex justify-between items-center px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">
                <span class="text-gray-900 dark:text-gray-100">Claro</span>
                <input class="form-radio text-green-600 focus:ring-green-500 bg-gray-100 border-gray-300 dark:bg-gray-900 dark:border-gray-700 h-4 w-4" type="radio" name="theme" value="light" v-model="config.theme">
            </label>
            <label class="flex justify-between items-center px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">
                <span class="text-gray-900 dark:text-gray-100">Oscuro</span>
                <input class="form-radio text-green-600 focus:ring-green-500 bg-gray-100 border-gray-300 dark:bg-gray-900 dark:border-gray-700 h-4 w-4" type="radio" name="theme" value="dark" v-model="config.theme">
            </label>
            
            <hr class="border-t border-gray-200 dark:border-gray-700 my-2">
            
            <div class="px-4 py-1 text-sm text-gray-500 dark:text-gray-400"><strong>Tamaño de Fuente</strong></div>
            <div class="px-4 py-2">
                <select class="form-select block w-full mt-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50" v-model="config.fontSize">
                    <option value="small">Pequeño</option>
                    <option value="medium">Mediano</option>
                    <option value="large">Grande</option>
                </select>
            </div>
            
            <hr class="border-t border-gray-200 dark:border-gray-700 my-2">
            
            <label class="flex justify-between items-center px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">
                <span class="text-gray-900 dark:text-gray-100"><strong>Remarcado de Elementos</strong></span>
                <input class="form-checkbox text-green-600 focus:ring-green-500 bg-gray-100 border-gray-300 dark:bg-gray-900 dark:border-gray-700 h-4 w-4" type="checkbox" v-model="config.highlight">
            </label>
            
            <hr class="border-t border-gray-200 dark:border-gray-700 my-2">
            
            <label class="flex justify-between items-center px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">
                <span class="text-gray-900 dark:text-gray-100"><strong>Asistencia de Clics</strong></span>
                <input class="form-checkbox text-green-600 focus:ring-green-500 bg-gray-100 border-gray-300 dark:bg-gray-900 dark:border-gray-700 h-4 w-4" type="checkbox" v-model="config.clickAsist">
            </label>
        </div>
    </div>
</template>