<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { useAlert } from '@/composables/useAlert'
import { useFileUpload } from '@/composables/useImageDropped'
import Dropzone from '@/Components/Dropzone.vue'
import Alerta from '@/Components/Alerta.vue'
import AppLoader from '@/Components/AppLoader.vue'
import { onMounted, ref } from 'vue'

defineOptions({ layout: AuthenticatedLayout })

const { alertState, success, errorA, warning, hideAlert } = useAlert()
const page = usePage()

const props = defineProps({
    album: { type: Object, default: () => ({}) },
    photos: { type: Array, default: () => [] },
    event_label: { type: String, default: 'Evento' },
    event_name: { type: String, default: null },
    flash: { type: Object, default: () => ({}) },
})

onMounted(() => {
    if (page.props.flash?.success || props.flash?.success)
        success(page.props.flash?.success || props.flash?.success)
    if (page.props.flash?.error || props.flash?.error)
        errorA(page.props.flash?.error || props.flash?.error)
})

// subida de fotos
const showUploader = ref(false)
const isUploading = ref(false)  // ← NUEVO

const { files, previews, isDragging, handleChange, handleDrop,
    handleDragEnter, handleDragLeave, removeAt, reset } = useFileUpload({
        multiple: true,
        maxFiles: 20,
        maxSizeMB: 2,
        acceptedTypes: ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'],
        onError: (msg) => alert(msg),
    })

const submitPhotos = () => {
    if (!files.value.length) return

    const form = new FormData()
    files.value.forEach((f, i) => form.append(`images[${i}]`, f))

    isUploading.value = true  // BLOQUEAR

    router.post(route('albums.photos.upload', props.album.id), form, {
        forceFormData: true,
        onSuccess: () => {
            reset()
            showUploader.value = false
        },
        onError: () => {
            errorA('Error al subir las fotos. Intenta de nuevo.')
        },
        onFinish: () => {
            isUploading.value = false  // DESBLOQUEAR siempre
        },
    })
}

// eliminar foto
const confirmDelete = (photo) => {
    warning('¿Eliminar esta foto? Esta acción no se puede deshacer.', {
        title: 'Eliminar foto',
        buttonText: 'Sí, eliminar',
        cancelText: 'Cancelar',
        onConfirm: () => {
            hideAlert()
            router.delete(route('albums.photos.delete', {
                id: props.album.id,
                mediaId: photo.id,
            }))
        },
    })
}

// lightbox
const lightbox = ref({ show: false, index: 0 })

const openLightbox = (index) => { lightbox.value = { show: true, index } }
const closeLightbox = () => { lightbox.value.show = false }
const prevImage = () => { if (lightbox.value.index > 0) lightbox.value.index-- }
const nextImage = () => { if (lightbox.value.index < props.photos.length - 1) lightbox.value.index++ }

const onKeydown = (e) => {
    if (!lightbox.value.show) return
    if (e.key === 'ArrowLeft') prevImage()
    if (e.key === 'ArrowRight') nextImage()
    if (e.key === 'Escape') closeLightbox()
}
</script>

