<script setup>
import { ref, watch } from 'vue'
import draggable from 'vuedraggable'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
    banners: {
        type: Array,
        default: () => []
    }
})

const enabled = ref(true)
const list = ref([])
const original = ref([])
const dragging = ref(false)

const init = () => {
    list.value = [...props.banners]
        .sort((a, b) => (a.order ?? 0) - (b.order ?? 0))
        .map(b => ({ ...b }))
    original.value = list.value.map(b => ({ ...b }))
}

watch(() => props.banners, init, { immediate: true })


const checkMove = (e) => {
    return true
}

const startDrag = () => { dragging.value = true }
const endDrag = () => { dragging.value = false }


const saveOrder = async () => {
    try {
        const payload = {
            banners: list.value.map((b, idx) => ({
                id: b.id,
                order: idx
            }))
        }

        await axios.post('/banners/reorder', payload)

        original.value = list.value.map(b => ({ ...b }))

        alert('Orden actualizado correctamente')

    } catch (error) {
        console.error(error)
        alert('Error actualizando el orden')
    }
}

const cancelChanges = () => {
    list.value = original.value.map(b => ({ ...b }))
}

const deleteBanner = (id) => {
    if (!confirm('¿Eliminar banner?')) return

    router.delete(`/banners/${id}`, {
        preserveScroll: true,
        onSuccess: () => {
            list.value = list.value.filter(b => b.id !== id)
            original.value = original.value.filter(b => b.id !== id)
        }
    })
}
</script>

<template>
    <div class="p-6 bg-zinc-50 rounded-lg max-w-3xl mx-auto">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-xl font-semibold">Ordenar Banners</h2>
                <p class="text-sm text-zinc-600">Arrastra y pulsa Guardar para aplicar el nuevo orden</p>
            </div>

            <div class="flex items-center gap-2">
                <button @click="cancelChanges"
                    class="bg-white border border-zinc-200 text-zinc-700 text-sm px-3 py-2 rounded hover:bg-zinc-50">
                    Cancelar
                </button>
                <button @click="saveOrder" class="bg-zinc-900 text-white text-sm px-3 py-2 rounded hover:bg-zinc-800">
                    Guardar orden
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-zinc-200 overflow-hidden">
            <draggable :list="list" :disabled="!enabled" class="flex flex-col gap-3 p-4" ghost-class="ghost"
                :move="checkMove" item-key="id" :swap="true" :swap-threshold="0.5" @start="startDrag" @end="endDrag">
                <template #item="{ element, index }">
                    <div
                        class="list-group-item banner-card w-full bg-white border rounded-lg shadow-sm overflow-hidden cursor-move">
                        <div class="flex gap-3 items-center p-3">
                            <img v-if="element.image" :src="element.image" class="w-28 h-20 object-cover rounded" />
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start">
                                    <div class="truncate">
                                        <div class="text-sm font-medium">Nombre: {{ element.name }}</div>
                                        <a v-if="element.link" :href="element.link" target="_blank"
                                            class="text-sm font-medium truncate text-blue-600 hover:text-blue-800 hover:underline cursor-pointer block">
                                            {{ element.link }}
                                        </a>

                                        <span v-else
                                            class="text-sm font-medium truncate text-gray-400 cursor-default block">
                                            — sin link —
                                        </span>
                                        <div class="text-xs text-zinc-500">ID: {{ element.id }}</div>
                                    </div>
                                    <div class="text-sm text-zinc-600 ml-4">Pos: {{ index }}</div>
                                </div>
                            </div>
                            <button @click=""
                                class="bg-yellow-600 text-white text-xs px-2 py-1 rounded hover:bg-yellow-700">
                                Editar
                            </button>
                            <button @click="deleteBanner(element.id)"
                                class="bg-red-600 text-white text-xs px-2 py-1 rounded hover:bg-red-700">
                                Eliminar
                            </button>
                        </div>
                    </div>
                </template>
            </draggable>
        </div>
    </div>
</template>

<style scoped>
.ghost {
    opacity: 0.5;
    background: #c8ebfb;
}

.list-group {
    padding: 0;
}

.list-group-item {
    list-style: none;
}
</style>