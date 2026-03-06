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
    banners: { type: Array, default: () => [] },
    eventTypes: { type: Array, default: () => ['home'] },
    filters: { type: Object, default: () => ({}) },
})

const { alertState, success, errorA, warning } = useAlert()

// Lista drag & drop
const list = ref([])
const original = ref([])
const dragging = ref(false)

// Modales
const showCreateModal = ref(false)
const showEditModal = ref(false)
const bannerToEdit = ref(null)

// Filtros locales (sincronizados con props.filters)
const selectedType = ref(props.filters.event_type ?? 'home')
const selectedStatus = ref(props.filters.status ?? '')
const searchText = ref(props.filters.search ?? '')
const dateFrom = ref(props.filters.date_from ?? '')
const dateTo = ref(props.filters.date_to ?? '')

// Labels legibles por event_type
const typeLabels = {
    home: 'Home',
    academic_session: 'Sesiones Académicas',
    webinar: 'Webinars',
    course: 'Cursos',
}
const labelFor = (type) => typeLabels[type] ?? type

// Lista ordenada
const nextOrder = computed(() => {
    if (!list.value.length) return 0
    return Math.max(...list.value.map(b => b.order ?? 0)) + 1
})

const init = () => {
    const sorted = [...props.banners].sort((a, b) => (a.order ?? 0) - (b.order ?? 0))
    list.value = sorted.map(b => ({ ...b }))
    original.value = sorted.map(b => ({ ...b }))
}

watch(() => props.banners, init, { immediate: true })

// Aplicar filtros (peticion al servidor)
const applyFilters = () => {
    router.get(route('banners.index'), {
        event_type: selectedType.value,
        status: selectedStatus.value,
        search: searchText.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
    }, { preserveScroll: true, replace: true })
}

// Cuando cambia el tipo, limpia fechas y re-aplica
const changeType = (type) => {
    selectedType.value = type
    dateFrom.value = ''
    dateTo.value = ''
    applyFilters()
}

// Debounce simple para el search
let searchTimer = null
const onSearchInput = () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(applyFilters, 400)
}

// Drag & drop
const startDrag = () => { dragging.value = true }
const endDrag = () => { dragging.value = false }

const saveOrder = () => {
    router.post('/banners/reorder', {
        event_type: selectedType.value,
        banners: list.value.map((b, idx) => ({ id: b.id, order: idx })),
    }, {
        preserveScroll: true,
        onSuccess: () => {
            original.value = list.value.map(b => ({ ...b }))
            success('Orden actualizado correctamente')
        },
        onError: () => errorA('Error actualizando el orden'),
    })
}

const cancelChanges = () => {
    list.value = original.value.map(b => ({ ...b }))
}

// Acciones
const openEditModal = (id) => {
    const banner = list.value.find(b => b.id === id)
    if (!banner) { warning('Banner no encontrado'); return }
    bannerToEdit.value = { ...banner }
    showEditModal.value = true
}

const deleteBanner = (id) => {
    warning('¿Estás seguro de eliminar este banner?', {
        title: 'Eliminar banner',
        buttonText: 'Eliminar',
        cancelText: 'Cancelar',
        onConfirm: () => {
            router.delete(`/banners/${id}`, {
                preserveScroll: true,
                onSuccess: () => success('Banner eliminado correctamente'),
                onError: () => errorA('Error al eliminar el banner'),
            })
        },
    })
}

const changeStatus = (id) => {
    router.patch(route('banners.statusChange', id), {}, {
        preserveScroll: true,
        onError: () => errorA('Error al cambiar el estado del banner'),
    })
}

const handleConfirm = () => { alertState.value.onConfirm?.(); alertState.value.show = false }
const handleCancel = () => { alertState.value.onCancel?.(); alertState.value.show = false }
</script>

