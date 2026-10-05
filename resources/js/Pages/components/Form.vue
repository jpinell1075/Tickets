// This script defines a reusable form component.
// It dynamically renders input fields based on the provided model.
// The form supports custom submission methods and integrates with global table instances for refreshing data.
<script setup>
    import { computed, reactive } from 'vue';
    import { useForm } from '@inertiajs/vue3';

    import INPUT from './inputs/input.vue';
    import SELECT from './inputs/select.vue';
    const componentMap = {
        INPUT,
        SELECT,
    };

    const props = defineProps({
        route: String,
        method: String,
        model: Object,
        form: Object,
        id: {
            type: String,
            default: () => Math.random().toString(36).substr(2, 9),
        },
        isModal: {
            default: false,
        },
        callback: {
            type: Function,
            required: false,
            default: null,
        },
        showSubmitButton: {
            type: Boolean,
            default: true,
        },
    });

    const form = props.form ?? reactive({});
    Object.entries(props.model.form_fields).forEach(([key, value]) => {
        form[key] = null;
    });

    const formMethod = computed(() => {
        return ['POST', 'PUT', 'PATCH'].includes(props.method) ? 'POST' : props.method;
    });

    const submitForm = () => {
        const send_form = useForm(form);

        send_form.submit(formMethod.value.toLowerCase(), props.route, {
            preserveScroll: true,
            onSuccess: (response) => {
                if(props.isModal) {
                    props.isModal.closeModal();
                }

                if(window.atm_tables && props.callback === null) {
                    window.atm_tables.forEach((table) => {
                        table.refresh();
                    });
                }
                
                if (props.callback) {
                    props.callback(response);
                }
            },
        });
    };

    function submitFormData() {
        document.getElementById('form-' + props.id).dispatchEvent(new Event('submit', { cancelable: true }));
    }

    defineExpose({
        submitFormData,
    });

</script>

<template>
    <form @submit.prevent="submitForm" class="p-3 mt-2" :id="'form-'+id">
        <div class="flex flex-wrap -mx-2">
            
            <input type="hidden" name="_method" :value="props.method">

            <template v-for="(field, index) in model.form_fields" :key="index">
                <component 
                    :is="componentMap[field.input_type.toUpperCase()]" 
                    v-model:value="form[field.name]"
                    :field="field">
                </component>
            </template>

            <slot name="extraContent"></slot>

            <div class="w-full px-2 mt-5 flex justify-between items-center">
                <button 
                    v-if="props.showSubmitButton"
                    type="submit" 
                    class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
                >
                    Agregar
                </button>
                
                <div class="flex items-center gap-2">
                    <slot name="secondaryButtons"></slot>
                </div>
            </div>
            
        </div>
    </form>
</template>