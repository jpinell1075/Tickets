// This script sets up the welcome panel page.
// It displays the application name and a welcome message using data from the Inertia page props.
// The template includes a centered layout with a welcome message and application logo.
<script setup>

    import mainDashboard from './layouts/mainDashboard.vue';
    import { usePage, Link } from '@inertiajs/vue3';
    import { computed } from 'vue';
    import Apps from './components/Apps.vue';

    const page = usePage();
    const userApps = computed(() => page.props.userApps);

</script>

<template>
    <mainDashboard>
        <Apps :userApps="userApps" />
    </mainDashboard>
</template>