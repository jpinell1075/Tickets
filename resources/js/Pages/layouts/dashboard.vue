// This script sets up the dashboard layout.
// It includes navigation, theming options, and a slot for injecting page-specific content.
// The template provides a sidebar for navigation and a header for user actions.
<script setup>
    import { computed, defineProps, watch } from 'vue';
    import { usePage, Link  } from '@inertiajs/vue3';
    import { useHead } from '@vueuse/head';
    import Logo from '../components/Logo.vue';
    import PremonishHandler from '../components/accesibility/PremonishHandler.vue';
    import TemeHandler from '../components/accesibility/TemeHandler.vue';
    import UserConfig from '../components/UserConfig.vue';

    const props = defineProps({
        appName: String,
    });

    const page = usePage();
    const modules = computed(() => {
        return [...page.props.modules]
        .filter(module => module.app === props.appName)
        .sort((a, b) => a.order - b.order);
    });

    const userName = computed(() => page.props.userName);
    const message = computed(() => page.props.flash.message);
    const error = computed(() => {
        const errors = page.props?.errors ?? null;
        if (errors && typeof errors === 'object') {
            const firstKey = Object.keys(errors)[0];
            return errors[firstKey];
        }
        return null;
    });

    watch(message, (newMessage) => {
        if (newMessage) {
            showToast(newMessage.message);
        }
    });

    watch(error, (newError) => {
        if (newError) {
            showToast(newError);
        }
    });

    useHead({
        link: [
            { rel: 'stylesheet', href: '/css/menu.css' },
        ],
    });
</script>

<template>
    <PremonishHandler />
    <TemeHandler />
    
    <header class="fixed top-0 right-0 w-full h-[calc(var(--header-height)+1rem)] bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex justify-end items-center px-4 z-40 transition-all duration-300 shadow-sm" id="header">
        <UserConfig :userName="userName" />
    </header>

    <div class="fixed top-0 left-0 w-16 md:w-[250px] h-screen bg-gray-50 dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 z-50 transition-all duration-300" id="nav-bar">
        <nav class="flex flex-col justify-between h-full overflow-hidden">
            <div>
                <div class="pt-4 px-4 flex items-center md:items-start md:px-6">
                    <Link href="/" class="flex justify-center md:justify-start items-center gap-3 text-decoration-none" aria-label="Ir a inicio">
                        <Logo class="w-100" />
                    </Link>
                </div>
                <div class="mt-8 flex flex-col gap-1 px-2">
                    <template v-for="module in modules" :key="module.name">
                        <Link  :href="module.route ? module.route : '#'" class="flex items-center gap-3 px-3 py-2 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors group" aria-label="Ir a {{ module.name }}">
                            <i :class="`bx ${module.icon} text-xl text-green-600 dark:text-green-500 group-hover:scale-110 transition-transform`"></i>
                            <span
                                class="hidden md:block font-medium truncate"
                                :style="module.name.length > 15 ? 'font-size: 0.85em;' : ''"
                            >
                            {{ module.name }}
                            </span>
                        </Link>
                    </template>
                </div>
            </div>

            <div class="bg-gray-200 dark:bg-gray-800 h-14 flex items-center justify-center md:justify-between px-4">
                <span class="hidden md:block font-medium text-gray-700 dark:text-gray-300 truncate"> {{ userName }} </span>
                <Link class="text-grey-500 hover:text-grey-700 dark:text-light-400 dark:hover:text-light-500 text-xl transition-colors" href="/logout" aria-label="Cerrar sesión"> 
                    <i class='bx bxs-x-circle'></i>
                </Link>
            </div>
        </nav>
    </div>

    <main class="w-full h-[calc(100vh-calc(var(--header-height)+1rem))] transition-all duration-300">
        <slot />
    </main>

</template>