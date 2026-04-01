<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Alerta from '@/Components/Alerta.vue'
import { useAlert } from '@/composables/useAlert'
import flatPickr from 'vue-flatpickr-component'
import { Spanish } from 'flatpickr/dist/l10n/es.js'
import 'flatpickr/dist/flatpickr.css'
import HistoryListItem from './HistoryListItem.vue'

defineOptions({ layout: AuthenticatedLayout })

const { alertState, success, errorA, warning, hideAlert } = useAlert()

const props = defineProps({
    history: { type: Object, default: () => ({ data: [], links: [], meta: {} }) },
    flash: { type: Object, default: () => ({}) },
    errors: { type: Object, default: () => ({}) },
})

// ----------------------------------
// Flash
// ----------------------------------

const page = usePage()

onMounted(() => {
    if (page.props.success || props.flash?.success) success(page.props.success || props.flash.success)
    if (page.props.error || props.flash?.error) errorA(page.props.error || props.flash.error)
    if (page.props.warning || props.flash?.warning) warning(page.props.warning || props.flash.warning)
})

// ----------------------------------
// Filtros
// ----------------------------------

const dateFrom = ref(route().params.date_from ?? '')
const dateTo = ref(route().params.date_to ?? '')
const didAttend = ref(route().params.did_attend ?? '')
const hasDiploma = ref(route().params.has_diploma ?? '')
const eventType = ref(route().params.event_type ?? '')

const hasActiveFilters = computed(() =>
    dateFrom.value || dateTo.value || didAttend.value || hasDiploma.value || eventType.value
)

const clearFilters = () => {
    dateFrom.value = ''
    dateTo.value = ''
    didAttend.value = ''
    hasDiploma.value = ''
    eventType.value = ''
}

const applyFilters = () => {
    router.get(
        route('history.index'),
        {
            date_from: dateFrom.value || undefined,
            date_to: dateTo.value || undefined,
            did_attend: didAttend.value || undefined,
            has_diploma: hasDiploma.value || undefined,
            event_type: eventType.value || undefined,
        },
        { preserveState: true, replace: true, only: ['history'] }
    )
}

watch([dateFrom, dateTo, didAttend, hasDiploma, eventType], applyFilters)

// ----------------------------------
// flatpickr
// ----------------------------------

const flatpickrConfig = {
    locale: Spanish,
    dateFormat: 'Y-m-d',
    altInput: true,
    altFormat: 'j M Y',
}

// ----------------------------------
// Paginacion
// ----------------------------------

const goToPage = (url) => {
    if (url) router.get(url, {}, { preserveState: true, only: ['history'] })
}
</script>

