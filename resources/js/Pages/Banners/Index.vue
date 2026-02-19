<script setup>
import { ref, watch, computed } from 'vue'
import draggable from 'vuedraggable'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Alerta from '@/Components/Alerta.vue'
import { useAlert } from '@/composables/useAlert'
import BannerEditModal from './BannerEditModal.vue'
import BannerCreateModal from './BannerCreateModal.vue'
import BannerListItem from './BannerListItem.vue'

defineOptions({
    layout: AuthenticatedLayout
})

const props = defineProps({
    banners: {
        type: Array,
        default: () => []
    }
})

const { alertState, success, errorA, warning } = useAlert()

// --- Estado de la lista y drag & drop ---
const list = ref([])
const original = ref([])
const dragging = ref(false)

// --- Estado de los modales ---
const showCreateModal = ref(false)
const showEditModal = ref(false)
const bannerToEdit = ref(null)

// --- Computed ---
const nextOrder = computed(() => {
    if (list.value.length === 0) return 0
    const maxOrder = Math.max(...list.value.map(b => b.order ?? 0))
    return maxOrder + 1
})

// --- Inicializar lista cuando cambian los banners ---
const init = () => {
    const sorted = [...props.banners].sort(
        (a, b) => (a.order ?? 0) - (b.order ?? 0)
    )
    list.value = sorted.map(b => ({ ...b }))
    original.value = sorted.map(b => ({ ...b }))
}

watch(() => props.banners, init, { immediate: true })

// --- Drag & Drop ---
const startDrag = () => { dragging.value = true }
const endDrag = () => { dragging.value = false }

const saveOrder = () => {
    const payload = {
        banners: list.value.map((b, idx) => ({
            id: b.id,
            order: idx
        }))
    }

    router.post('/banners/reorder', payload, {
        preserveScroll: true,
        onSuccess: () => {
            original.value = list.value.map(b => ({ ...b }))
            success('Orden actualizado correctamente')
        },
        onError: () => {
            errorA('Error actualizando el orden')
        }
    })
}

const cancelChanges = () => {
    list.value = original.value.map(b => ({ ...b }))
}

// --- Acciones sobre banners ---
const openEditModal = (id) => {
    const banner = list.value.find(b => b.id === id)
    if (!banner) {
        warning('Banner no encontrado')
        return
    }
    bannerToEdit.value = { ...banner }
    showEditModal.value = true
}

const deleteBanner = (id) => {
    warning('Estas seguro de eliminar este banner?', {
        title: 'Eliminar banner',
        buttonText: 'Eliminar',
        cancelText: 'Cancelar',
        onConfirm: () => {
            router.delete(`/banners/${id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    success('Banner eliminado correctamente')
                },
                onError: () => {
                    errorA('Error al eliminar el banner')
                }
            })
        }
    })
}

// --- Callbacks de modales ---
const onBannerCreated = () => {
    success('El banner ha sido creado correctamente')
}

const onBannerUpdated = () => {
    success('El banner ha sido actualizado correctamente')
}

// --- Alerta ---
const handleConfirm = () => {
    alertState.value.onConfirm?.()
    alertState.value.show = false
}

const handleCancel = () => {
    alertState.value.onCancel?.()
    alertState.value.show = false
}
</script>

<template>
    <div>
        <Head title="Banners" />

        <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6 lg:ml-[290px]">
            <div class="space-y-5">
                <div class="flex flex-col gap-5 px-6 mb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Banners</h3>
                        <p class="text-sm text-gray-500">
                            Arrastra las filas para reordenar y pulsa "Guardar orden"
                        </p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6 mb-4">
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <button
                                @click="cancelChanges"
                                class="inline-flex h-10 w-full sm:w-auto justify-center items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                @click="saveOrder"
                                class="inline-flex h-10 w-full sm:w-auto justify-center items-center gap-2 rounded-lg bg-zinc-900 text-white px-4 py-2.5 text-sm font-medium hover:bg-zinc-800 transition-colors"
                            >
                                Guardar orden
                            </button>
                        </div>

                        <p class="text-sm text-amber-600 dark:text-amber-500">
                            ⚠️ Las imágenes deben pesar máximo 1MB
                        </p>

                        <button
                            @click="showCreateModal = true"
                            class="inline-flex h-10 w-full sm:w-auto justify-center items-center gap-2 rounded-lg bg-green-600 text-white px-4 py-2.5 text-sm font-medium hover:bg-green-700 transition-colors shadow-sm"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                            </svg>
                            Nuevo banner
                        </button>
                    </div>

                    <div class="block px-5 pb-4">
                        <div class="space-y-4">
                            <draggable
                                :list="list"
                                ghost-class="ghost"
                                item-key="id"
                                @start="startDrag"
                                @end="endDrag"
                            >
                                <template #item="{ element }">
                                    <BannerListItem
                                        :key="element.id"
                                        :banner="element"
                                        @edit="openEditModal"
                                        @delete="deleteBanner"
                                    />
                                </template>
                            </draggable>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modales -->
        <BannerCreateModal
            :show="showCreateModal"
            :next-order="nextOrder"
            @close="showCreateModal = false"
            @created="onBannerCreated"
            @warning="(msg) => warning(msg)"
            @error="(msg) => errorA(msg)"
            @info="(msg) => warning(msg)"
        />

        <BannerEditModal
            :show="showEditModal"
            :banner="bannerToEdit"
            @close="showEditModal = false; bannerToEdit = null"
            @updated="onBannerUpdated"
            @warning="(msg) => warning(msg)"
            @error="(msg) => errorA(msg)"
        />

        <Alerta
            :show="alertState.show"
            :message="alertState.message"
            :title="alertState.title"
            :type="alertState.type"
            :buttonText="alertState.buttonText"
            :cancelText="alertState.cancelText"
            @confirm="handleConfirm"
            @cancel="handleCancel"
            @close="alertState.show = false"
        />
    </div>
</template>

<style scoped>
.ghost {
    opacity: 0.5;
    background: #c8ebfb;
}
</style>
