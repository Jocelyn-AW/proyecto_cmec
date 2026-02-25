<script setup>
/**
 * Componente Dropzone reutilizable.
 * Soporta modo simple (1 imagen) y modo multiple (grilla de previews).
 *
 * Modo simple:
 *   <Dropzone
 *     :preview="preview"
 *     :is-dragging="isDragging"
 *     @change="handleChange"
 *     @drop="handleDrop"
 *     @drag-enter="handleDragEnter"
 *     @drag-leave="handleDragLeave"
 *     @remove="reset"
 *   />
 *
 * Modo multiple:
 *   <Dropzone
 *     multiple
 *     :previews="previews"
 *     :is-dragging="isDragging"
 *     :max-files="5"
 *     @change="handleChange"
 *     @drop="handleDrop"
 *     @drag-enter="handleDragEnter"
 *     @drag-leave="handleDragLeave"
 *     @remove-at="removeAt"
 *     @remove="reset"
 *   />
 */
import { computed, ref } from 'vue'

/**
 * Determina si un preview es una imagen (string base64/url) o un archivo (objeto con metadata).
 */
const isImagePreview = (preview) => {
    return typeof preview === 'string'
}
// const isImagePreview = () => {
//     return props.isImage;
// }

/**
 * Formatea bytes a una cadena legible (KB, MB).
 */
