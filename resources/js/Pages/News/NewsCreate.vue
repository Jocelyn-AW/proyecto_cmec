<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Alerta from '@/Components/Alerta.vue'
import { useAlert } from '@/composables/useAlert'
import { useImageUpload } from '@/composables/useImageUpload'

defineOptions({
    layout: AuthenticatedLayout
})

const { alertState, success, errorA, warning } = useAlert()

const isSubmitting = ref(false)

const editorRef = ref(null)
const execCmd = (command, value = null) => {
    editorRef.value?.focus()
    document.execCommand(command, false, value)
}
// sinc el HTML del editor con forms.content
const onEditorInput = () => {
    form.value.content = editorRef.value?.innerHTML ?? ''
}

const form = ref({
    title: '',
    content: '',
    extract: '',
    link: '',
    type: 'noticia',
    is_active: true,
})

// imagen
const { file: imageFile, preview: imagePreview, handleChange: onImageChange, reset: resetImage } = useImageUpload({
    maxSizeMB: 1,
    onError: (msg) => warning(msg)
})

// PDF
const pdfFile = ref(null)
const pdfName = ref('')

const onPdfChange = (event) => {
    const file = event.target.files[0]
    if (!file) return

    const maxSize = 10 * 1024 * 1024 // 10MB
    if (file.size > maxSize) {
        warning('El PDF no puede pesar más de 10MB')
        event.target.value = ''
        return
    }

    pdfFile.value = file
    pdfName.value = file.name
}

const removePdf = () => {
    pdfFile.value = null
    pdfName.value = ''
}

const removeImage = () => {
    resetImage()
}

const handleConfirm = () => {
    alertState.value.onConfirm?.()
    alertState.value.show = false
}

const handleCancel = () => {
    alertState.value.onCancel?.()
    alertState.value.show = false
}

// ─── Drag & Drop imagen ────────────────────────────────────
const isImageDragging = ref(false)

const onImageDragOver = (e) => { e.preventDefault(); isImageDragging.value = true }
const onImageDragLeave = (e) => { e.preventDefault(); isImageDragging.value = false }
const onImageDrop = (e) => {
    e.preventDefault()
    isImageDragging.value = false
    const files = e.dataTransfer.files
    if (!files?.length) return
    onImageChange({ target: { files } })
}

/* DRAG AND DROP DE LA IMAGEN */
const isPdfDragging = ref(false)

const onPdfDragOver = (e) => { e.preventDefault(); isPdfDragging.value = true }
const onPdfDragLeave = (e) => { e.preventDefault(); isPdfDragging.value = false }
const onPdfDrop = (e) => {
    e.preventDefault()
    isPdfDragging.value = false
    const files = e.dataTransfer.files
    if (!files?.length) return
    onPdfChange({ target: { files } })
}

// validacion de campos
const canSubmit = computed(() => {
    return form.value.title.trim() !== '' &&
        form.value.content.trim() !== '' &&
        form.value.type !== ''
})

const cancel = () => {
    router.get('/news')
}

const submit = () => {
    if (!canSubmit.value) {
        warning('Por favor completa los campos requeridos: título, contenido y tipo')
        return
    }

    isSubmitting.value = true

    const formData = new FormData()
    formData.append('title', form.value.title)
    formData.append('content', form.value.content)
    formData.append('type', form.value.type)
    formData.append('is_active', form.value.is_active ? '1' : '0')

    if (form.value.extract.trim()) formData.append('extract', form.value.extract)
    if (form.value.link.trim()) formData.append('link', form.value.link)
    if (imageFile.value) formData.append('image', imageFile.value)
    if (pdfFile.value) formData.append('pdf', pdfFile.value)

    router.post('/news', formData, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            success('Noticia creada correctamente')
        },
        onError: (errors) => {
            if (errors.link) {
                errorA(errors.link)
            } else if (errors.title) {
                errorA(errors.title)
            } else if (errors.content) {
                errorA(errors.content)
            } else if (errors.image) {
                errorA(errors.image)
            } else if (errors.pdf) {
                errorA(errors.pdf)
            } else {
                errorA('Error al crear la noticia')
            }
        },
        onFinish: () => {
            isSubmitting.value = false
        }
    })
}
</script>

