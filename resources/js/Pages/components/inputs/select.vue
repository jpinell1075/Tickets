// This script defines a select input component.
// It renders a dropdown menu with options and binds the selected value to a model.
<script setup>
    defineProps({
        field: Object,
    });

    const value = defineModel('value', { default: null });
</script>

<template>
    <div class="w-full md:w-1/2 mt-3 px-2">
        <label :for="field.id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ field.label }} 
            <span class="text-red-500 dark:text-red-400" v-if="field.required">*</span>
        </label>
        <select 
            :name="field.name" 
            :id="field.id" 
            :required="field.required" 
            :readonly="field.readonly" 
            v-model="value"
            class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none sm:text-sm disabled:bg-gray-100 dark:disabled:bg-gray-800/50 disabled:text-gray-500 transition-colors"
        >
            <option value="null" disabled selected>Seleccione una opción</option>
            <option v-for="(val, key) in field.options" :key="key" :value="key">{{ val }}</option>
        </select>
    </div>
</template>