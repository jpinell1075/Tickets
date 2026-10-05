<script setup>
import { ref, onMounted } from 'vue';
import FormattedCurrency from './FormattedCurrency.vue';
 
const props = defineProps({
    target: {
        type: String,
        required: true,
    },
    type: {
        type: String,
        default: 'sum',
    },
    format: {
        type: String,
        default: 'number',
    },
    selector: {
        type: String,
        required: false,
    },
});

let calculated = false;

const computedTotal = ref(0);

onMounted(() => {
    function checkElementsReady(elements) {
        return Array.from(elements).some(el => el.dataset.calculated === 'true');
    }

    function waitForElementsReady() {
        const interval = setInterval(() => {
            const elements = getElements();
            if (checkElementsReady(elements)) {
                clearInterval(interval);
                calculateTotal();
            }
        }, 1000);
    }

    const elements = getElements();
    if (checkElementsReady(elements)) {
        calculateTotal();
    } else {
        waitForElementsReady();
    }
});

function getElements() {
    let target = props.target;

    return document.querySelectorAll(`[data-selector="${target}"]`);
}

function calculateTotal() {
    let type = props.type;

    const values = Array.from(getElements()).map(el => parseFloat(el.dataset.tableValue) || 0);

    if (type === 'sum') {
        computedTotal.value = values.reduce((acc, val) => acc + val, 0);
    } else if (type === 'average') {
        computedTotal.value = values.length ? values.reduce((acc, val) => acc + val, 0) / values.length : 0;
    }
    else if (type === 'max') {
        computedTotal.value = Math.max(...values);
    } else if (type === 'min') {
        computedTotal.value = Math.min(...values);
    }
    else if (type === 'count') {
        computedTotal.value = values.length;
    }
    else {
        console.warn(`Tipo de cálculo "${type}" no soportado.`);
    }

    calculated = true;
}
</script>

<template>
    <td v-if="format == 'number'" :data-selector="selector" :data-table-value="computedTotal" :data-calculated="calculated">
        {{ computedTotal }}
    </td>
    <td v-else-if="format == 'currency'" :data-selector="selector" :data-table-value="computedTotal" :data-calculated="calculated">
        <FormattedCurrency :value="computedTotal" />
    </td>
</template>