<template>
    <div>

        <Head title="Nueva noticia" />

        <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6 lg:ml-[290px]">
            <div class="space-y-6 max-w-5xl mx-auto">

                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Nueva noticia</h3>
                        <p class="text-sm text-gray-500">Completa los campos para publicar una nueva noticia o sesión
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button @click="cancel" :disabled="isSubmitting"
                            class="inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors disabled:opacity-50">
                            Cancelar
                        </button>
                        <button @click="submit" :disabled="isSubmitting || !canSubmit"
                            class="inline-flex h-10 items-center gap-2 rounded-lg bg-green-600 px-4 text-sm font-medium text-white hover:bg-green-700 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg v-if="isSubmitting" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                            </svg>
                            {{ isSubmitting ? 'Publicando...' : 'Publicar noticia' }}
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                    <!-- columna principal -->
                    <div class="lg:col-span-2 space-y-5">

                        <!-- titulo -->
                        <div
                            class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Título <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.title" type="text" placeholder="Escribe el título de la noticia..."
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-800 dark:text-white/90 dark:bg-gray-900 dark:border-gray-700 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500"
                                maxlength="255">
                            <p class="mt-1 text-xs text-gray-400 text-right">{{ form.title.length }}/255</p>
                        </div>

                        <!-- 
                        contenido -->
                        <div
                            class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">

                            <!-- barra de herramientas -->
                            <div
                                class="flex flex-wrap items-center gap-1 p-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">

                                <!-- formato de texto -->
                                <div class="flex items-center gap-0.5">
                                    <button type="button" @click="execCmd('bold')" title="Negrita"
                                        class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                            class="w-4 h-4">
                                            <path d="M6 4h8a4 4 0 010 8H6V4zm0 8h9a4 4 0 010 8H6v-8z" />
                                        </svg>
                                    </button>
                                    <button type="button" @click="execCmd('italic')" title="Cursiva"
                                        class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                            class="w-4 h-4">
                                            <path d="M10 4v3h2.21l-3.42 10H6v3h8v-3h-2.21l3.42-10H18V4z" />
                                        </svg>
                                    </button>
                                    <button type="button" @click="execCmd('underline')" title="Subrayado"
                                        class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                            class="w-4 h-4">
                                            <path
                                                d="M12 17c3.31 0 6-2.69 6-6V3h-2.5v8c0 1.93-1.57 3.5-3.5 3.5S8.5 12.93 8.5 11V3H6v8c0 3.31 2.69 6 6 6zm-7 2v2h14v-2H5z" />
                                        </svg>
                                    </button>
                                    <button type="button" @click="execCmd('strikeThrough')" title="Tachado"
                                        class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                            class="w-4 h-4">
                                            <path d="M10 19h4v-3h-4v3zM5 4v3h5v3h4V7h5V4H5zM3 14h18v-2H3v2z" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1" />

                                <!-- alineacion -->
                                <div class="flex items-center gap-0.5">
                                    <button type="button" @click="execCmd('justifyLeft')" title="Alinear izquierda"
                                        class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                            class="w-4 h-4">
                                            <path
                                                d="M15 15H3v2h12v-2zm0-8H3v2h12V7zM3 13h18v-2H3v2zm0 8h18v-2H3v2zM3 3v2h18V3H3z" />
                                        </svg>
                                    </button>
                                    <button type="button" @click="execCmd('justifyCenter')" title="Centrar"
                                        class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                            class="w-4 h-4">
                                            <path
                                                d="M7 15v2h10v-2H7zm-4 6h18v-2H3v2zm0-8h18v-2H3v2zm4-6v2h10V7H7zM3 3v2h18V3H3z" />
                                        </svg>
                                    </button>
                                    <button type="button" @click="execCmd('justifyRight')" title="Alinear derecha"
                                        class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                            class="w-4 h-4">
                                            <path
                                                d="M3 21h18v-2H3v2zm6-4h12v-2H9v2zm-6-4h18v-2H3v2zm6-4h12V7H9v2zM3 3v2h18V3H3z" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1" />

                                <!-- deshacer/rehacer -->
                                <div class="flex items-center gap-0.5">
                                    <button type="button" @click="execCmd('undo')" title="Deshacer"
                                        class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                            class="w-4 h-4">
                                            <path
                                                d="M12.5 8c-2.65 0-5.05.99-6.9 2.6L2 7v9h9l-3.62-3.62c1.39-1.16 3.16-1.88 5.12-1.88 3.54 0 6.55 2.31 7.6 5.5l2.37-.78C21.08 11.03 17.15 8 12.5 8z" />
                                        </svg>
                                    </button>
                                    <button type="button" @click="execCmd('redo')" title="Rehacer"
                                        class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                            class="w-4 h-4">
                                            <path
                                                d="M18.4 10.6C16.55 8.99 14.15 8 11.5 8c-4.65 0-8.58 3.03-9.96 7.22L3.9 16c1.05-3.19 4.05-5.5 7.6-5.5 1.95 0 3.73.72 5.12 1.88L13 16h9V7l-3.6 3.6z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- editable -->
                            <div class="p-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Contenido <span class="text-red-500">*</span>
                                </label>
                                <p class="text-xs text-gray-400 mb-2">Contenido completo de la noticia</p>
                                <div ref="editorRef" contenteditable="true" @input="onEditorInput"
                                    class="min-h-[260px] w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-800 dark:text-white/90 dark:bg-gray-900 dark:border-gray-700 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500 leading-relaxed prose prose-sm max-w-none"
                                    :placeholder="'Escribe el contenido completo de la noticia...'" />
                                <p class="mt-1 text-xs text-gray-400 text-right">{{ form.content.length }} caracteres
                                </p>
                            </div>
                        </div>

                        <!-- extracto -->
                        <div
                            class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Extracto <span class="text-gray-400 text-xs">(opcional)</span>
                            </label>
                            <p class="text-xs text-gray-400 mb-2">Resumen corto que aparecerá en la tarjeta de la
                                noticia</p>
                            <textarea v-model="form.extract" rows="3"
                                placeholder="Breve descripción para la vista previa..."
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-800 dark:text-white/90 dark:bg-gray-900 dark:border-gray-700 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500 resize-none"
                                maxlength="500" />
                            <p class="mt-1 text-xs text-gray-400 text-right">{{ form.extract.length }}/500</p>
                        </div>

                        <!-- link -->
                        <div
                            class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Link <span class="text-gray-400 text-xs">(opcional)</span>
                            </label>
                            <p class="text-xs text-gray-400 mb-2">URL externa relacionada con la noticia</p>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                    </svg>
                                </span>
                                <input v-model="form.link" type="url" placeholder="https://ejemplo.com"
                                    class="block w-full rounded-lg border border-gray-300 pl-9 pr-3 py-2.5 text-sm text-gray-800 dark:text-white/90 dark:bg-gray-900 dark:border-gray-700 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                            </div>
                        </div>
                    </div>

                    <!-- columna lateral -->
                    <div class="space-y-5">

                        <!-- estado y tipo -->
                        <div
                            class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5 space-y-4">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Configuración</h4>

                            <!-- tipo -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Tipo <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select v-model="form.type"
                                        class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 pr-8 text-sm appearance-none text-gray-800 dark:text-white/90 dark:bg-gray-900 dark:border-gray-700 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                                        <option value="noticia">Noticia</option>
                                        <option value="sesion">Sesión</option>
                                    </select>
                                    <span
                                        class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                                        <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16"
                                            fill="none">
                                            <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke=""
                                                stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            <!-- estado -->
                            <div
                                class="flex items-center justify-between py-2 border-t border-gray-100 dark:border-gray-800">
                                <div>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Publicar activo</p>
                                    <p class="text-xs text-gray-400">Visible inmediatamente al guardar</p>
                                </div>
                                <button type="button" @click="form.is_active = !form.is_active"
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                    :class="form.is_active ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'">
                                    <span
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm transition duration-200 ease-in-out"
                                        :class="form.is_active ? 'translate-x-5' : 'translate-x-0'" />
                                </button>
                            </div>
                        </div>

                        <!-- imagen -->
                        <div
                            class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Imagen</h4>

                            <!-- preview con botón eliminar -->
                            <div v-if="imagePreview" class="mb-3 relative group">
                                <img :src="imagePreview"
                                    class="w-full h-36 object-cover rounded-lg border border-gray-200" alt="Preview">
                                <div
                                    class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                                    <button @click="removeImage"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-red-500 text-white text-xs font-medium hover:bg-red-600 transition-colors shadow">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Eliminar imagen
                                    </button>
                                </div>
                            </div>

                            <!-- zona drag & drop imagen -->
                            <div v-else @dragover="onImageDragOver" @dragleave="onImageDragLeave" @drop="onImageDrop"
                                @click="$refs.imageInput.click()" :class="[
                                    'mb-3 w-full h-36 rounded-lg border-2 border-dashed flex flex-col items-center justify-center gap-2 cursor-pointer transition-all',
                                    isImageDragging
                                        ? 'border-green-500 bg-green-50 dark:bg-green-500/10'
                                        : 'border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 hover:border-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900'
                                ]">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor"
                                    :class="['w-8 h-8 transition-colors', isImageDragging ? 'text-green-400' : 'text-gray-300']">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                                <div class="text-center">
                                    <p class="text-xs font-medium text-green-600 dark:text-green-400">
                                        {{ isImageDragging ? 'Suelta la imagen aquí' : 'Haz clic o arrastra una imagen'
                                        }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-0.5">JPG, PNG, WEBP · Máx. 1MB</p>
                                </div>
                            </div>

                            <!-- nombre del archivo seleccionado -->
                            <p v-if="imageFile" class="mt-1.5 text-xs text-gray-500 truncate flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor"
                                    class="w-3.5 h-3.5 text-green-500 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                {{ imageFile.name }}
                            </p>

                            <input ref="imageInput" type="file" accept="image/jpeg,image/png,image/jpg,image/webp"
                                @change="onImageChange" class="hidden">
                        </div>

                        <!-- PDF -->
                        <div
                            class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Documento PDF</h4>

                            <!-- PDF seleccionado -->
                            <div v-if="pdfName"
                                class="mb-3 flex items-center gap-3 p-3 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-100 dark:border-red-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-red-500 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-red-700 dark:text-red-400 truncate">{{ pdfName }}
                                    </p>
                                    <p class="text-xs text-red-500">PDF seleccionado</p>
                                </div>
                                <button @click="removePdf"
                                    class="p-1 rounded-full hover:bg-red-100 dark:hover:bg-red-500/20 text-red-500 transition-colors"
                                    title="Quitar PDF">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- drag and drop PDF -->
                            <div v-else @dragover="onPdfDragOver" @dragleave="onPdfDragLeave" @drop="onPdfDrop"
                                @click="$refs.pdfInput.click()" :class="[
                                    'mb-3 w-full h-20 rounded-lg border-2 border-dashed flex flex-col items-center justify-center gap-1.5 cursor-pointer transition-all',
                                    isPdfDragging
                                        ? 'border-red-400 bg-red-50 dark:bg-red-500/10'
                                        : 'border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 hover:border-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900'
                                ]">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor"
                                    :class="['w-5 h-5 transition-colors', isPdfDragging ? 'text-red-400' : 'text-gray-300']">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                <p class="text-xs font-medium transition-colors"
                                    :class="isPdfDragging ? 'text-red-500' : 'text-red-400'">
                                    {{ isPdfDragging ? 'Suelta el PDF aquí' : 'Haz clic o arrastra un PDF' }}
                                </p>
                                <p class="text-xs text-gray-400">Solo PDF · Máx. 10MB</p>
                            </div>

                            <input ref="pdfInput" type="file" accept="application/pdf" @change="onPdfChange"
                                class="hidden">
                        </div>

                        <!-- pantalla mas chica -->
                        <div class="flex flex-col gap-2 lg:hidden">
                            <button @click="submit" :disabled="isSubmitting || !canSubmit"
                                class="inline-flex h-10 w-full justify-center items-center gap-2 rounded-lg bg-green-600 px-4 text-sm font-medium text-white hover:bg-green-700 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                {{ isSubmitting ? 'Publicando...' : 'Publicar noticia' }}
                            </button>
                            <button @click="cancel" :disabled="isSubmitting"
                                class="inline-flex h-10 w-full justify-center items-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors disabled:opacity-50">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
            :buttonText="alertState.buttonText" :cancelText="alertState.cancelText" @confirm="handleConfirm"
            @cancel="handleCancel" @close="alertState.show = false" />
    </div>
</template>
<style scoped>
[contenteditable]:empty:before {
    content: attr(placeholder);
    color: #9ca3af;
    pointer-events: none;
}
</style>