<script setup>
    import { onMounted } from 'vue';

    const applyFontSize = (size) => {
        const validSize = size || 'medium';
        document.documentElement.setAttribute('data-font-size', validSize);
    };

    onMounted(() => {
        if (typeof window.showActiveTheme === 'function') {
            window.showActiveTheme(window.getPreferredTheme());
        }
        
        applyFontSize(window.localStorage.getItem('fontSize'));
        
        window.addEventListener('config-updated', (event) => {
            const theme = window.localStorage.getItem('theme') || 'light';
            window.setTheme(theme);
            if (typeof window.showActiveTheme === 'function') {
                window.showActiveTheme(theme, true);
            }

            applyFontSize(window.localStorage.getItem('fontSize'));
        });
    });
</script>
<template></template>