const formatSize = (bytes) => {
    if (bytes < 1024) return `${bytes} B`
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

/**
 * Retorna la extension del archivo en mayusculas.
 */
const getExtension = (filename) => {
    const parts = filename.split('.')
    return parts.length > 1 ? parts.pop().toUpperCase() : '???'
}

const props = defineProps({
    /** Modo multiple */
    multiple: {
        type: Boolean,
        default: false
    },
    /** URL o base64 de la imagen (modo simple) */
    preview: {
        type: [String, Object],
        default: null
    },
    /** Array de URLs o base64 de previews (modo multiple) */
    previews: {
        type: Array,
        default: () => []
    },
    /** Maximo de archivos permitidos (modo multiple, solo visual) */
    maxFiles: {
        type: Number,
        default: 10
    },
    /** Estado externo de drag (del composable) */
    isDragging: {
        type: Boolean,
        default: false
    },
    /** Tipos de archivo aceptados para el input nativo */
    accept: {
        type: String,
        default: 'image/jpeg,image/png,image/jpg,image/webp'
    },
    /** Texto de ayuda debajo del icono */
    hint: {
        type: String,
        default: 'JPG, PNG, WEBP (max. 1MB)'
    },
    /** Texto principal del dropzone */
    label: {
        type: String,
        default: 'Arrastra tu imagen aqui o haz clic para seleccionar'
    },
    /** Deshabilitar el dropzone */
    disabled: {
        type: Boolean,
        default: false
    },
    /** Texto alt para la imagen de preview */
    previewAlt: {
        type: String,
        default: 'Vista previa'
    }
})

const emit = defineEmits(['change', 'drop', 'drag-enter', 'drag-leave', 'remove', 'remove-at'])

const fileInput = ref(null)

const hasContent = computed(() => {
    if (props.multiple) return props.previews.length > 0
    return !!props.preview
})

const canAddMore = computed(() => {
    if (!props.multiple) return !props.preview
    return props.previews.length < props.maxFiles
})

const openFilePicker = () => {
    if (!props.disabled) {
        fileInput.value?.click()
    }
}

const onInputChange = (event) => {
    emit('change', event)
}

const onDrop = (event) => {
    if (props.disabled) return
    event.preventDefault()
    emit('drop', event)
}

const onDragOver = (event) => {
    event.preventDefault()
}

const onDragEnter = () => {
    if (!props.disabled) emit('drag-enter')
}

const onDragLeave = () => {
    emit('drag-leave')
}

const removePreview = () => {
    emit('remove')
}

const removeAtIndex = (index) => {
    emit('remove-at', index)
}
</script>

<template>
    <div class="w-full">
        <!-- Input oculto -->
        <input
            ref="fileInput"
            type="file"
            :accept="accept"
            :multiple="multiple"
            class="hidden"
            :disabled="disabled"
            @change="onInputChange"
        >

        <!-- ============================================ -->
        <!-- MODO SIMPLE (una sola imagen)                -->
        <!-- ============================================ -->
        <template v-if="!multiple">
            <!-- Estado con preview (imagen) -->
            <div v-if="preview && isImagePreview(preview)" class="relative group">
                <div class="w-full h-48 overflow-hidden rounded-lg border border-gray-300">
                    <img
                        :src="preview"
                        :alt="previewAlt"
                        class="w-full h-full object-cover"
                    >
                </div>

                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center gap-3">
                    <button
                        type="button"
                        :disabled="disabled"
                        class="rounded-md bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors disabled:opacity-50"
                        @click="openFilePicker"
                    >
                        Cambiar
                    </button>

                    <button
                        type="button"
                        :disabled="disabled"
                        class="rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-red-700 transition-colors disabled:opacity-50"
                        @click="removePreview"
                    >
                        Quitar
                    </button>
                </div>
            </div>

            <!-- Estado con preview (archivo no-imagen) -->
            <div v-else-if="preview && !isImagePreview(preview)" class="relative group">
                <div class="flex items-center gap-3 rounded-lg border border-gray-300 bg-gray-50 p-4">
                    <!-- Icono de archivo -->
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="truncate text-sm font-medium text-gray-700">{{ preview.name }}</p>
                        <p class="text-xs text-gray-400">
                            {{ getExtension(preview.name) }} &middot; {{ formatSize(preview.size) }}
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <button
                            type="button"
                            :disabled="disabled"
                            class="rounded-md bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm border border-gray-300 hover:bg-gray-50 transition-colors disabled:opacity-50"
                            @click="openFilePicker"
                        >
                            Cambiar
                        </button>

                        <button
                            type="button"
                            :disabled="disabled"
                            class="rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-red-700 transition-colors disabled:opacity-50"
                            @click="removePreview"
                        >
                            Quitar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Estado sin preview (dropzone) -->
            <div
                v-else
                role="button"
                tabindex="0"
                :class="[
                    'w-full rounded-lg border-2 border-dashed p-6 text-center cursor-pointer transition-colors',
                    isDragging
                        ? 'border-blue-500 bg-blue-50'
                        : 'border-gray-300 bg-gray-50 hover:border-gray-400 hover:bg-gray-100',
                    disabled ? 'opacity-50 cursor-not-allowed' : ''
                ]"
                @click="openFilePicker"
                @drop="onDrop"
                @dragover="onDragOver"
                @dragenter="onDragEnter"
                @dragleave="onDragLeave"
                @keydown.enter="openFilePicker"
                @keydown.space.prevent="openFilePicker"
            >
                <div class="flex flex-col items-center gap-2">
                    <svg
                        class="h-10 w-10 text-gray-400"
                        :class="{ 'text-blue-500': isDragging }"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"
                        />
                    </svg>

                    <p class="text-sm text-gray-600" :class="{ 'text-blue-600': isDragging }">
                        {{ isDragging ? 'Suelta el archivo aqui' : label }}
                    </p>

                    <p class="text-xs text-gray-400">
                        {{ hint }}
                    </p>
                </div>
            </div>
        </template>

        <!-- ============================================ -->
        <!-- MODO MULTIPLE (grilla de previews)           -->
        <!-- ============================================ -->
        <template v-else>
            <!-- Grilla de thumbnails + boton agregar -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5">

                <!-- Thumbnails existentes -->
                <div
                    v-for="(src, index) in previews"
                    :key="index"
                    class="group relative aspect-square overflow-hidden rounded-lg border border-gray-300"
                >
                    <!-- Preview imagen -->
                    <img
                        v-if="isImagePreview(src)"
                        :src="src"
                        :alt="`${previewAlt} ${index + 1}`"
                        class="h-full w-full object-cover"
                    >

                    <!-- Preview archivo no-imagen -->
                    <div v-else class="flex h-full w-full flex-col items-center justify-center bg-gray-100 p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <span class="mt-1 text-xs font-medium text-gray-500">{{ getExtension(src.name) }}</span>
                        <span class="text-[10px] text-gray-400 truncate max-w-full px-1">{{ src.name }}</span>
                    </div>

                    <!-- Boton quitar individual -->
                    <button
                        type="button"
                        :disabled="disabled"
                        class="absolute top-1.5 right-1.5 rounded-full bg-black/60 p-1 text-white opacity-0 group-hover:opacity-100 transition-opacity hover:bg-black/80 disabled:opacity-50"
                        :title="`Quitar archivo ${index + 1}`"
                        @click="removeAtIndex(index)"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span class="sr-only">Quitar archivo {{ index + 1 }}</span>
                    </button>

                    <!-- Numero de orden -->
                    <span class="absolute bottom-1.5 left-1.5 rounded bg-black/60 px-1.5 py-0.5 text-xs font-medium text-white">
                        {{ index + 1 }}
                    </span>
                </div>

                <!-- Boton agregar mas (si hay slots disponibles) -->
                <div
                    v-if="canAddMore"
                    role="button"
                    tabindex="0"
                    :class="[
                        'flex aspect-square flex-col items-center justify-center rounded-lg border-2 border-dashed cursor-pointer transition-colors',
                        isDragging
                            ? 'border-blue-500 bg-blue-50'
                            : 'border-gray-300 bg-gray-50 hover:border-gray-400 hover:bg-gray-100',
                        disabled ? 'opacity-50 cursor-not-allowed' : ''
                    ]"
                    @click="openFilePicker"
                    @drop="onDrop"
                    @dragover="onDragOver"
                    @dragenter="onDragEnter"
                    @dragleave="onDragLeave"
                    @keydown.enter="openFilePicker"
                    @keydown.space.prevent="openFilePicker"
                >
                    <svg
                        class="h-8 w-8 text-gray-400"
                        :class="{ 'text-blue-500': isDragging }"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>

                    <p class="mt-1 text-xs text-gray-500">
                        {{ isDragging ? 'Suelta aqui' : 'Agregar' }}
                    </p>
                </div>
            </div>

            <!-- Contador y hint -->
            <div class="mt-2 flex items-center justify-between">
                <p class="text-xs text-gray-400">
                    {{ hint }}
                </p>
                <p class="text-xs text-gray-400">
                    {{ previews.length }} / {{ maxFiles }}
                </p>
            </div>

            <!-- Dropzone principal cuando no hay nada -->
            <!-- <div
                v-if="previews.length === 0"
                role="button"
                tabindex="0"
                :class="[
                    'w-full rounded-lg border-2 border-dashed p-8 text-center cursor-pointer transition-colors mt-0',
                    isDragging
                        ? 'border-blue-500 bg-blue-50'
                        : 'border-gray-300 bg-gray-50 hover:border-gray-400 hover:bg-gray-100',
                    disabled ? 'opacity-50 cursor-not-allowed' : ''
                ]"
                @click="openFilePicker"
                @drop="onDrop"
                @dragover="onDragOver"
                @dragenter="onDragEnter"
                @dragleave="onDragLeave"
                @keydown.enter="openFilePicker"
                @keydown.space.prevent="openFilePicker"
            >
                <div class="flex flex-col items-center gap-2">
                    <svg
                        class="h-10 w-10 text-gray-400"
                        :class="{ 'text-blue-500': isDragging }"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"
                        />
                    </svg>

                    <p class="text-sm text-gray-600" :class="{ 'text-blue-600': isDragging }">
                        {{ isDragging ? 'Suelta los archivos aqui' : label }}
                    </p>

                    <p class="text-xs text-gray-400">
                        {{ hint }}
                    </p>
                </div>
            </div> -->

            <!-- Boton limpiar todo (si hay imagenes) -->
            <div v-if="previews.length > 1" class="mt-2 flex justify-end">
                <button
                    type="button"
                    :disabled="disabled"
                    class="text-xs text-red-500 hover:text-red-700 transition-colors disabled:opacity-50"
                    @click="removePreview"
                >
                    Quitar todas
                </button>
            </div>
        </template>
    </div>
</template>
