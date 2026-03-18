<script setup>
import { onMounted } from 'vue'
import Dropzone from '@/Components/Dropzone.vue'
import { useFileUpload } from '@/composables/useImageDropped'

const props = defineProps({
    initialPlatinum: { type: Array, default: () => [] },
    initialGolden: { type: Array, default: () => [] },
    initialSilver: { type: Array, default: () => [] },
    errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['error'])

const makeUpload = () =>
    useFileUpload({
        multiple: true,
        maxFiles: 20,
        maxSizeMB: 2,
        acceptedTypes: ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'],
        onError: (msg) => emit('error', msg),
    })

const platinum = makeUpload()
const golden = makeUpload()
const silver = makeUpload()

onMounted(() => {
    platinum.initExisting(props.initialPlatinum)
    golden.initExisting(props.initialGolden)
    silver.initExisting(props.initialSilver)
})

/**
 * el componente padre (create, edit) llama sponsorsRef.value.getData() al hacer submit
 * para obtener los archivos nuevos y los IDs a eliminar
 */
const getData = () => ({
    platinum_sponsors: platinum.files.value,
    golden_sponsors: golden.files.value,
    silver_sponsors: silver.files.value,
    platinum_delete: platinum.deletedExistingIds?.value ?? [],
    golden_delete: golden.deletedExistingIds?.value ?? [],
    silver_delete: silver.deletedExistingIds?.value ?? [],
})

defineExpose({ getData })
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="grid lg:grid-cols-4 gap-4 p-6">

            <div>
                <span class="text-sm text-gray-700 dark:text-gray-400">Patrocinadores</span>
            </div>

            <div class="col-span-3 space-y-6">

                <!-- PLATINO -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Platino
                    </label>
                    <Dropzone multiple :all-previews="platinum.allPreviews.value"
                        :total-count="platinum.totalCount.value" :is-dragging="platinum.isDragging.value"
                        :max-files="20" :columns="'6'" :object-fit="'contain'" @change="platinum.handleChange" @drop="platinum.handleDrop"
                        @drag-enter="platinum.handleDragEnter" @drag-leave="platinum.handleDragLeave"
                        @remove-item="platinum.removeItem" @remove="platinum.reset" />
                    <span v-if="errors['platinum_sponsors']" class="text-red-500 text-xs mt-1 block">
                        {{ errors['platinum_sponsors'] }}
                    </span>
                </div>

                <!-- ORO -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Oro
                    </label>
                    <Dropzone multiple :all-previews="golden.allPreviews.value" :total-count="golden.totalCount.value"
                        :is-dragging="golden.isDragging.value" :max-files="20" :columns="'6'" :object-fit="'contain'"
                        @change="golden.handleChange" @drop="golden.handleDrop" @drag-enter="golden.handleDragEnter"
                        @drag-leave="golden.handleDragLeave" @remove-item="golden.removeItem" @remove="golden.reset" />
                    <span v-if="errors['golden_sponsors']" class="text-red-500 text-xs mt-1 block">
                        {{ errors['golden_sponsors'] }}
                    </span>
                </div>

                <!-- PLATA -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Plata
                    </label>
                    <Dropzone multiple :all-previews="silver.allPreviews.value" :total-count="silver.totalCount.value"
                        :is-dragging="silver.isDragging.value" :max-files="20" :columns="'6'" :object-fit="'contain'"
                        @change="silver.handleChange" @drop="silver.handleDrop" @drag-enter="silver.handleDragEnter"
                        @drag-leave="silver.handleDragLeave" @remove-item="silver.removeItem" @remove="silver.reset" />
                    <span v-if="errors['silver_sponsors']" class="text-red-500 text-xs mt-1 block">
                        {{ errors['silver_sponsors'] }}
                    </span>
                </div>

            </div>
        </div>
    </div>
</template>