// This script sets up the welcome panel page.
// It displays the application name and a welcome message using data from the Inertia page props.
// The template includes a centered layout with a welcome message and application logo.
<script setup>

    import dashboard from '../layouts/dashboard.vue';
    import { computed } from 'vue';
    import { usePage } from '@inertiajs/vue3';
    import Apps from '../components/Apps.vue';

    const page = usePage();
    const modules = computed(() => {
        return [...page.props.modules]
            .filter(module => module.app === 'administration_app')
            .sort((a, b) => a.order - b.order);
    });
</script>

<template>
    <dashboard :appName="'administration_app'">
        <apps :userApps="modules" />
    </dashboard>
</template>