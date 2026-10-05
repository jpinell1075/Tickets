<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    value: {
        type: [Number, null],
        required: true,
    },
    decimal_separator: {
        type: String,
        default: '.',
    },
    currency_symbol: {
        type: String,
        default: 'NIO',
    },
    decimal_digits: {
        type: Number,
        default: 2,
    },
});

const propsLocale = computed(() => {
    if(props.decimal_separator === ',') {
        return 'es-VE';
    }
    else {
        return 'es-MX';
    }
});

const formattedValue = computed(() => {
    return new Intl.NumberFormat(propsLocale.value, {
        style: 'currency',
        currencyDisplay: 'narrowSymbol',
        currency: props.currency_symbol,
        minimumFractionDigits: props.decimal_digits,
        maximumFractionDigits: props.decimal_digits,
    }).format(props.value);
});
</script>

<template>
    <span>{{ formattedValue }}</span>
</template>