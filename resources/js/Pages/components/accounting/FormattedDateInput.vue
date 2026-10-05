<script setup>
    import { ref, computed } from 'vue';

    const props = defineProps({
        attrs: Object,
        date_format: {
            type: String,
            default: 'd/m/Y',
        },
    });
    const value = defineModel('value');
    const dateInput = ref(null);

    function openDatePicker() {
        try {
            dateInput.value?.showPicker();
        } catch (error) {
            dateInput.value?.click();
        }
    }

    const formattedValue = computed(() => {
        if (!value.value) {
            return '';
        }
        const format = props.date_format || 'd/m/Y';

        const [year, month, day] = value.value.split('-');

        if (!year || !month || !day) {
            return value.value;
        }

        const formattedDate = format
            .replace('d', day)
            .replace('m', month)
            .replace('Y', year);

        return formattedDate;
    });
</script>

<template>
    <div class="position-relative">
        <input
            type="text"
            :value="formattedValue"
            v-bind="attrs"
            @click="openDatePicker"
            readonly
        />
        <input
            type="date"
            ref="dateInput"
            v-model="value"
            @click.prevent="null"
            class="position-absolute top-0 start-0 w-100 h-100 opacity-0 pe-none"
        />
    </div>
</template>