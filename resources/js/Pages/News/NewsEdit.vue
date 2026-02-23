<script setup>
import { ref, watch, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    newsItem: {
        type: Object,
        default: null
    }
})

const emit = defineEmits(['close', 'success', 'error', 'warning'])

const isSubmitting = ref(false)
const editEditorRef = ref(null)

const editForm = ref({
    title: '',
    content: '',
    extract: '',
    link: '',
    type: 'noticia',
    is_active: true,
})

const editImageFile = ref(null)
const editImagePreview = ref(null)
const editCurrentImage = ref(null)
const editPdfFile = ref(null)
const editPdfName = ref('')

// Poblar formulario cuando cambia el item
watch(() => props.newsItem, (item) => {
    if (!item) return
    editForm.value = {
        title: item.title,
        content: item.content,
        extract: item.extract || '',
        link: item.link || '',
        type: item.type,
        is_active: item.is_active,
    }
    editCurrentImage.value = item.image || null
    editImageFile.value = null
    editImagePreview.value = null
    editPdfFile.value = null
    editPdfName.value = ''

    nextTick(() => {
        if (editEditorRef.value) {
            editEditorRef.value.innerHTML = item.content || ''
        }
    })
})

const close = () => emit('close')

const onEditImageChange = (event) => {
    const file = event.target.files[0]
    if (!file) return
    if (file.size > 1 * 1024 * 1024) {
        emit('warning', 'La imagen no puede pesar más de 1MB')
        event.target.value = ''
        return
    }
    editImageFile.value = file
    editImagePreview.value = URL.createObjectURL(file)
}

const onEditPdfChange = (event) => {
    const file = event.target.files[0]
    if (!file) return
    if (file.size > 10 * 1024 * 1024) {
        emit('warning', 'El PDF no puede pesar más de 10MB')
        event.target.value = ''
        return
    }
    editPdfFile.value = file
    editPdfName.value = file.name
}

const execEditCmd = (command, value = null) => {
    editEditorRef.value?.focus()
    document.execCommand(command, false, value)
}

const onEditEditorInput = () => {
    editForm.value.content = editEditorRef.value?.innerHTML ?? ''
}

const submitEdit = () => {
    if (!editForm.value.title.trim() || !editForm.value.content.trim()) {
        emit('warning', 'El título y el contenido son requeridos')
        return
    }

    isSubmitting.value = true

    const formData = new FormData()
    formData.append('_method', 'PUT')
    formData.append('title', editForm.value.title)
    formData.append('content', editForm.value.content)
    formData.append('type', editForm.value.type)
    formData.append('is_active', editForm.value.is_active ? '1' : '0')
    if (editForm.value.extract.trim()) formData.append('extract', editForm.value.extract)
    if (editForm.value.link.trim()) formData.append('link', editForm.value.link)
    if (editImageFile.value) formData.append('image', editImageFile.value)
    if (editPdfFile.value) formData.append('pdf', editPdfFile.value)

    router.post(`/news/${props.newsItem.id}`, formData, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            emit('success', 'Noticia actualizada correctamente')
            close()
        },
        onError: (errors) => {
            emit('error', errors?.message || 'Error al actualizar la noticia')
        },
        onFinish: () => {
            isSubmitting.value = false
        }
    })
}
</script>

