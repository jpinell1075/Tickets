// This script sets up a modal for deleting records.
// It uses Inertia.js for form handling and Bootstrap for modal functionality.
<script setup>
    import { useForm } from '@inertiajs/vue3';
    import { ref } from 'vue';
    import Modal from './Modal.vue';

    const props = defineProps({
        callback: {
            type: Function,
            required: false,
            default: null,
        },
    });

    const form_request = useForm({
        _method: 'DELETE',
    });

    const modalRef = ref(null);
    
    const submit = () => {
        let form = document.getElementById('deleteForm');
        let delete_url = form.action;
        
        form_request.delete(delete_url, {
            onSuccess: (response) => {
                form.reset();
                closeDeleteModal();

                if(window.atm_tables){
                    window.atm_tables.forEach((table) => {
                        table.refresh();
                    });
                }

                if (props.callback) {
                    props.callback(response);
                }
            },
            onError: () => {
                showToast('Error al eliminar el registro');
                closeDeleteModal();
            },
        });
    }

    function openDeleteModal(url) {
        const form = document.getElementById('deleteForm');
        form.action = url;
        modalRef.value.openModal();
    }

    function closeDeleteModal() {
        const form = document.getElementById('deleteForm');
        form.action = '';
        modalRef.value.closeModal();
    }

    defineExpose({
        openDeleteModal,
        closeDeleteModal,
    });
</script>

<template>
    <Modal :title="'¿Está Seguro de que desea eliminar este registro?'" :id="'delete_modal'" ref="modalRef" :accept-callback="submit">
        <form id="deleteForm" action="" method="POST" class="d-none"></form>
    </Modal>
</template>