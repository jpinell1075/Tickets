<script setup>
    defineProps({
        show: {
            type: Boolean,
            default: false,
        },
    });
</script>

<template>
    <Transition
        enter-active-class="transition-opacity duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-300 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-white/80" v-if="show">
            <div class="h-12 w-12 animate-spin rounded-full border-4 border-green-600 border-t-transparent" role="status" aria-label="Loading">
                <span class="sr-only">Cargando...</span>
            </div>
        </div>
    </Transition>
</template>