<script setup>
import { ref, watch, computed } from 'vue'
import draggable from 'vuedraggable'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Alerta from '@/Components/Alerta.vue'
import { useAlert } from '@/composables/useAlert'
import PublicityEditModal from './PublicityEditModal.vue'
import PublicityCreateModal from './PublicityCreateModal.vue'
import PublicityListItem from './PublicityListItem.vue'

defineOptions({
    layout: AuthenticatedLayout
})

const props = defineProps({
    posts: {
        type: Array,
        default: () => []
    }
})

const { alertState, success, errorA, warning } = useAlert()

const list = ref([])
const original = ref([])
const dragging = ref(false)

const showCreateModal = ref(false)
const showEditModal = ref(false)
const postToEdit = ref(null)

const nextOrder = computed(() => {
    if (list.value.length === 0) return 0
    const maxOrder = Math.max(...list.value.map(p => p.order ?? 0))
    return maxOrder + 1
})

const init = () => {
    const sorted = [...props.posts].sort(
        (a, b) => (a.order ?? 0) - (b.order ?? 0)
    )
    list.value = sorted.map(p => ({ ...p }))
    original.value = sorted.map(p => ({ ...p }))
}

watch(() => props.posts, init, { immediate: true })

const startDrag = () => { dragging.value = true }
const endDrag = () => { dragging.value = false }

const saveOrder = () => {
    const payload = {
        posts: list.value.map((p, idx) => ({
            id: p.id,
            order: idx
        }))
    }

    router.post('/publicity/reorder', payload, {
        preserveScroll: true,
        onSuccess: () => {
            original.value = list.value.map(p => ({ ...p }))
            success('Orden actualizado correctamente')
        },
        onError: () => {
            errorA('Error actualizando el orden')
        }
    })
}

const cancelChanges = () => {
    list.value = original.value.map(p => ({ ...p }))
}

const openEditModal = (id) => {
    const post = list.value.find(p => p.id === id)
    if (!post) {
        warning('Publicidad no encontrada')
        return
    }
    postToEdit.value = { ...post }
    showEditModal.value = true
}

const deletePost = (id) => {
    warning('¿Estás seguro de eliminar esta publicidad?', {
        title: 'Eliminar publicidad',
        buttonText: 'Eliminar',
        cancelText: 'Cancelar',
        onConfirm: () => {
            router.delete(`/publicity/${id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    success('Publicidad eliminada correctamente')
                },
                onError: () => {
                    errorA('Error al eliminar la publicidad')
                }
            })
        }
    })
}

const onPostCreated = () => {
    success('La publicidad ha sido creada correctamente')
}

const onPostUpdated = () => {
    success('La publicidad ha sido actualizada correctamente')
}

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

        <Head title="Publicidad" />

        <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6 lg:p-8">
            <div class="space-y-5">
                <div class="flex flex-col gap-5 px-6 mb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Publicidad</h3>
                        <p class="text-sm text-gray-500">
                            Arrastra las filas para reordenar y pulsa "Guardar orden"
                        </p>
                    </div>
                </div>

                <div
                    class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6 mb-4">
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <button @click="cancelChanges"
                                class="inline-flex h-10 w-full sm:w-auto justify-center items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                Cancelar
                            </button>
                            <button @click="saveOrder"
                                class="inline-flex h-10 w-full sm:w-auto justify-center items-center gap-2 rounded-lg bg-zinc-900 text-white px-4 py-2.5 text-sm font-medium hover:bg-zinc-800 transition-colors">
                                Guardar orden
                            </button>
                        </div>

                        <p class="text-sm text-amber-600 dark:text-amber-500">
                            ⚠️ Las imágenes deben pesar máximo 1MB
                        </p>

                        <button @click="showCreateModal = true"
                            class="inline-flex h-10 w-full sm:w-auto justify-center items-center gap-2 rounded-lg bg-green-600 text-white px-4 py-2.5 text-sm font-medium hover:bg-green-700 transition-colors shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                class="w-5 h-5">
                                <path
                                    d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                            </svg>
                            Nueva publicidad
                        </button>
                    </div>

                    <div class="block px-5 pb-4">
                        <div class="space-y-4">
                            <draggable :list="list" ghost-class="ghost" item-key="id" @start="startDrag" @end="endDrag">
                                <template #item="{ element }">
                                    <PublicityListItem :key="element.id" :post="element" @edit="openEditModal"
                                        @delete="deletePost" />
                                </template>
                            </draggable>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <PublicityCreateModal :show="showCreateModal" :next-order="nextOrder" @close="showCreateModal = false"
            @created="onPostCreated" @warning="(msg) => warning(msg)" @error="(msg) => errorA(msg)"
            @info="(msg) => warning(msg)" />

        <PublicityEditModal :show="showEditModal" :post="postToEdit" @close="showEditModal = false; postToEdit = null"
            @updated="onPostUpdated" @warning="(msg) => warning(msg)" @error="(msg) => errorA(msg)" />

        <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
            :buttonText="alertState.buttonText" :cancelText="alertState.cancelText" @confirm="handleConfirm"
            @cancel="handleCancel" @close="alertState.show = false" />
    </div>
</template>

<style scoped>
.ghost {
    opacity: 0.5;
    background: #c8ebfb;
}
</style>