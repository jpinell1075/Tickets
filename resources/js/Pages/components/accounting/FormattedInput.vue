<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
    field: Object,
    showLabel: {
        type: Boolean,
        default: true,
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
    if( props.decimal_separator === ',' ) {
        return 'es-VE';
    } else {
        return 'es-MX';
    }
});

const value = defineModel('value');
const formattedValue = ref('');
const isFocused = ref(false);

const formatValue = (val) => {
    if (val === null || val === undefined || val === '') return '';
    const num = parseFloat(val); 
    if (isNaN(num)) return '';

    return new Intl.NumberFormat(propsLocale.value, {
        style: 'currency',
        currencyDisplay: 'narrowSymbol',
        currency: props.currency_symbol,
        minimumFractionDigits: props.decimal_digits,
        maximumFractionDigits: props.decimal_digits,
    }).format(num);
};

const parseValue = (val) => {
    if (val === null || val === undefined || val === '') return 0;
    return parseFloat(val.toString().replace(/[^0-9.-]+/g, '')) || 0;
};

watch(value, (newValue) => {
    if (isFocused.value && newValue != 0) {
        return;
    }
    formattedValue.value = formatValue(newValue);
}, { immediate: true });

const handleInput = (event) => {
    const rawValue = parseValue(event.target.value);
    value.value = rawValue;
}

const handleFocus = () => {
    isFocused.value = true;
}

const handleBlur = () => {
    isFocused.value = false;
    formattedValue.value = formatValue(value.value);
}
</script>

<template>
    <div class="form-group mt-3">
        <label :for="field.id + 'formatted'" v-if="showLabel">
            {{ field.label }}
            <span v-if="field.required" class="text-danger">*</span>
        </label>

        <input 
            type="text" 
            :id="field.id + 'formatted'" 
            class="form-control" 
            :required="field.required"
            :readonly="field.readonly" 
            :placeholder="field.placeholder" 
            :disabled="field.disabled"
            :value="formattedValue" 
            @change="handleInput"
            @focus="handleFocus"
            @blur="handleBlur"
        />

        <input type="hidden" :name="field.name" :value="value" />
    </div>
</template>