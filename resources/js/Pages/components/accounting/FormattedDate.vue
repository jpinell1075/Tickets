<script setup>
    import { computed } from 'vue';

    const props = defineProps({
        date: String,
        date_format: {
            type: String,
            default: 'd/m/Y',
        },
    });

    const formattedValue = computed(() => {
        if (!props.date) {
            return '';
        }
        const format = props.date_format || 'd/m/Y';

        const [year, month, day] = props.date.split('-');

        if (!year || !month || !day) {
            return props.date;
        }

        const formattedDate = format
            .replace('d', day)
            .replace('m', month)
            .replace('Y', year);

        return formattedDate;
    });
</script>

<template>
    <span>
        {{ formattedValue }}
    </span>
</template>