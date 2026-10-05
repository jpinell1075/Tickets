<script setup>
    import { ref } from 'vue';

    const props = defineProps({
        text: {
            type: String,
            required: true
        },
        maxLength: {
            type: Number,
            default: 100
        },
        allowExpand: {
            type: Boolean,
            default: true
        }
    });

    const isExpanded = ref(false);

</script>

<template>
    <div v-if="text">
        <p v-if="text.length <= maxLength && !isExpanded" class="m-0">{{ text }}</p>
        <p v-else-if="!isExpanded" class="m-0">
            {{ text.substring(0, maxLength) }} 
            <span v-if="allowExpand" class="text-decoration-underline" role="button" aria-label="Expandir texto" @click="isExpanded = !isExpanded">...</span> 
            <span v-else>...</span>
        </p>
        <p v-if="isExpanded" class="m-0">{{ text }} <span v-if="allowExpand" class="text-decoration-underline" role="button" aria-label="Contraer texto" @click="isExpanded = !isExpanded">...</span></p>
    </div>
</template>