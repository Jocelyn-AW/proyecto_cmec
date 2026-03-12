<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    show: { type: Boolean, default: false },
    eventType: { type: String, default: '' },
    eventId: { type: Number, default: null },
})

const emit = defineEmits(['close', 'created', 'error'])

const form = ref({ title: '', description: '' })
const formError = ref('')
const isSubmitting = ref(false)

const resetForm = () => {
    form.value = { title: '', description: '' }
    formError.value = ''
}

const close = () => {
    resetForm()
    emit('close')
}

const submit = () => {
    if (!form.value.title.trim()) {
        formError.value = 'El título es obligatorio.'
        return
    }
    formError.value = ''
    isSubmitting.value = true

    router.post(route('albums.store'), {
        title: form.value.title,
        description: form.value.description,
        event_type: props.eventType,
        event_id: props.eventId,
    }, {
        preserveScroll: true,
        onSuccess: () => { close(); emit('created') },
        onError: () => emit('error', 'Error al crear el álbum'),
        onFinish: () => { isSubmitting.value = false },
    })
}
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
                    <div v-if="show" class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">

                        <!-- Header -->
                        <div class="px-6 pt-6 pb-4 border-b border-slate-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-50">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold text-slate-900">Nuevo álbum</h2>
                                        <p class="text-sm text-slate-500">Completa los datos del álbum</p>
                                    </div>
                                </div>
                                <button @click="close"
                                    class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="px-6 py-5 space-y-4">

                            <!-- Título -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                    Título <span class="text-red-500">*</span>
                                </label>
                                <input v-model="form.title" type="text" autofocus
                                    placeholder="Ej. Ceremonia de apertura"
                                    class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition-colors" />
                                <p v-if="formError" class="mt-1 text-xs text-red-500">{{ formError }}</p>
                            </div>

                            <!-- Descripción -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                    Descripción
                                    <span class="text-slate-400 font-normal">(opcional)</span>
                                </label>
                                <textarea v-model="form.description" rows="3"
                                    placeholder="Describe brevemente el contenido del álbum..."
                                    class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition-colors resize-none" />
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                            <div class="flex items-center justify-end gap-2">
                                <button @click="close" :disabled="isSubmitting"
                                    class="px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 border border-slate-300 bg-white hover:bg-slate-50 transition-colors disabled:opacity-50">
                                    Cancelar
                                </button>
                                <button @click="submit" :disabled="isSubmitting || !form.title.trim()"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg v-if="isSubmitting" class="w-4 h-4 animate-spin" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                                    </svg>
                                    {{ isSubmitting ? 'Creando...' : 'Crear álbum' }}
                                </button>
                            </div>
                        </div>

                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>