<template>

    <Head :title="`${album.title} · Fotos`" />

    <div class="p-6 border-t border-gray-100 sm:p-6" @keydown="onKeydown" tabindex="-1">
        <div class="space-y-6">

            <!-- encabezado -->
            <div class="flex items-center justify-between">
                <div>
                    <!-- datos -->
                    <div class="flex items-center gap-1.5 text-sm text-gray-500 mb-1 flex-wrap">
                        <span>{{ event_label }}</span>
                        <template v-if="event_name">
                            <span>/</span>
                            <span class="text-gray-600 font-medium truncate max-w-[200px]">{{ event_name }}</span>
                        </template>
                        <span>/</span>
                        <button
                            @click="router.get(route('albums.index', { event_type: album.event_type, event_id: album.event_id }))"
                            class="hover:text-gray-700 transition-colors">
                            Álbumes
                        </button>
                        <span>/</span>
                        <span class="text-gray-700 font-medium truncate max-w-[200px]">{{ album.title }}</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">{{ album.title }}</h3>
                    <p v-if="album.description" class="text-sm text-gray-500 mt-0.5">{{ album.description }}</p>
                </div>
                <!-- botón Agregar fotos igual que antes -->
            </div>

            <!-- subir imagenes -->
            <div v-if="showUploader" class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <Dropzone multiple :previews="previews" :is-dragging="isDragging" :max-files="20" :columns="'5'"
                    @change="handleChange" @drop="handleDrop" @drag-enter="handleDragEnter"
                    @drag-leave="handleDragLeave" @remove-at="removeAt" @remove="reset" />
                <div class="mt-3 flex justify-end gap-2">
                    <button @click="showUploader = false; reset()"
                        class="rounded-lg border px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 transition-colors">
                        Cancelar
                    </button>
                    <button @click="submitPhotos" :disabled="!files.length || isUploading"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 transition-colors">
                        Subir {{ files.length ? `(${files.length})` : '' }}
                    </button>
                </div>
            </div>

            <!-- grid de las fotos -->
            <div v-if="photos.length" class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                <div v-for="(photo, index) in photos" :key="photo.id"
                    class="group relative aspect-square overflow-hidden rounded-xl border border-gray-200 bg-gray-100">

                    <img :src="photo.url" :alt="`Foto ${index + 1}`"
                        class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-105" />

                    <!-- overlay con acciones -->
                    <div class="absolute inset-0 flex items-center justify-center gap-2
                        bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click="openLightbox(index)"
                            class="rounded-full bg-white/90 p-2 text-gray-700 hover:bg-white transition-colors"
                            title="Ver en grande">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6" />
                            </svg>
                        </button>
                        <button @click="confirmDelete(photo)"
                            class="rounded-full bg-red-500/90 p-2 text-white hover:bg-red-600 transition-colors"
                            title="Eliminar foto">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </button>
                    </div>

                    <!-- numero -->
                    <span class="absolute bottom-1.5 left-1.5 rounded bg-black/50 px-1.5 py-0.5 text-xs text-white">
                        {{ index + 1 }}
                    </span>
                </div>
            </div>

            <!-- sin datos -->
            <div v-else
                class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 py-20 text-center">
                <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 21h18M3.75 3h16.5M4.5 3v18M19.5 3v18" />
                </svg>
                <p class="mt-3 text-sm text-gray-500">Este álbum no tiene fotos todavía</p>
                <button @click="showUploader = true" class="mt-3 text-sm font-medium text-blue-600 hover:text-blue-700">
                    Agregar la primera foto
                </button>
            </div>
        </div>
    </div>

    <!-- OVERLAY DE CARGA -->
    <Teleport to="body">
        <Transition enter-active-class="duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="isUploading"
                class="fixed inset-0 z-[9999] flex flex-col items-center justify-center gap-4 bg-black/60 backdrop-blur-sm">
                <AppLoader :size="72" color="#ffffff" />
                <p class="text-white text-sm font-medium tracking-wide">
                    Subiendo fotos, por favor espera...
                </p>
            </div>
        </Transition>
    </Teleport>

    <!-- LIGHTBOX -->
    <Teleport to="body">
        <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-200" leave-to-class="opacity-0">
            <div v-if="lightbox.show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90"
                @click.self="closeLightbox">

                <img :src="photos[lightbox.index]?.url" :alt="`Foto ${lightbox.index + 1}`"
                    class="max-h-[90vh] max-w-[90vw] rounded-lg object-contain shadow-2xl" />

                <button @click="closeLightbox"
                    class="absolute top-4 right-4 rounded-full bg-white/10 p-2 text-white hover:bg-white/20 transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <button v-if="lightbox.index > 0" @click="prevImage"
                    class="absolute left-4 rounded-full bg-white/10 p-3 text-white hover:bg-white/20 transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </button>

                <button v-if="lightbox.index < photos.length - 1" @click="nextImage"
                    class="absolute right-4 rounded-full bg-white/10 p-3 text-white hover:bg-white/20 transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>

                <div
                    class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-black/50 px-3 py-1 text-sm text-white">
                    {{ lightbox.index + 1 }} / {{ photos.length }}
                </div>
            </div>
        </Transition>
    </Teleport>

    <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
        :buttonText="alertState.buttonText" :cancelText="alertState.cancelText"
        @confirm="alertState.onConfirm ? alertState.onConfirm() : hideAlert()"
        @cancel="alertState.onCancel ? alertState.onCancel() : hideAlert()" @close="hideAlert()" />
</template>