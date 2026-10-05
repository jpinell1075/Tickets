<script setup>
    import { ref, onMounted, watch, computed } from 'vue';

    const props = defineProps({
        tabs: {
            type: Array,
            required: true,
        },
        storageKey: {
            type: String,
            required: true,
        }
    });

    const activeTab = ref(props.tabs.length > 0 ? props.tabs[0].id : null);
    const fullStorageKey = computed(() => `persistent-tabs:${window.location.pathname}:${props.storageKey}`);

    onMounted(() => {
        const savedTab = localStorage.getItem(fullStorageKey.value);
        if (savedTab && props.tabs.some(tab => tab.id === savedTab)) {
            activeTab.value = savedTab;
        }
    });

    watch(activeTab, (newTab) => {
        localStorage.setItem(fullStorageKey.value, newTab);
    });

    const getTabClasses = (tabId) => {
        const baseClasses = "inline-block px-4 py-2.5 text-sm font-medium border-b-2 rounded-t-lg transition-colors duration-200 focus:outline-none";
        const activeClasses = "text-blue-600 border-blue-600 bg-gray-50 dark:text-blue-400 dark:border-blue-400 dark:bg-gray-800";
        const inactiveClasses = "text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200";
        
        return `${baseClasses} ${activeTab.value === tabId ? activeClasses : inactiveClasses}`;
    };
</script>

<template>
    <div class="w-full">
        <ul class="flex flex-row justify-end border-b border-gray-200 dark:border-gray-700 select-none">
            <li v-for="tab in tabs" :key="tab.id" class="mr-1">
                <button 
                    @click="activeTab = tab.id" 
                    :class="getTabClasses(tab.id)"
                >
                    {{ tab.label }}
                </button>
            </li>
        </ul>

        <div class="mt-6 min-h-[300px]">
            <Transition 
                mode="out-in"
                enter-active-class="transition-opacity duration-300 ease-in-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-200 ease-in-out"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div :key="activeTab" class="w-full">
                    <slot :name="activeTab"></slot>
                </div>
            </Transition>
        </div>
    </div>
</template>