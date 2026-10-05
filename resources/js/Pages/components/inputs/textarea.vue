// This script defines a generic input field component.
// It supports various input types and binds the value to a model.
<script setup>
    defineProps({
        field: Object
    });

    const value = defineModel('value');
</script>

<template>
    <div class="w-full mt-3 px-2">
        <label :for="field.id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ field.label }} 
            <span class="text-red-500 dark:text-red-400" v-if="field.required">*</span>
        </label>
        <textarea
            :placeholder="field.placeholder"
            :type="field.type" 
            :name="field.name" 
            :id="field.id" 
            :required="field.required" 
            :readonly="field.readonly"
            v-model="value"
            rows="4"
            class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none sm:text-sm read-only:bg-gray-100 dark:read-only:bg-gray-800/50 read-only:text-gray-500 cursor-default transition-colors"
        >
        </textarea>
    </div>
</template>