<template>
    <TransitionRoot as="template" :show="show">
        <Dialog class="relative z-50" @close="close">

            <!-- Overlay -->
            <TransitionChild as="template" enter="ease-in-out duration-300" enter-from="opacity-0"
                enter-to="opacity-100" leave="ease-in-out duration-300" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500/60 dark:bg-gray-900/70 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">

                        <TransitionChild as="template" enter="transform transition ease-in-out duration-300"
                            enter-from="translate-x-full" enter-to="translate-x-0"
                            leave="transform transition ease-in-out duration-300" leave-from="translate-x-0"
                            leave-to="translate-x-full">

                            <DialogPanel class="pointer-events-auto w-screen max-w-xl">
                                <div class="flex h-full flex-col bg-white dark:bg-gray-900 shadow-xl overflow-y-auto">

                                    <!-- Header -->
                                    <div
                                        class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 sticky top-0 z-10">
                                        <div>
                                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                                Editar noticia</h2>
                                            <p class="text-xs text-gray-500 mt-0.5">Modifica los campos que necesites
                                            </p>
                                        </div>
                                        <button @click="close"
                                            class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Contenido -->
                                    <div class="flex-1 px-6 py-5 space-y-5">

                                        <!-- Título -->
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Título <span class="text-red-500">*</span>
                                            </label>
                                            <input v-model="editForm.title" type="text"
                                                placeholder="Título de la noticia..."
                                                class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-800 dark:text-white/90 dark:bg-gray-800 dark:border-gray-700 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                maxlength="255">
                                            <p class="mt-1 text-xs text-gray-400 text-right">
                                                {{ editForm.title.length }}/255</p>
                                        </div>

                                        <!-- Tipo y Estado -->
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    Tipo <span class="text-red-500">*</span>
                                                </label>
                                                <div class="relative">
                                                    <select v-model="editForm.type"
                                                        class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 pr-8 text-sm appearance-none text-gray-800 dark:text-white/90 dark:bg-gray-800 dark:border-gray-700 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                        <option value="noticia">Noticia</option>
                                                        <option value="sesion">Sesión</option>
                                                    </select>
                                                    <span
                                                        class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                                                        <svg class="stroke-current" width="16" height="16"
                                                            viewBox="0 0 16 16" fill="none">
                                                            <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165"
                                                                stroke="" stroke-width="1.2" stroke-linecap="round"
                                                                stroke-linejoin="round" />
                                                        </svg>
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="flex flex-col justify-center">
                                                <label
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                                                <button type="button" @click="editForm.is_active = !editForm.is_active"
                                                    class="inline-flex items-center gap-2 w-fit">
                                                    <div class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out"
                                                        :class="editForm.is_active ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'">
                                                        <span
                                                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm transition duration-200 ease-in-out"
                                                            :class="editForm.is_active ? 'translate-x-5' : 'translate-x-0'" />
                                                    </div>
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                                        {{ editForm.is_active ? 'Activo' : 'Inactivo' }}
                                                    </span>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Editor de contenido -->
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Contenido <span class="text-red-500">*</span>
                                            </label>
                                            <div
                                                class="rounded-lg border border-gray-300 dark:border-gray-700 overflow-hidden">

                                                <!-- Toolbar -->
                                                <div
                                                    class="flex flex-wrap items-center gap-1 p-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                                                    <div class="flex items-center gap-0.5">
                                                        <button type="button" @click="execEditCmd('bold')"
                                                            title="Negrita"
                                                            class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                                fill="currentColor" class="w-4 h-4">
                                                                <path
                                                                    d="M6 4h8a4 4 0 010 8H6V4zm0 8h9a4 4 0 010 8H6v-8z" />
                                                            </svg>
                                                        </button>
                                                        <button type="button" @click="execEditCmd('italic')"
                                                            title="Cursiva"
                                                            class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                                fill="currentColor" class="w-4 h-4">
                                                                <path
                                                                    d="M10 4v3h2.21l-3.42 10H6v3h8v-3h-2.21l3.42-10H18V4z" />
                                                            </svg>
                                                        </button>
                                                        <button type="button" @click="execEditCmd('underline')"
                                                            title="Subrayado"
                                                            class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                                fill="currentColor" class="w-4 h-4">
                                                                <path
                                                                    d="M12 17c3.31 0 6-2.69 6-6V3h-2.5v8c0 1.93-1.57 3.5-3.5 3.5S8.5 12.93 8.5 11V3H6v8c0 3.31 2.69 6 6 6zm-7 2v2h14v-2H5z" />
                                                            </svg>
                                                        </button>
                                                        <button type="button" @click="execEditCmd('strikeThrough')"
                                                            title="Tachado"
                                                            class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                                fill="currentColor" class="w-4 h-4">
                                                                <path
                                                                    d="M10 19h4v-3h-4v3zM5 4v3h5v3h4V7h5V4H5zM3 14h18v-2H3v2z" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <div class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1" />
                                                    <div class="flex items-center gap-0.5">
                                                        <button type="button" @click="execEditCmd('justifyLeft')"
                                                            title="Izquierda"
                                                            class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                                fill="currentColor" class="w-4 h-4">
                                                                <path
                                                                    d="M15 15H3v2h12v-2zm0-8H3v2h12V7zM3 13h18v-2H3v2zm0 8h18v-2H3v2zM3 3v2h18V3H3z" />
                                                            </svg>
                                                        </button>
                                                        <button type="button" @click="execEditCmd('justifyCenter')"
                                                            title="Centrar"
                                                            class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                                fill="currentColor" class="w-4 h-4">
                                                                <path
                                                                    d="M7 15v2h10v-2H7zm-4 6h18v-2H3v2zm0-8h18v-2H3v2zm4-6v2h10V7H7zM3 3v2h18V3H3z" />
                                                            </svg>
                                                        </button>
                                                        <button type="button" @click="execEditCmd('justifyRight')"
                                                            title="Derecha"
                                                            class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                                fill="currentColor" class="w-4 h-4">
                                                                <path
                                                                    d="M3 21h18v-2H3v2zm6-4h12v-2H9v2zm-6-4h18v-2H3v2zm6-4h12V7H9v2zM3 3v2h18V3H3z" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <div class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1" />
                                                    <div class="flex items-center gap-0.5">
                                                        <button type="button" @click="execEditCmd('undo')"
                                                            title="Deshacer"
                                                            class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                                fill="currentColor" class="w-4 h-4">
                                                                <path
                                                                    d="M12.5 8c-2.65 0-5.05.99-6.9 2.6L2 7v9h9l-3.62-3.62c1.39-1.16 3.16-1.88 5.12-1.88 3.54 0 6.55 2.31 7.6 5.5l2.37-.78C21.08 11.03 17.15 8 12.5 8z" />
                                                            </svg>
                                                        </button>
                                                        <button type="button" @click="execEditCmd('redo')"
                                                            title="Rehacer"
                                                            class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                                fill="currentColor" class="w-4 h-4">
                                                                <path
                                                                    d="M18.4 10.6C16.55 8.99 14.15 8 11.5 8c-4.65 0-8.58 3.03-9.96 7.22L3.9 16c1.05-3.19 4.05-5.5 7.6-5.5 1.95 0 3.73.72 5.12 1.88L13 16h9V7l-3.6 3.6z" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Área editable -->
                                                <div ref="editEditorRef" contenteditable="true"
                                                    @input="onEditEditorInput" placeholder="Escribe el contenido..."
                                                    class="min-h-[200px] px-3 py-2.5 text-sm text-gray-800 dark:text-white/90 dark:bg-gray-800 focus:outline-none leading-relaxed prose prose-sm max-w-none" />
                                            </div>
                                        </div>

                                        <!-- Extracto -->
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Extracto <span class="text-gray-400 text-xs">(opcional)</span>
                                            </label>
                                            <textarea v-model="editForm.extract" rows="3"
                                                placeholder="Breve descripción para la vista previa..."
                                                class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-800 dark:text-white/90 dark:bg-gray-800 dark:border-gray-700 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none"
                                                maxlength="500" />
                                            <p class="mt-1 text-xs text-gray-400 text-right">
                                                {{ editForm.extract.length }}/500</p>
                                        </div>

                                        <!-- Link -->
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Link <span class="text-gray-400 text-xs">(opcional)</span>
                                            </label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                                    </svg>
                                                </span>
                                                <input v-model="editForm.link" type="url"
                                                    placeholder="https://ejemplo.com"
                                                    class="block w-full rounded-lg border border-gray-300 pl-9 pr-3 py-2.5 text-sm text-gray-800 dark:text-white/90 dark:bg-gray-800 dark:border-gray-700 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                            </div>
                                        </div>

                                        <!-- Imagen -->
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Imagen <span class="text-gray-400 text-xs">(opcional)</span>
                                            </label>
                                            <div v-if="editImagePreview || editCurrentImage" class="mb-3 relative">
                                                <img :src="editImagePreview || editCurrentImage"
                                                    class="w-full h-36 object-cover rounded-lg border border-gray-200 dark:border-gray-700"
                                                    alt="Preview">
                                                <button
                                                    @click="editImagePreview = null; editImageFile = null; editCurrentImage = null"
                                                    class="absolute top-2 right-2 p-1 rounded-full bg-red-500 text-white hover:bg-red-600 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                        class="w-3 h-3">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div v-else
                                                class="mb-3 w-full h-28 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700 flex flex-col items-center justify-center gap-2 bg-gray-50 dark:bg-gray-800/50">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor"
                                                    class="w-7 h-7 text-gray-300">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                                </svg>
                                                <span class="text-xs text-gray-400">Sin imagen</span>
                                            </div>
                                            <input type="file" accept="image/jpeg,image/png,image/jpg,image/webp"
                                                @change="onEditImageChange"
                                                class="block w-full text-xs text-gray-500 border border-gray-300 dark:border-gray-700 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-800 focus:outline-none">
                                            <p class="mt-1 text-xs text-gray-400">JPG, PNG, WEBP · Máx. 1MB</p>
                                        </div>

                                        <!-- PDF -->
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Documento PDF <span class="text-gray-400 text-xs">(opcional)</span>
                                            </label>
                                            <div v-if="editPdfName || newsItem?.pdf"
                                                class="mb-3 flex items-center gap-3 p-3 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-100 dark:border-red-500/20">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor"
                                                    class="w-7 h-7 text-red-500 shrink-0">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                </svg>
                                                <div class="flex-1 min-w-0">
                                                    <p
                                                        class="text-xs font-medium text-red-700 dark:text-red-400 truncate">
                                                        {{ editPdfName || 'PDF actual' }}
                                                    </p>
                                                    <p class="text-xs text-red-500">
                                                        {{ editPdfName ? 'Nuevo PDF seleccionado' : 'PDF existente' }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div v-else
                                                class="mb-3 w-full h-14 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700 flex items-center justify-center gap-2 bg-gray-50 dark:bg-gray-800/50">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor"
                                                    class="w-5 h-5 text-gray-300">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                </svg>
                                                <span class="text-xs text-gray-400">Sin documento adjunto</span>
                                            </div>
                                            <input type="file" accept="application/pdf" @change="onEditPdfChange"
                                                class="block w-full text-xs text-gray-500 border border-gray-300 dark:border-gray-700 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-800 focus:outline-none">
                                            <p class="mt-1 text-xs text-gray-400">Solo PDF · Máx. 10MB</p>
                                        </div>
                                    </div>

                                    <!-- Footer sticky -->
                                    <div
                                        class="sticky bottom-0 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex items-center justify-end gap-3">
                                        <button @click="close" :disabled="isSubmitting"
                                            class="rounded-lg border border-gray-300 dark:border-gray-700 py-2 px-4 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors disabled:opacity-50">
                                            Cancelar
                                        </button>
                                        <button @click="submitEdit" :disabled="isSubmitting"
                                            class="rounded-lg bg-blue-600 py-2 px-4 text-sm text-white hover:bg-blue-700 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center gap-2">
                                            <svg v-if="isSubmitting" class="animate-spin w-4 h-4"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4" />
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                                            </svg>
                                            {{ isSubmitting ? 'Guardando...' : 'Guardar cambios' }}
                                        </button>
                                    </div>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<style scoped>
[contenteditable]:empty:before {
    content: attr(placeholder);
    color: #9ca3af;
    pointer-events: none;
}
</style>
