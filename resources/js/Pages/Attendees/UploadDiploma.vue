<script setup>
import { ref, computed } from 'vue'
import Dropzone from '@/Components/Dropzone.vue'
import { useFileUpload } from '@/composables/useImageDropped'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    maxWidth: {
        type: String,
        default: '2xl'
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

const isSubmitting = ref(false)

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

    isSubmitting.value = true

    const formData = new FormData()
    formData.append('diploma', diploma.file.value)

    router.post(route('attendees.upload-diploma', props.attendee.id), formData, {
        forceFormData: true,
        onSuccess: () => {
            diploma.reset()
            isSubmitting.value = false
            emit('close')
        },
        onError: () => {
            isSubmitting.value = false
            alert('Error al subir el diploma. Por favor intenta de nuevo.')
        }
    })
}

const close = () => {
    diploma.reset()
    emit('close')
}

const maxWidthClass = computed(() => {
    const sizes = {
        sm: 'max-w-sm',
        md: 'max-w-md',
        lg: 'max-w-lg',
        xl: 'max-w-xl',
        '2xl': 'max-w-2xl'
    }
    return sizes[props.maxWidth] || 'max-w-2xl'
})
</script>

<template>
    <Teleport to="body">
        <Transition enter-active-class="duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="close" />

                <!-- Modal -->
                <Transition enter-active-class="duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0" leave-active-class="duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-4">
                    <div v-if="show"
                        :class="[maxWidthClass, 'relative w-full bg-white rounded-2xl shadow-2xl overflow-hidden']">
                        <!-- Header -->
                        <div class="px-6 pt-6 pb-4 border-b border-slate-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-50">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold text-slate-900">
                                            Agregar diploma
                                        </h2>
                                        <p class="text-sm text-slate-500">
                                            Sube el diploma en formato PDF
                                        </p>
                                    </div>
                                </div>
                                <button @click="close" :disabled="isSubmitting"
                                    class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors disabled:opacity-50">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6">
                            <div class="space-y-4">
                                <!-- Dropzone Container -->
                                <div v-if="!diploma.file.value"
                                    class="rounded-xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-1 transition-colors hover:border-blue-300 hover:bg-blue-50/30">
                                    <Dropzone :preview="diploma.preview.value" :is-dragging="diploma.isDragging.value"
                                        accept="application/pdf"
                                        label="Arrastra tu PDF aqui o haz clic para seleccionar" hint="PDF (max. 5MB)"
                                        @change="diploma.handleChange" @drop="diploma.handleDrop"
                                        @drag-enter="diploma.handleDragEnter" @drag-leave="diploma.handleDragLeave"
                                        @remove="diploma.reset" />
                                </div>

                                <!-- File selected indicator -->
                                <Transition enter-active-class="duration-200 ease-out"
                                    enter-from-class="opacity-0 -translate-y-2"
                                    enter-to-class="opacity-100 translate-y-0" leave-active-class="duration-150 ease-in"
                                    leave-from-class="opacity-100 translate-y-0"
                                    leave-to-class="opacity-0 -translate-y-2">
                                    <div v-if="diploma.file.value"
                                        class="flex items-center gap-3 p-4 rounded-xl bg-blue-50 border border-blue-100">
                                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-100">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-blue-900 truncate">
                                                {{ diploma.file.value.name }}
                                            </p>
                                            <p class="text-xs text-blue-600">
                                                {{ (diploma.file.value.size / 1024 / 1024).toFixed(2) }} MB
                                            </p>
                                        </div>
                                        <button @click="diploma.reset"
                                            class="p-1.5 rounded-lg text-blue-400 hover:text-blue-600 hover:bg-blue-100 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </Transition>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                            <div class="flex items-center justify-end gap-3">
                                <button @click="close" :disabled="isSubmitting"
                                    class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-slate-700 text-sm font-medium hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2 transition-colors disabled:opacity-50">
                                    Cancelar
                                </button>
                                <button @click="submit" :disabled="!diploma.file.value || isSubmitting"
                                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg v-if="isSubmitting" class="w-4 h-4 animate-spin" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                    </svg>
                                    <span>{{ isSubmitting ? 'Subiendo...' : 'Agregar diploma' }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
