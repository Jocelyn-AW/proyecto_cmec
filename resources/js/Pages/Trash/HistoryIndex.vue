<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Alerta from '@/Components/Alerta.vue'
import { useAlert } from '@/composables/useAlert'
import flatPickr from 'vue-flatpickr-component'
import { Spanish } from 'flatpickr/dist/l10n/es.js'
import 'flatpickr/dist/flatpickr.css'

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

const hasActiveFilters = computed(() =>
    dateFrom.value || dateTo.value || didAttend.value || hasDiploma.value
)

const clearFilters = () => {
    dateFrom.value = ''
    dateTo.value = ''
    didAttend.value = ''
    hasDiploma.value = ''
}

const applyFilters = () => {
    router.get(
        route('history.index'),
        {
            date_from: dateFrom.value || undefined,
            date_to: dateTo.value || undefined,
            did_attend: didAttend.value || undefined,
            has_diploma: hasDiploma.value || undefined,
        },
        { preserveState: true, replace: true, only: ['history'] }
    )
}

watch([dateFrom, dateTo, didAttend, hasDiploma], applyFilters)

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
// Helpers
// ----------------------------------

const eventTypeLabel = (type) => {
    if (!type) return 'Evento'
    const t = type.toLowerCase()
    if (t.includes('webinar')) return 'Webinar'
    if (t.includes('academic')) return 'Sesión Académica'
    return 'Evento'
}

const eventTypeStyle = (type) => {
    const t = type?.toLowerCase() ?? ''
    if (t.includes('webinar')) {
        return {
            badge: 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
            dot: 'bg-blue-500',
        }
    }
    if (t.includes('academic')) {
        return {
            badge: 'bg-purple-100 text-purple-700 dark:bg-purple-500/15 dark:text-purple-400',
            dot: 'bg-purple-500',
        }
    }
    return {
        badge: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
        dot: 'bg-gray-400',
    }
}


const formatDate = (dateStr) => {
    if (!dateStr) return '—'
    const parts = dateStr.split(/[T ]/)[0].split('-')
    return new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]))
        .toLocaleDateString('es-MX', { day: 'numeric', month: 'long', year: 'numeric' })
}

const formatCurrency = (amount) => {
    if (amount === null || amount === undefined || amount === '') return '—'
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(amount)
}

const firstSession = (item) => {
    const sessions = item.event?.sessions
    if (!sessions?.length) return null
    return [...sessions].sort((a, b) => new Date(a.date) - new Date(b.date))[0]
}

const goToPage = (url) => {
    if (url) router.get(url, {}, { preserveState: true, only: ['history'] })
}
</script>

