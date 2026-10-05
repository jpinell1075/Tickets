<script setup>
    defineProps({
        value: {
            type: [Number, String],
            required: true,
        },
        selector: {
            type: String,
            required: true,
        },
    });
</script>

<template>
    <td :data-selector="selector" :data-table-value="value" :data-calculated="true">
        <slot></slot>
    </td>
</template>