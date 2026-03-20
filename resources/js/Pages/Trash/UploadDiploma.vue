<script setup>
import Modal from '@/Components/Modal.vue';
import Dropzone from '@/Components/Dropzone.vue';
import { useFileUpload } from '@/composables/useImageDropped';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    diplomaUrl: {
        type: String,
        default: ''
    },
    attendee: {
        type: Object,
        default: () => ({})
    }
})

const emit = defineEmits(['close'])

const diploma = useFileUpload({
    acceptedTypes: ['application/pdf'],
    maxSizeMB: 5,
    onError: (msg) => alert(msg)
})

const submit = () => {
    if (!diploma.file.value) {
        alert('Por favor selecciona un archivo PDF')
        return
    }

    const formData = new FormData();
    formData.append('diploma', diploma.file.value);

    router.post(route('attendees.upload-diploma', props.attendee.id), formData, {
        forceFormData: true,
        onSuccess: () => {
            diploma.reset();
            emit('close');
        },
        onError: () => {
            alert('Error al subir el diploma. Por favor intenta de nuevo.');
        }
    });
}

const close = () => {
    diploma.reset()
    emit('close')
}

</script>
<template>
    <Modal :show="show" @close="close" maxWidth="2xl">
        <template #title>
            Agregar diploma
        </template>

        <div class="p-6 space-y-4">
            <Dropzone :preview="diploma.preview.value" :is-dragging="diploma.isDragging.value"
                :accept="'application/pdf'" label="Arrastra tu PDF aqui o haz clic para seleccionar"
                hint="PDF (max. 5MB)" @change="diploma.handleChange" @drop="diploma.handleDrop"
                @drag-enter="diploma.handleDragEnter" @drag-leave="diploma.handleDragLeave" @remove="diploma.reset" />

        </div>

        <template #footer>
            <button @click="close" :disabled="isSubmitting"
                class="rounded-md border border-transparent py-2 px-4 text-sm text-slate-600 transition-all hover:bg-slate-100 disabled:opacity-50">
                Cancelar
            </button>

            <button @click="submit" :disabled="!diploma.file.value"
                class="rounded-md bg-green-600 py-2 px-4 text-sm text-white transition-all shadow-md hover:bg-green-700 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                {{ isSubmitting ? 'Agregando diploma...' : 'Agregar diploma' }}
            </button>
        </template>
    </Modal>

</template>