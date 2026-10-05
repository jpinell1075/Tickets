<script setup>
    const props = defineProps({
        url: String,
        deleteModal:{
            type: Object
        },
        sm: {
            type: Boolean,
            default: false
        }
    });

    function showDeleteModal() {
        props.deleteModal.openDeleteModal(props.url);
    }
</script>

<template>
    <button aria-label="eliminar"
        type="button"
        class="min-w-[35px] h-[35px] inline-flex items-center justify-center bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600 text-white rounded transition-colors"
        @click="showDeleteModal()" 
        :class="{'btn-sm': sm}">
        <i class='bx bx-trash'></i>
    </button>
</template>