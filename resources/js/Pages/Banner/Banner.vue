<script setup>
import { ref, watch } from 'vue'
import draggable from 'vuedraggable'
import axios from 'axios'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({
    layout: AuthenticatedLayout
})

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
    <div>

        <Head title="Banners" />

        <div class="p-4 border-t border-gray-100 dark:border-gray-800 sm:p-6 lg:ml-[290px]">
            <div class="space-y-5">
                <div
                    class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex flex-col gap-5 px-6 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Banners</h3>
                            <p class="text-sm text-gray-500">Arrastra las filas para reordenar y pulsa "Guardar orden"
                            </p>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <div>
                                <button @click="cancelChanges"
                                    class="inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Cancelar
                                </button>
                                <button @click="saveOrder"
                                    class="inline-flex h-10 items-center gap-2 rounded-lg bg-zinc-900 text-white px-4 py-2.5 text-sm font-medium hover:bg-zinc-800 ml-2">
                                    Guardar orden
                                </button>
                                <button @click="$inertia.visit('/banners/create')"
                                    class="inline-flex h-10 items-center gap-2 rounded-lg bg-green-600 text-white px-4 py-2.5 text-sm font-medium hover:bg-green-700 ml-2">
                                    Nuevo
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="lg:hidden px-5 pb-4">
                        <div class="space-y-4">
                            <draggable :list="list" :disabled="!enabled" ghost-class="ghost" :move="checkMove" item-key="id">
                                <template #item="{ element, index }">
                                    <div :key="element.id" class="bg-white dark:bg-white/[0.03] border border-gray-200 dark:border-gray-800 rounded-2xl p-4 flex items-center gap-4">
                                        <div class="w-28 h-20 flex-shrink-0 overflow-hidden rounded">
                                            <img v-if="element.image" :src="element.image" class="w-full h-full object-cover" />
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="text-base font-medium leading-6 text-gray-800 dark:text-white/90 truncate">{{ element.name }}</div>
                                            <div v-if="element.link" class="text-sm text-blue-600 truncate max-w-full"><a :href="element.link" target="_blank" class="hover:underline">{{ element.link }}</a></div>
                                            <div v-else class="text-sm text-gray-400">— sin link —</div>
                                        </div>

                                        <div class="flex-shrink-0 flex flex-col gap-2">
                                            <button @click="editBanner(element.id)" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Editar</button>
                                            <button @click="deleteBanner(element.id)" class="inline-flex items-center gap-2 rounded-lg bg-red-600 text-white px-3 py-2 text-sm font-medium hover:bg-red-700">Eliminar</button>
                                        </div>
                                    </div>
                                </template>
                            </draggable>
                        </div>
                    </div>

                    <div class="hidden lg:block max-w-full overflow-x-auto custom-scrollbar px-5 pb-4">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-y border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                                    <th class="px-6 py-3 text-center">
                                        <p class="font-medium text-gray-500 text-xs">Preview</p>
                                    </th>
                                    <th class="px-6 py-3 text-center">
                                        <p class="font-medium text-gray-500 text-xs">Nombre</p>
                                    </th>
                                    <th class="px-6 py-3 text-center">
                                        <p class="font-medium text-gray-500 text-xs">Link</p>
                                    </th>
                                    <!-- <th class="px-6 py-3 text-center">
                                        <p class="font-medium text-gray-500 text-xs">Pos</p>
                                    </th> -->
                                    <th class="px-6 py-3 text-center">
                                        <p class="font-medium text-gray-500 text-xs">Acciones</p>
                                    </th>
                                </tr>
                            </thead>

                            <draggable tag="tbody" :list="list" :disabled="!enabled" ghost-class="ghost"
                                :move="checkMove" item-key="id" class="divide-y divide-gray-100 dark:divide-gray-800">
                                <template #item="{ element, index }">
                                    <tr class="border-t border-gray-100 dark:border-gray-800">
                                        <td class="px-6 py-3.5 text-center">
                                            <div class="w-28 h-20 overflow-hidden rounded mx-auto">
                                                <img v-if="element.image" :src="element.image"
                                                    class="w-full h-full object-cover" />
                                            </div>
                                        </td>

                                        <td class="px-6 py-3.5 align-middle">
                                            <div>
                                                <div
                                                    class="text-base font-medium leading-6 text-gray-800 dark:text-white/90 truncate text-center">
                                                    {{ element.name }}</div>
                                                <!-- <div class="text-xs text-gray-500">ID: {{ element.id }}</div> -->
                                            </div>
                                        </td>

                                        <td class="px-6 py-3.5 align-middle text-center">
                                            <div v-if="element.link"
                                                class="text-sm text-gray-700 truncate max-w-sm mx-auto"><a
                                                    :href="element.link" target="_blank"
                                                    class="text-blue-600 hover:underline">{{ element.link }}</a></div>
                                            <div v-else class="text-sm text-gray-400">— sin link —</div>
                                        </td>

                                        <!-- <td class="px-6 py-3.5 align-top">
                                            <div class="text-sm text-gray-700">{{ index }}</div>
                                        </td> -->

                                        <td class="px-6 py-3.5 align-middle text-center">
                                            <div class="flex items-center gap-2 justify-center">
                                                <button @click="editBanner(element.id)"
                                                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Editar</button>
                                                <button @click="deleteBanner(element.id)"
                                                    class="inline-flex items-center gap-2 rounded-lg bg-red-600 text-white px-3 py-2 text-sm font-medium hover:bg-red-700">Eliminar</button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </draggable>
                        </table>
                    </div>
                </div>
            </div>
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