<template>
    <div>

        <Head title="Mi historial" />

        <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6 lg:p-8">
            <div class="max-w-5xl mx-auto space-y-8">

                <!-- Header  -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        Mi historial de eventos
                    </h3>
                    <p class="text-sm text-gray-500">
                        Registro cronológico de todos los eventos en los que participaste
                    </p>
                </div>

                <!-- Filtros  -->
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03] p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">

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
                                    <svg class="stroke-current" width="14" height="14" viewBox="0 0 16 16" fill="none">
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
                                    <svg class="stroke-current" width="14" height="14" viewBox="0 0 16 16" fill="none">
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
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Limpiar filtros
                        </button>
                    </div>
                </div>

                <!-- Estado vacío ─ -->
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
                    <p class="mt-1 text-xs text-gray-400">No se encontraron eventos con los filtros seleccionados</p>
                </div>

                <!-- Timeline -->
                <div v-else class="relative pb-8">

                    <!-- Línea vertical central -->
                    <div
                        class="absolute left-1/2 top-0 bottom-0 w-px -translate-x-1/2 bg-gray-200 dark:bg-gray-700/80" />

                    <div class="space-y-10">
                        <div v-for="(item, index) in history.data" :key="item.id"
                            class="relative flex items-start gap-0"
                            :class="index % 2 === 0 ? 'flex-row' : 'flex-row-reverse'">
                            <!-- Card ─ -->
                            <div class="w-[calc(50%-1.75rem)]" :class="index % 2 === 0 ? 'pr-8' : 'pl-8'">
                                <div
                                    class="group rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03] overflow-hidden shadow-sm hover:shadow-md transition-all duration-200">

                                    <!-- Imagen del evento -->
                                    <div v-if="item.event?.cover_url"
                                        class="relative h-32 w-full overflow-hidden bg-gray-100 dark:bg-gray-800">
                                        <img :src="item.event.cover_url" :alt="item.event?.topic"
                                            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" />
                                        <!-- Badge tipo sobre imagen -->
                                        <span
                                            class="absolute top-2 left-2 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold backdrop-blur-sm bg-white/85 dark:bg-gray-900/85"
                                            :class="eventTypeStyle(item.event_type).badge">
                                            {{ eventTypeLabel(item.event_type) }}
                                        </span>
                                    </div>

                                    <!-- Cabecera sin imagen -->
                                    <div v-else class="px-4 pt-4">
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                            :class="eventTypeStyle(item.event_type).badge">
                                            {{ eventTypeLabel(item.event_type) }}
                                        </span>
                                    </div>

                                    <!-- Cuerpo -->
                                    <div class="p-4 space-y-3">

                                        <!-- Nombre y fecha -->
                                        <div>
                                            <p
                                                class="text-sm font-semibold text-gray-800 dark:text-white/90 leading-snug line-clamp-2">
                                                {{ item.event?.topic ?? '—' }}
                                            </p>
                                            <p class="mt-1 text-xs text-gray-400 flex items-center gap-1.5">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5" />
                                                </svg>
                                                {{ firstSession(item) ? formatDate(firstSession(item).date) : '—' }}
                                            </p>
                                        </div>

                                        <div class="border-t border-gray-100 dark:border-gray-800" />

                                        <!-- Grid de datos -->
                                        <div class="grid grid-cols-2 gap-x-4 gap-y-2.5">

                                            <!-- Monto -->
                                            <div>
                                                <p
                                                    class="text-[10px] uppercase tracking-wide text-gray-400 font-medium">
                                                    Monto</p>
                                                <p
                                                    class="mt-0.5 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                                    {{ formatCurrency(item.event_payment?.amount
                                                    ) }}
                                                </p>
                                            </div>

                                            <!-- Estado de pago -->
                                            <div>
                                                <p
                                                    class="text-[10px] uppercase tracking-wide text-gray-400 font-medium">
                                                    Pago</p>

                                                <span
                                                    class="mt-0.5 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                                                    :class="{
                                                        'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400': item.event_payment?.status === 'paid',
                                                        'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400': item.event_payment?.status === 'pending',
                                                        'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400': item.event_payment?.status === 'cancelled',
                                                        'bg-sky-100 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400': item.event_payment?.status === 'free'
                                                    }">
                                                    <span class="h-1.5 w-1.5 rounded-full" :class="{
                                                        'bg-green-500': item.event_payment?.status === 'paid',
                                                        'bg-yellow-500': item.event_payment?.status === 'pending',
                                                        'bg-rose-500': item.event_payment?.status === 'cancelled',
                                                        'bg-sky-500': item.event_payment?.status === 'free'
                                                    }" />

                                                    <span>
                                                        {{ item.event_payment?.status === 'paid' ? 'Pagado' : '' }}
                                                        {{ item.event_payment?.status === 'pending' ? 'Pendiente' : ''
                                                        }}
                                                        {{ item.event_payment?.status === 'cancelled' ? 'Cancelado' : ''
                                                        }}
                                                        {{ item.event_payment?.status === 'free' ? 'Gratis' : '' }}
                                                    </span>
                                                </span>
                                            </div>

                                            <!-- Asistencia -->
                                            <div>
                                                <p
                                                    class="text-[10px] uppercase tracking-wide text-gray-400 font-medium">
                                                    Asistencia</p>
                                                <span
                                                    class="mt-0.5 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                                                    :class="item.did_attend
                                                        ? 'bg-brand-100 text-brand-700 dark:bg-brand-500/10 dark:text-brand-400'
                                                        : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'">
                                                    <span class="h-1.5 w-1.5 rounded-full"
                                                        :class="item.did_attend ? 'bg-brand-500' : 'bg-gray-400'" />
                                                    {{ item.did_attend ? 'Asistió' : 'No asistió' }}
                                                </span>
                                            </div>

                                            <!-- Referencia (si aplica) -->
                                            <div v-if="item.event_payment?.reference">
                                                <p
                                                    class="text-[10px] uppercase tracking-wide text-gray-400 font-medium">
                                                    Referencia</p>
                                                <p class="mt-0.5 text-xs font-mono text-gray-600 dark:text-gray-300 truncate"
                                                    :title="item.event_payment?.reference">
                                                    {{ item.event_payment?.reference }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Botón diploma -->
                                        <div v-if="item.diploma_url" class="pt-0.5">
                                            <a :href="item.diploma_url" target="_blank"
                                                class="inline-flex w-full items-center justify-center gap-2 h-9 rounded-lg bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 text-xs font-medium hover:bg-brand-100 dark:hover:bg-brand-500/20 border border-brand-100 dark:border-brand-500/20 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                                                </svg>
                                                Ver mi diploma
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dot central  -->
                            <div class="absolute left-1/2 top-5 z-10 -translate-x-1/2">
                                <!-- Anillo exterior -->
                                <div class="flex h-5 w-5 items-center justify-center rounded-full border-2 border-white dark:border-gray-900 shadow-md"
                                    :class="eventTypeStyle(item.event_type).dot">
                                    <!-- Punto interno blanco -->
                                    <div class="h-2 w-2 rounded-full bg-white/60" />
                                </div>
                            </div>

                            <!-- Spacer lado opuesto -->
                            <div class="w-[calc(50%-1.75rem)]" />
                        </div>
                    </div>
                </div>

                <!--  Paginación -->
                <div v-if="history.meta?.last_page > 1" class="flex items-center justify-center gap-1.5 pt-2">
                    <button v-for="link in history.meta.links" :key="link.label" @click="goToPage(link.url)"
                        :disabled="!link.url" v-html="link.label"
                        class="inline-flex h-9 min-w-[2.25rem] items-center justify-center rounded-lg border px-3 text-sm transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                        :class="link.active
                            ? 'border-brand-500 bg-brand-500 text-white font-semibold shadow-sm'
                            : 'border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'" />
                </div>

            </div>
        </div>

        <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
            :buttonText="alertState.buttonText" :cancelText="alertState.cancelText"
            @confirm="alertState.onConfirm ? alertState.onConfirm() : hideAlert()"
            @cancel="alertState.onCancel ? alertState.onCancel() : hideAlert()" @close="hideAlert()" />
    </div>
</template>