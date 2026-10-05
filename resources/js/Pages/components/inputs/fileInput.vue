<script setup>
    import { ref, onUnmounted } from 'vue';

    const props = defineProps({
        field: {
            type: Object,
            required: true,
            accept: 'image/*,application/pdf,text/plain,.doc,.docx,.xls,.xlsx'
        }
    });

    const file = defineModel('file');
    const previewUrl = ref('');
    const fileType = ref('');
    let previousUrl = null;

    function handleFileChange(event) {
        const selected = event.target.files && event.target.files[0];
        file.value = selected;

        if (previousUrl) {
            URL.revokeObjectURL(previousUrl);
            previousUrl = null;
        }
        previewUrl.value = '';
        fileType.value = '';

        if (!selected) return;

        const type = selected.type;

        if (type.startsWith('image/') || type === 'application/pdf' || type.startsWith('text/')) {
            previewUrl.value = URL.createObjectURL(selected);
            previousUrl = previewUrl.value;
            fileType.value = type;
        }
    }

    onUnmounted(() => {
        if (previousUrl) URL.revokeObjectURL(previousUrl);
    });
</script>

<template>
    <div class="flex flex-wrap -mx-2">
        
        <div class="w-full md:w-1/2 px-2 mt-3">
            <label :for="field.id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                {{ field.label }}
            </label>
            <input
                type="file"
                :name="field.name"
                :id="field.id"
                :required="field.required"
                :readonly="field.readonly"
                @change="handleFileChange"
                :multiple="false"
                :accept="field.accept"
                class="block w-full text-sm text-gray-900 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-md cursor-pointer bg-gray-50 dark:bg-gray-900 focus:outline-none focus:border-blue-500 focus:ring-blue-500 transition-colors
                       file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-800 dark:file:text-gray-200 dark:hover:file:bg-gray-700"
            />
        </div>

        <div v-if="previewUrl" class="w-full md:w-1/2 px-2 mt-4 md:mt-3 flex justify-center md:justify-start">
            
            <div class="w-full max-w-[350px] h-[350px] overflow-hidden rounded-md border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 flex justify-center items-center shadow-sm transition-colors">
                
                <img v-if="fileType.startsWith('image/')" :src="previewUrl" alt="Preview" class="w-full h-full object-contain" />

                <iframe v-else-if="fileType === 'application/pdf'" :src="previewUrl" class="w-full h-full border-none"></iframe>

                <div v-else-if="fileType.startsWith('text/')" class="w-full h-full">
                    <iframe :src="previewUrl" class="w-full h-full border-none bg-white dark:bg-white" frameborder="0"></iframe>
                </div>

                <div v-else class="text-center p-4 text-gray-700 dark:text-gray-300">
                    <p class="text-sm font-medium">Archivo cargado:</p>
                    <p class="text-xs truncate w-[300px] mt-1" :title="file?.name">{{ file?.name }}</p>
                </div>
                
            </div>
            
        </div>
        
    </div>
</template>