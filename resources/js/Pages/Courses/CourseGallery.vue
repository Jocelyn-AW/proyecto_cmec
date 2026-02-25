<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useFileUpload } from '@/composables/useImageDropped'
import { useAlert } from '@/composables/useAlert'
import Dropzone from '@/Components/Dropzone.vue'
import Alerta from '@/Components/Alerta.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, usePage } from '@inertiajs/vue3'

const props = defineProps({
    course: Object,
    images: Array,
})

defineOptions({
    layout: AuthenticatedLayout
})

const { alertState, warning, success, hideAlert } = useAlert()

// ── Subida ──────────────────────────────────────────
const showUploader = ref(false)

const { files, previews, isDragging, handleChange, handleDrop,
    handleDragEnter, handleDragLeave, removeAt, reset } = useFileUpload({
        multiple: true,
        maxFiles: 20,
        maxSizeMB: 2,
        acceptedTypes: ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'],
        onError: (msg) => alert(msg)
    })

const submitImages = () => {
    if (!files.value.length) return

    const form = new FormData()
    files.value.forEach((f, i) => form.append(`images[${i}]`, f))

    router.post(route('courses.gallery.update', props.course.id), form, {
        forceFormData: true,
        onSuccess: () => {
            reset()
            showUploader.value = false
        }
    })
}

// ── Eliminar ─────────────────────────────────────────
const confirmDelete = (image) => {
    warning('¿Eliminar esta foto?', {
        title: 'Eliminar foto',
        buttonText: 'Sí, eliminar',
        cancelText: 'Cancelar',
        onConfirm: () => deleteImage(image.id)
    })
}

const deleteImage = (mediaId) => {
    hideAlert()
    router.delete(route('courses.gallery.delete', {
        id: props.course.id,
        mediaId: mediaId
    }), {
        onSuccess: () => success('Foto eliminada correctamente')
    })
}

// ── Lightbox ──────────────────────────────────────────
const lightbox = ref({ show: false, index: 0 })

const openLightbox = (index) => {
    lightbox.value = { show: true, index }
}

const closeLightbox = () => {
    lightbox.value.show = false
}

const prevImage = () => {
    if (lightbox.value.index > 0) lightbox.value.index--
}

const nextImage = () => {
    if (lightbox.value.index < props.images.length - 1) lightbox.value.index++
}

// Navegar con teclado
const onKeydown = (e) => {
    if (!lightbox.value.show) return
    if (e.key === 'ArrowLeft') prevImage()
    if (e.key === 'ArrowRight') nextImage()
    if (e.key === 'Escape') closeLightbox()
}
</script>

<template>

    <Head title="Galería del curso" />
    <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
        <div class="space-y-5">
            <div class="">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Galería del curso: {{ course.topic }}</h3>
                <p class="text-sm text-gray-500">Sube o elimina fotos de la galería</p>
            </div>

            <div @keydown="onKeydown" tabindex="-1">

                <Alerta 
                    v-bind="alertState" 
                    @confirm="alertState.onConfirm ? alertState.onConfirm() : hideAlert()" 
                    @cancel="alertState.onCancel ? alertState.onCancel() : hideAlert()"
                    @close="hideAlert()" />

                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <button @click="router.get(route('courses.index'))" class="text-gray-500 hover:text-gray-700">
                            ← Volver
                        </button>
                    </div>

                    <button @click="showUploader = !showUploader"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        <span>+ Agregar más fotos</span>
                    </button>
                </div>

                <!-- Uploader (se muestra/oculta) -->
                <div v-if="showUploader" class="mb-6 rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <Dropzone multiple :previews="previews" :is-dragging="isDragging" :max-files="20"
                        @change="handleChange" @drop="handleDrop" @drag-enter="handleDragEnter"
                        @drag-leave="handleDragLeave" @remove-at="removeAt" @remove="reset" />
                    <div class="mt-3 flex justify-end gap-2">
                        <button @click="showUploader = false; reset()"
                            class="rounded-lg border px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">
                            Cancelar
                        </button>
                        <button @click="submitImages" :disabled="!files.length"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                            Subir {{ files.length ? `(${files.length})` : '' }}
                        </button>
                    </div>
                </div>

                <!-- Grid de imágenes -->
                <div v-if="images.length" class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                    <div v-for="(image, index) in images" :key="image.id"
                        class="group relative aspect-square overflow-hidden rounded-xl border border-gray-200 bg-gray-100">

                        <img :src="image.thumb ?? image.url" :alt="`Foto ${index + 1}`"
                            class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-105" />

                        <!-- Overlay con acciones -->
                        <div class="absolute inset-0 flex items-center justify-center gap-2
                            bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity">

                            <!-- Ver en grande -->
                            <button @click="openLightbox(index)"
                                class="rounded-full bg-white/90 p-2 text-gray-700 hover:bg-white">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6" />
                                </svg>
                            </button>

                            <!-- Eliminar -->
                            <button @click="confirmDelete(image)"
                                class="rounded-full bg-red-500/90 p-2 text-white hover:bg-red-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </div>

                        <!-- Número -->
                        <span class="absolute bottom-1.5 left-1.5 rounded bg-black/50 px-1.5 py-0.5 text-xs text-white">
                            {{ index + 1 }}
                        </span>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else
                    class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 py-20 text-center">
                    <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 21h18M3.75 3h16.5M4.5 3v18M19.5 3v18" />
                    </svg>
                    <p class="mt-3 text-sm text-gray-500">No hay fotos todavía</p>
                    <button @click="showUploader = true"
                        class="mt-3 text-sm font-medium text-blue-600 hover:text-blue-700">
                        Agregar la primera foto
                    </button>
                </div>

                <!-- Lightbox -->
                <Teleport to="body">
                    <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0"
                        leave-active-class="transition-opacity duration-200" leave-to-class="opacity-0">
                        <div v-if="lightbox.show"
                            class="fixed inset-0 z-50 flex items-center justify-center bg-black/90"
                            @click.self="closeLightbox">

                            <!-- Imagen -->
                            <img :src="images[lightbox.index]?.url" :alt="`Foto ${lightbox.index + 1}`"
                                class="max-h-[90vh] max-w-[90vw] rounded-lg object-contain shadow-2xl" />

                            <!-- Cerrar -->
                            <button @click="closeLightbox"
                                class="absolute top-4 right-4 rounded-full bg-white/10 p-2 text-white hover:bg-white/20">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>

                            <!-- Anterior -->
                            <button v-if="lightbox.index > 0" @click="prevImage"
                                class="absolute left-4 rounded-full bg-white/10 p-3 text-white hover:bg-white/20">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                            </button>

                            <!-- Siguiente -->
                            <button v-if="lightbox.index < images.length - 1" @click="nextImage"
                                class="absolute right-4 rounded-full bg-white/10 p-3 text-white hover:bg-white/20">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>

                            <!-- Contador -->
                            <div
                                class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-black/50 px-3 py-1 text-sm text-white">
                                {{ lightbox.index + 1 }} / {{ images.length }}
                            </div>
                        </div>
                    </Transition>
                </Teleport>

            </div>
        </div>
    </div>
</template>