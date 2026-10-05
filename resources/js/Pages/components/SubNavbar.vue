// This script defines a sub-navigation bar component.
// It displays a list of links, highlighting the active link.
// The links are passed as props and rendered dynamically.
<script setup>
    import { Link } from '@inertiajs/vue3';

    defineProps({
        links: Array
    });
</script>

<template>
    <div class="w-full p-0">
        <ul class="flex flex-row justify-end mb-4 border-b border-gray-200 dark:border-gray-700">
            <li v-for="(link, index) in links" :key="index" class="mr-1">
                <Link 
                    :href="route(link.route)"
                    class="inline-block px-4 py-2.5 text-sm font-medium border-b-2 rounded-t-lg transition-colors duration-200"
                    :class="[
                        link.active 
                            ? 'text-blue-600 border-blue-600 bg-gray-50 dark:text-blue-400 dark:border-blue-400 dark:bg-gray-800' 
                            : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:border-gray-600 dark:hover:bg-gray-800/50'
                    ]"
                >
                    {{ link.name }}
                </Link>
            </li>
        </ul>
    </div>
</template>