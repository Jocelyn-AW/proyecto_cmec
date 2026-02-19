<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'
import { useImageUpload } from '@/composables/useImageUpload'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    /** El banner que se esta editando (objeto completo) */
    banner: {
        type: Object,
        default: null
    }
})

const emit = defineEmits(['close', 'updated', 'warning', 'error'])

const form = ref({
    link: '',
    order: 0,
    currentImage: null
})

const isSubmitting = ref(false)

const { file: imageFile, preview: imagePreview, handleChange: onImageChange, reset: resetImage } = useImageUpload({
    maxSizeMB: 1,
    onError: (msg) => emit('warning', msg)
})

// Cuando cambia el banner seleccionado, poblar el form
watch(() => props.banner, (banner) => {
    if (banner) {
        form.value = {
            link: banner.link || '',
            order: banner.order ?? 0,
            currentImage: banner.image
        }
        resetImage()
    }
}, { immediate: true })

const resetForm = () => {
    form.value = { link: '', order: 0, currentImage: null }
    resetImage()
}

const close = () => {
    resetForm()
    emit('close')
}

const handleImageChange = (event) => {
    onImageChange(event)
}

const submit = async () => {
    if (!props.banner) return

    isSubmitting.value = true

    const formData = new FormData()
    formData.append('order', form.value.order)

    if (form.value.link && form.value.link.trim() !== '') {
        formData.append('link', form.value.link)
    }

    // Solo enviar imagen si se selecciono una nueva
    if (imageFile.value) {
        formData.append('image', imageFile.value)
    }

    router.post(`/banners/${props.banner.id}`, formData, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            close()
            emit('updated')
        },
        onError: () => {
            emit('error', 'Error al actualizar el banner')
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
            Editar banner
        </template>

        <div class="p-6 space-y-4">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ imagePreview ? 'Nueva imagen' : 'Imagen actual' }}
                </label>
                <div class="w-full h-48 overflow-hidden rounded-lg border border-gray-300">
                    <img
                        :src="imagePreview || form.currentImage"
                        class="w-full h-full object-cover"
                        alt="Banner actual"
                    >
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Cambiar imagen (opcional)
                </label>
                <input
                    type="file"
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                    @change="handleImageChange"
                    class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                >
                <p class="mt-1 text-xs text-gray-500">
                    Formato permitido: JPG, PNG, WEBP (Peso max. 1MB). Deja vacio para mantener la imagen actual.
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Link (opcional)
                </label>
                <input
                    v-model="form.link"
                    type="text"
                    placeholder="https://ejemplo.com"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </div>
        </div>

        <template #footer>
            <button
                @click="close"
                :disabled="isSubmitting"
                class="rounded-md border border-transparent py-2 px-4 text-sm text-slate-600 transition-all hover:bg-slate-100 disabled:opacity-50"
            >
                Cancelar
            </button>

            <button
                @click="submit"
                :disabled="isSubmitting"
                class="rounded-md bg-blue-600 py-2 px-4 text-sm text-white transition-all shadow-md hover:bg-blue-700 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
            >
                {{ isSubmitting ? 'Actualizando...' : 'Actualizar banner' }}
            </button>
        </template>
    </Modal>
</template>
