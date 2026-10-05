// This script defines a logo component.
// It dynamically updates the logo based on the current theme (light or dark).
// The logo's width and additional classes can be customized via props.
<script setup>

    import { ref, watchEffect } from 'vue';

    const logoLightUrl = '/img/logo_light.png';
    const logoDarkUrl = '/img/logo_dark.png';

    const props = defineProps({
        with: {
            type: String,
            default: '120',
        },
        class: {
            type: String,
            default: '',
        },
    });

    const logoWidth = ref(props.with);
    const logoClass = ref(props.class);

    const getPreferredTheme = () => {
        return window.localStorage.getItem('theme') || 'light';
    };

    const logosUrl = {
        light: logoLightUrl,
        dark: logoDarkUrl,
    };

    const logoUrl = ref(logosUrl[getPreferredTheme()] || logoLightUrl);

    window.addEventListener('config-updated', () => {
        logoUrl.value = logosUrl[getPreferredTheme()] || logoLightUrl;
    });
</script>

<template>
    <img id="logo" alt="Logo" :width="logoWidth" :src="logoUrl" :class="logoClass" class="img-fluid"/>
</template>