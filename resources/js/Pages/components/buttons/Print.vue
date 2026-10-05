// This script defines a print button component.
// It generates a URL for printing a specific view with parameters.
<script setup>
    import { computed } from 'vue';

    const props = defineProps({
        viewName: String,
        title: String,
        params: Object,
        pageProperties: Object,
    });

    const printUrl = computed(() => {
        let url = new URL(route('exports.print'));
        url.searchParams.append('view_name', props.viewName);
        url.searchParams.append('title', props.title);
        url.searchParams.append('params', JSON.stringify(props.params));
        url.searchParams.append('page_properties', JSON.stringify(props.pageProperties));
        return url.toString();
    });

    function openPrintWindow() {
        if (props.pageProperties.openInNewTab || false) {
            window.open(printUrl.value, '_blank', 'width=1100,height=800');
        } else {
            window.location.href = printUrl.value;
        }
    }
</script>

<template>
    <a
        aria-label="imprimir"
        class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded transition-colors excel_api_export"
        href="#"
        @click.prevent="openPrintWindow()"
    >
        <i class="bx bxs-printer"></i>
    </a>
</template>