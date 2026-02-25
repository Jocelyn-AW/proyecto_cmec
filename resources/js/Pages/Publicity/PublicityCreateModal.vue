<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'
import { useImageUpload } from '@/composables/useImageUpload'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    nextOrder: {
        type: Number,
        default: 0
    }
})

const emit = defineEmits(['close', 'created', 'warning', 'error', 'info'])

const form = ref({
    link: ''
})

const isSubmitting = ref(false)

const { file: imageFile, preview: imagePreview, handleChange: onImageChange, reset: resetImage } = useImageUpload({
    maxSizeMB: 1,
    onError: (msg) => emit('warning', msg)
})

const resetForm = () => {
    form.value = { link: '' }
    resetImage()
}

const close = () => {
    resetForm()
    emit('close')
}

const submit = () => {
    if (!imageFile.value) {
        emit('info', 'Por favor selecciona una imagen')
        return
    }

    isSubmitting.value = true

    const formData = new FormData()
    formData.append('image', imageFile.value)
    formData.append('order', props.nextOrder)

    if (form.value.link && form.value.link.trim() !== '') {
        formData.append('link', form.value.link)
    }

    router.post('/publicity', formData, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            close()
            emit('created')
        },
        onError: () => {
            emit('error', 'Error al crear la publicidad')
        },
        onFinish: () => {
            isSubmitting.value = false
        }
    })
}
</script>

<template>
    <Modal :show="show" @close="close" maxWidth="2xl">
        <template #title>
            Crear publicidad
        </template>

        <div class="p-6 space-y-4">
            <div v-if="imagePreview" class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Vista previa</label>
                <div class="w-full h-48 overflow-hidden rounded-lg border border-gray-300">
                    <img :src="imagePreview" class="w-full h-full object-cover" alt="Vista previa">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Imagen <span class="text-red-500">*</span>
                </label>
                <input type="file" accept="image/jpeg,image/png,image/jpg,image/webp" @change="onImageChange"
                    class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                <p class="mt-1 text-xs text-gray-500">
                    Formato permitido: JPG, PNG, WEBP (Peso máx. 1MB)
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Link (opcional)
                </label>
                <input v-model="form.link" type="text" placeholder="https://ejemplo.com"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:ring-green-500">
            </div>
        </div>

        <template #footer>
            <button @click="close" :disabled="isSubmitting"
                class="rounded-md border border-transparent py-2 px-4 text-sm text-slate-600 transition-all hover:bg-slate-100 disabled:opacity-50">
                Cancelar
            </button>

            <button @click="submit" :disabled="isSubmitting || !imageFile"
                class="rounded-md bg-green-600 py-2 px-4 text-sm text-white transition-all shadow-md hover:bg-green-700 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                {{ isSubmitting ? 'Creando...' : 'Crear publicidad' }}
            </button>
        </template>
    </Modal>
</template>