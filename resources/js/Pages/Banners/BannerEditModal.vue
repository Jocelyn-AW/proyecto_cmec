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
const isDragging = ref(false)

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

/* DRAG AND DROP DE LA IMAGEN */
const onDragOver = (e) => {
    e.preventDefault()
    isDragging.value = true
}

const onDragLeave = (e) => {
    e.preventDefault()
    isDragging.value = false
}

const onDrop = (e) => {
    e.preventDefault()
    isDragging.value = false

    const files = e.dataTransfer.files
    if (!files || files.length === 0) return

    const syntheticEvent = { target: { files } }
    onImageChange(syntheticEvent)
}
/* ------------------- */

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
            <!-- imagen actual o nueva -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ imagePreview ? 'Nueva imagen' : 'Imagen actual' }}
                </label>
                <div class="w-full h-48 overflow-hidden rounded-lg border border-gray-300">
                    <img :src="imagePreview || form.currentImage" class="w-full h-full object-cover"
                        alt="Banner actual">
                </div>
            </div>

            <!-- drag and drop -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Cambiar imagen (opcional)
                </label>

                <div @dragover="onDragOver" @dragleave="onDragLeave" @drop="onDrop" @click="$refs.fileInput.click()"
                    :class="[
                        'relative flex flex-col items-center justify-center w-full h-36 rounded-lg border-2 border-dashed cursor-pointer transition-all',
                        isDragging
                            ? 'border-blue-500 bg-blue-50'
                            : 'border-gray-300 bg-gray-50 hover:bg-gray-100 hover:border-gray-400'
                    ]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-2 text-gray-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12V4m0 0L8 8m4-4l4 4" />
                    </svg>

                    <p class="text-sm text-gray-500">
                        <span class="font-medium text-blue-600">Haz clic para seleccionar</span>
                        &nbsp;o arrastra y suelta aquí
                    </p>
                    <p class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP — Máx. 1MB</p>

                    <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/jpg,image/webp"
                        @change="handleImageChange" class="hidden">
                </div>

                <p v-if="imageFile" class="mt-1 text-xs text-gray-500 truncate">
                    📎 {{ imageFile.name }}
                </p>
                <p v-else class="mt-1 text-xs text-gray-500">
                    Deja vacío para mantener la imagen actual.
                </p>
            </div>

            <!-- link -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Link (opcional)
                </label>
                <input v-model="form.link" type="text" placeholder="https://ejemplo.com"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
        </div>

        <template #footer>
            <button @click="close" :disabled="isSubmitting"
                class="rounded-md border border-transparent py-2 px-4 text-sm text-slate-600 transition-all hover:bg-slate-100 disabled:opacity-50">
                Cancelar
            </button>

            <button @click="submit" :disabled="isSubmitting"
                class="rounded-md bg-blue-600 py-2 px-4 text-sm text-white transition-all shadow-md hover:bg-blue-700 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                {{ isSubmitting ? 'Actualizando...' : 'Actualizar banner' }}
            </button>
        </template>
    </Modal>
</template>