<template>
    <div>

        <Head title="Banners" />

        <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6 lg:p-8">
            <div class="space-y-5">

                <!-- ENCABEZADO -->
                <div class="flex flex-col gap-5 px-6 mb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Banners</h3>
                        <p class="text-sm text-gray-500">Arrastra las filas para reordenar y pulsa "Guardar orden"</p>
                    </div>
                </div>

                <!-- TABS POR EVENT_TYPE -->
                <div class="px-6">
                    <div class="flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-700 pb-0">
                        <button v-for="type in eventTypes" :key="type" @click="changeType(type)" :class="[
                            'px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 transition-colors',
                            selectedType === type
                                ? 'border-brand-500 text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400'
                        ]">
                            {{ labelFor(type) }}
                        </button>
                    </div>
                </div>

                <!-- FILTROS -->
                <div class="px-6">
                    <div
                        class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-4">
                        <div class="flex flex-wrap gap-4 items-end">

                            <!-- BUSCAR POR TÍTULO -->
                            <div class="flex-1 min-w-[180px]">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                    Buscar por título
                                </label>
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <input v-model="searchText" @input="onSearchInput" type="text"
                                        placeholder="Ej. Congreso 2026"
                                        class="w-full h-10 pl-9 pr-4 rounded-lg border border-gray-300 text-sm text-gray-800 dark:bg-gray-900 dark:border-gray-700 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-300" />
                                </div>
                            </div>

                            <!-- ESTADO -->
                            <div class="min-w-[150px]">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                    Estado
                                </label>
                                <select v-model="selectedStatus" @change="applyFilters"
                                    class="w-full h-10 px-3 rounded-lg border border-gray-300 text-sm text-gray-700 dark:bg-gray-900 dark:border-gray-700 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                                    <option value="">Todos</option>
                                    <option value="active">Activos</option>
                                    <option value="inactive">Inactivos</option>
                                </select>
                            </div>

                            <!-- FILTRO FECHA (solo para no-home) -->
                            <template v-if="selectedType !== 'home'">
                                <div class="min-w-[150px]">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        Fecha desde
                                    </label>
                                    <input v-model="dateFrom" @change="applyFilters" type="date"
                                        class="w-full h-10 px-3 rounded-lg border border-gray-300 text-sm text-gray-700 dark:bg-gray-900 dark:border-gray-700 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/20" />
                                </div>

                                <div class="min-w-[150px]">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        Fecha hasta
                                    </label>
                                    <input v-model="dateTo" @change="applyFilters" type="date"
                                        class="w-full h-10 px-3 rounded-lg border border-gray-300 text-sm text-gray-700 dark:bg-gray-900 dark:border-gray-700 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/20" />
                                </div>

                                <!-- Limpiar fechas -->
                                <button v-if="dateFrom || dateTo" @click="dateFrom = ''; dateTo = ''; applyFilters()"
                                    class="h-10 px-3 text-xs text-gray-500 hover:text-red-500 transition-colors self-end">
                                    Limpiar fechas
                                </button>
                            </template>

                        </div>
                    </div>
                </div>

                <!-- LISTA -->
                <div
                    class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">

                    <!-- ACCIONES DE LISTA -->
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

                        <button v-if="selectedType === 'home'" @click="showCreateModal = true"
                            class="inline-flex h-10 w-full sm:w-auto justify-center items-center gap-2 rounded-lg bg-green-600 text-white px-4 py-2.5 text-sm font-medium hover:bg-green-700 transition-colors shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                class="w-5 h-5">
                                <path
                                    d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                            </svg>
                            Nuevo banner
                        </button>
                        <p v-if="selectedType !== 'home'" class="inline-flex h-10 w-full sm:w-auto justify-center items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium shadow-sm text-gray-500">
                            Estos banners se crean desde sus respectivos eventos
                        </p>
                    </div>

                    <!-- DRAG LIST -->
                    <div class="block px-5 pb-4">
                        <div v-if="list.length === 0" class="py-12 text-center text-gray-400 text-sm">
                            No hay banners con los filtros seleccionados.
                        </div>
                        <div v-else class="space-y-4">
                            <draggable :list="list" ghost-class="ghost" item-key="id" @start="startDrag" @end="endDrag">
                                <template #item="{ element }">
                                    <BannerListItem :key="element.id" :banner="element" @edit="openEditModal"
                                        @delete="deleteBanner" @status-change="changeStatus" />
                                </template>
                            </draggable>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Modales -->
        <BannerCreateModal :show="showCreateModal" :next-order="nextOrder" @close="showCreateModal = false"
            @created="() => { showCreateModal = false; success('Banner creado correctamente') }"
            @warning="(msg) => warning(msg)" @error="(msg) => errorA(msg)" />

        <BannerEditModal :show="showEditModal" :banner="bannerToEdit"
            @close="showEditModal = false; bannerToEdit = null"
            @updated="() => { showEditModal = false; success('Banner actualizado correctamente') }"
            @warning="(msg) => warning(msg)" @error="(msg) => errorA(msg)" />

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