<template>
    <div>

        <Head title="Mi historial" />

        <div class="min-h-screen">

            <!-- CARD -->
            <div
                class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800">
                <div class="flex flex-col lg:flex-row">

                    <!-- Texto -->
                    <div class="flex flex-1 flex-col justify-center p-8 lg:p-10">
                        <p class="text-sm font-medium text-brand-600">Actividad del miembro</p>
                        <h1 class="mt-1 text-2xl font-bold tracking-tight text-gray-800 dark:text-white/90 sm:text-3xl">
                            Mi historial de eventos
                        </h1>
                        <p class="mt-3 max-w-md text-sm leading-relaxed text-gray-500">
                            Registro cronológico de todos los eventos en los que has participado.
                            Consulta tus pagos, asistencias y descarga tus diplomas.
                        </p>

                        <!-- INFO FLASH -->
                        <div class="mt-6 flex flex-wrap gap-4">
                            <div class="flex flex-col">
                                <span class="text-2xl font-bold text-gray-800 dark:text-white/90">
                                    {{ history.total ?? 0 }}
                                </span>
                                <span class="text-xs text-gray-500">Resultados encontrados</span>
                            </div>
                            <div class="h-12 w-px bg-gray-200 dark:bg-gray-700" />
                            <div class="flex flex-col">
                                <span class="text-2xl font-bold text-gray-800 dark:text-white/90">
                                    {{history.data?.filter(i => i.did_attend).length ?? 0}}
                                </span>
                                <span class="text-xs text-gray-500">Asistencias (esta página)</span>
                            </div>
                            <div class="h-12 w-px bg-gray-200 dark:bg-gray-700" />
                            <div class="flex flex-col">
                                <span class="text-2xl font-bold text-gray-800 dark:text-white/90">
                                    {{history.data?.filter(i => i.diploma_url).length ?? 0}}
                                </span>
                                <span class="text-xs text-gray-500">Diplomas (esta página)</span>
                            </div>
                        </div>
                    </div>

                    <!-- icono decorativo -->
                    <div class="relative h-48 w-full lg:h-auto lg:w-72 xl:w-80 overflow-hidden">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-brand-600 via-brand-500 to-brand-400 opacity-10 dark:opacity-5" />
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="0.75" stroke-linecap="round" stroke-linejoin="round"
                                class="h-40 w-40 text-brand-200 dark:text-brand-800">
                                <path d="M8 2v4" />
                                <path d="M16 2v4" />
                                <rect width="18" height="18" x="3" y="4" rx="2" />
                                <path d="M3 10h18" />
                                <path d="M8 14h.01" />
                                <path d="M12 14h.01" />
                                <path d="M16 14h.01" />
                                <path d="M8 18h.01" />
                                <path d="M12 18h.01" />
                                <path d="M16 18h.01" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CONTENIDO -->
            <div class="mt-8 space-y-6">

                <!-- FILTROS -->
                <div
                    class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800">

                    <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 px-8 py-5">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                class="h-5 w-5">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Filtros</h2>
                            <p class="text-xs text-gray-500">Filtra tu historial por fecha, asistencia o diploma</p>
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">

                            <!-- Tipo de evento (NUEVO) -->
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                    Tipo de evento
                                </label>
                                <div class="relative">
                                    <select v-model="eventType"
                                        class="h-10 w-full appearance-none rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 pr-8 text-sm text-gray-800 dark:text-white/90 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900">
                                        <option value="">Todos</option>
                                        <option value="webinar">Webinar</option>
                                        <option value="academic_session">Sesión Académica</option>
                                        <option value="conference">Congreso</option>
                                        <option value="pre_conference">Pre-Congreso</option>
                                        <option value="trans_conference">Trans-Congreso</option>
                                        <option value="course">Curso</option>
                                        <option value="membership">Membresía</option>
                                    </select>
                                    <span
                                        class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                                        <svg class="stroke-current" width="14" height="14" viewBox="0 0 16 16"
                                            fill="none">
                                            <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke-width="1.2"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            <!-- Desde -->
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                    Desde
                                </label>
                                <flat-pickr v-model="dateFrom" :config="flatpickrConfig" placeholder="Fecha inicio"
                                    class="h-10 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-sm text-gray-800 dark:text-white/90 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900" />
                            </div>

                            <!-- Hasta -->
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                    Hasta
                                </label>
                                <flat-pickr v-model="dateTo" :config="flatpickrConfig" placeholder="Fecha fin"
                                    class="h-10 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-sm text-gray-800 dark:text-white/90 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900" />
                            </div>

                            <!-- Asistencia -->
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                    Asistencia
                                </label>
                                <div class="relative">
                                    <select v-model="didAttend"
                                        class="h-10 w-full appearance-none rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 pr-8 text-sm text-gray-800 dark:text-white/90 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900">
                                        <option value="">Todos</option>
                                        <option value="yes">Asistió</option>
                                        <option value="no">No asistió</option>
                                    </select>
                                    <span
                                        class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                                        <svg class="stroke-current" width="14" height="14" viewBox="0 0 16 16"
                                            fill="none">
                                            <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke-width="1.2"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            <!-- Diploma -->
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                    Diploma
                                </label>
                                <div class="relative">
                                    <select v-model="hasDiploma"
                                        class="h-10 w-full appearance-none rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 pr-8 text-sm text-gray-800 dark:text-white/90 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900">
                                        <option value="">Todos</option>
                                        <option value="yes">Con diploma</option>
                                        <option value="no">Sin diploma</option>
                                    </select>
                                    <span
                                        class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                                        <svg class="stroke-current" width="14" height="14" viewBox="0 0 16 16"
                                            fill="none">
                                            <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke-width="1.2"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Limpiar -->
                        <div v-if="hasActiveFilters" class="mt-4 flex justify-end">
                            <button @click="clearFilters"
                                class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg border border-gray-300 dark:border-gray-700 text-sm text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-red-500 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Limpiar filtros
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TABLA / VACIO -->
                <div
                    class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800">

                    <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 px-8 py-5">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                class="h-5 w-5">
                                <path d="M8 2v4M16 2v4" />
                                <rect width="18" height="18" x="3" y="4" rx="2" />
                                <path d="M3 10h18" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Eventos</h2>
                            <p class="text-xs text-gray-500">
                                {{ history.total
                                    ? `${history.total} resultado${history.total === 1 ? '' : 's'} encontrado${history.total
                                        === 1 ? '' : 's'}`
                                : 'Sin resultados' }}
                            </p>
                        </div>
                    </div>

                    <!-- Vacío -->
                    <div v-if="!history.data?.length" class="py-24 text-center">
                        <div
                            class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Sin eventos registrados</p>
                        <p class="mt-1 text-xs text-gray-400">No se encontraron eventos con los filtros seleccionados
                        </p>
                    </div>

                    <!-- Tabla -->
                    <div v-else class="overflow-x-auto">
                        <table class="w-full border-collapse text-left">
                            <thead>
                                <tr
                                    class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    <th class="px-8 py-3 border-b border-gray-100 dark:border-white/[0.05]">Evento</th>
                                    <th class="px-4 py-3 border-b border-gray-100 dark:border-white/[0.05]">Fecha</th>
                                    <th class="px-4 py-3 border-b border-gray-100 dark:border-white/[0.05]">Pago</th>
                                    <th class="px-4 py-3 border-b border-gray-100 dark:border-white/[0.05]">Asistencia
                                    </th>
                                    <th class="px-8 py-3 border-b border-gray-100 dark:border-white/[0.05] text-right">
                                        Documentos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-white/[0.02]">
                                <HistoryListItem v-for="item in history.data" :key="item.id" :item="item" />
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginacion -->
                    <div v-if="history.last_page > 1"
                        class="flex items-center justify-center gap-1.5 border-t border-gray-100 dark:border-white/[0.05] px-8 py-5">
                        <button v-for="link in history.links" :key="link.label" @click="goToPage(link.url)"
                            :disabled="!link.url" v-html="link.label"
                            class="inline-flex h-9 min-w-[2.25rem] items-center justify-center rounded-lg border px-3 text-sm transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                            :class="link.active
                                ? 'border-brand-500 bg-brand-500 text-white font-semibold shadow-sm'
                                : 'border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'" />
                    </div>
                </div>

            </div>
        </div>

        <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
            :buttonText="alertState.buttonText" :cancelText="alertState.cancelText"
            @confirm="alertState.onConfirm ? alertState.onConfirm() : hideAlert()"
            @cancel="alertState.onCancel ? alertState.onCancel() : hideAlert()" @close="hideAlert()" />
    </div>
</template>