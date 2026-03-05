<script setup>
import { reactive, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useAlert } from '@/composables/useAlert';
import Alerta from '@/Components/Alerta.vue';
import flatPickr from "vue-flatpickr-component";
import { Spanish } from "flatpickr/dist/l10n/es.js";
import "flatpickr/dist/flatpickr.css";

const props = defineProps({
    paginator: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    routePrefix: { type: String, required: true },
    title: { type: String, default: 'Eventos' },
    subtitle: { type: String, default: 'Administra y supervisa tus eventos' },
    eyebrow: { type: String, default: 'Módulo' },
    createLabel: { type: String, default: 'Nuevo' },
    entityLabel: { type: String, default: 'evento' },
    only: { type: Array, default: null },
})

const { alertState, warning, hideAlert } = useAlert()
const sessions = computed(() => props.paginator?.data ?? [])

const filterForm = reactive({
    search: props.filters.search ?? '',
    date: props.filters.date ?? '',
    organized_by: props.filters.organized_by ?? '',
    status: props.filters.status ?? '',
})

const flatpickrConfig = {
    locale: Spanish, dateFormat: "Y-m-d",
    altInput: true, altFormat: "F j, Y",
}

let debounceTimer = null
watch(filterForm, () => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => applyFilters(), 350)
})

const applyFilters = () => {
    const params = Object.fromEntries(
        Object.entries(filterForm).filter(([, v]) => v !== '' && v !== null)
    )
    const onlyProps = props.only ?? [props.routePrefix]
    router.get(route(`${props.routePrefix}.index`), params, {
        preserveState: true, preserveScroll: true,
        only: [...onlyProps, 'filters'],
    })
}

const clearFilters = () => {
    filterForm.search = ''; filterForm.date = '';
    filterForm.organized_by = ''; filterForm.status = '';
}

const hasActiveFilters = () => Object.values(filterForm).some(v => v !== '' && v !== null)

const goToPage = (url) => {
    if (!url) return
    const onlyProps = props.only ?? [props.routePrefix]
    router.get(url, {}, { preserveState: true, preserveScroll: true, only: onlyProps })
}

const formatLabels = { online: 'En línea', in_person: 'Presencial', hybrid: 'Híbrido' }
const formatLabel = (value) => formatLabels[value] || value

const formatPrice = (price) => {
    const val = parseFloat(price)
    return val === 0 ? 'Gratis' : '$' + price
}

const formatDate = (dateStr) => {
    const parts = dateStr.split(/[T ]/)[0].split('-')
    return new Date(parts[0], parts[1] - 1, parts[2])
        .toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' })
}

const handleOnCreate = () => router.get(route(`${props.routePrefix}.new`))
const handleOnEdit = (item) => router.get(route(`${props.routePrefix}.edit`, item.id), {}, { preserveState: false })

const onChangeStatus = (item) => {
    const action = item.is_active ? 'desactivar' : 'activar'
    const title = item.is_active ? 'Desactivar' : 'Activar'
    const message = item.is_active
        ? 'Al hacerlo ya no podrá registrar más asistentes.'
        : 'Al hacerlo podrá volver a registrar asistentes.'
    warning(`¿Confirma que desea ${action} esta ${props.entityLabel}? ${message}`, {
        title: `${title} ${props.entityLabel}`,
        buttonText: `Sí, ${action}`, cancelText: 'Cancelar',
        onConfirm: () => { hideAlert(); router.patch(route(`${props.routePrefix}.statusChange`, item.id)) }
    })
}

const handleOnDelete = (item) => {
    warning(`¿Confirma que desea eliminar esta ${props.entityLabel}? Esta acción no se puede deshacer.`, {
        title: `Eliminar ${props.entityLabel}`,
        buttonText: 'Sí, eliminar', cancelText: 'Cancelar',
        onConfirm: () => { hideAlert(); router.delete(route(`${props.routePrefix}.delete`, item.id)) }
    })
}

const openPdf = (item) => {
    if (item.program_url) {
        window.open(item.program_url, '_blank')
    } else {
        warning(`Esta ${props.entityLabel} no tiene un programa PDF asociado.`, {
            title: 'Sin programa', buttonText: 'Aceptar',
        })
    }
}

const openGallery = (item) => router.get(route(`${props.routePrefix}.gallery`, item.id))
</script>

<template>
    <div class="p-6 sm:p-8">

        <!-- ENCABEZADO  -->
        <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-[#191319] dark:text-white">{{ title }}</h1>
                <p class="text-sm text-[#72689D] dark:text-[#72689D]/80 mt-0.5">{{ subtitle }}</p>
            </div>
            <button @click="handleOnCreate"
                class="inline-flex items-center gap-2 rounded-xl bg-[#373C8B] hover:bg-[#3157F8] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all mt-4 sm:mt-0">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                {{ createLabel }}
            </button>
        </div>

        <!-- FILTROS -->
        <div
            class="bg-white dark:bg-gray-900 border border-[#E6F0F7] dark:border-gray-700 rounded-2xl p-4 mb-6 shadow-sm">
            <div class="flex flex-wrap items-center gap-3">

                <!-- BUSCAR -->
                <div class="relative flex-1 min-w-[180px]">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-[#72689D] pointer-events-none" width="15"
                        height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input v-model="filterForm.search" type="text" placeholder="Buscar por título o descripción…"
                        class="w-full pl-9 pr-3 py-2 text-sm rounded-xl border border-[#E6F0F7] dark:border-gray-600 bg-[#E6F0F7]/40 dark:bg-gray-800 text-[#191319] dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#3157F8]/40 focus:border-[#3157F8] transition" />
                </div>

                <!-- FECHA -->
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-[#72689D] pointer-events-none z-10"
                        width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                    <flat-pickr v-model="filterForm.date" :config="flatpickrConfig" placeholder="Fecha de sesión"
                        class="pl-9 pr-3 py-2 text-sm rounded-xl border border-[#E6F0F7] dark:border-gray-600 bg-[#E6F0F7]/40 dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-[#3157F8]/40 focus:border-[#3157F8] transition" />
                </div>

                <!-- ORGANIZAR -->
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-[#72689D] pointer-events-none" width="14"
                        height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                    </svg>
                    <input v-model="filterForm.organized_by" type="text" placeholder="Organizador..."
                        class="pl-9 pr-3 py-2 text-sm rounded-xl border border-[#E6F0F7] dark:border-gray-600 bg-[#E6F0F7]/40 dark:bg-gray-800 text-[#191319] dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#3157F8]/40 focus:border-[#3157F8] transition" />
                </div>

                <!-- ESTADO -->
                <select v-model="filterForm.status"
                    class="px-3 py-2 text-sm rounded-xl border border-[#E6F0F7] dark:border-gray-600 bg-[#E6F0F7]/40 dark:bg-gray-800 text-[#191319] dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#3157F8]/40 focus:border-[#3157F8] transition">
                    <option value="">Todos los estados</option>
                    <option value="active">Activo</option>
                    <option value="inactive">Inactivo</option>
                </select>

                <slot name="extra-filters" />

                <!-- LIMPIAR FILTRO -->
                <button v-if="hasActiveFilters()" @click="clearFilters"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-[#DF7DAC] bg-[#DF7DAC]/10 hover:bg-[#DF7DAC]/20 dark:bg-[#DF7DAC]/10 dark:hover:bg-[#DF7DAC]/20 rounded-xl transition">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                    Limpiar
                </button>
            </div>
        </div>

        <!-- GRID DE TARJETAS  -->
        <div v-if="sessions.length > 0" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
            <div v-for="item in sessions" :key="item.id"
                class="group relative bg-white dark:bg-gray-900 border border-[#E6F0F7] dark:border-gray-700 rounded-2xl overflow-hidden shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-200 flex flex-col"
                :class="{ 'opacity-60': !item.is_active }">
                <!-- MODALIDADES -->
                <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-2xl" :class="{
                    'bg-[#3157F8]': item.format === 'online',
                    'bg-[#51DBFD]': item.format === 'in_person',
                    'bg-[#72689D]': item.format === 'hybrid',
                }"></div>

                <!-- CABECERA -->
                <div class="relative bg-gradient-to-br from-[#191319] to-[#373C8B] px-5 pt-4 pb-5 pl-6">

                    <!-- MODALIDAD + ESTADO -->
                    <div class="flex items-center justify-between mb-3">

                        <!-- MODALIDAD SIGNO -->
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold tracking-wide"
                            :class="{
                                'bg-[#3157F8]/20 text-[#51DBFD]': item.format === 'online',
                                'bg-[#51DBFD]/20 text-[#51DBFD]': item.format === 'in_person',
                                'bg-[#72689D]/30 text-[#DF7DAC]': item.format === 'hybrid',
                            }">
                            <svg v-if="item.format === 'online'" width="11" height="11" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="2" y1="12" x2="22" y2="12" />
                                <path
                                    d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                            </svg>
                            <svg v-else-if="item.format === 'in_person'" width="11" height="11" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            <svg v-else width="11" height="11" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <rect x="2" y="3" width="20" height="14" rx="2" />
                                <line x1="8" y1="21" x2="16" y2="21" />
                                <line x1="12" y1="17" x2="12" y2="21" />
                            </svg>
                            {{ formatLabel(item.format) }}
                        </span>

                        <!-- ESTADO -->
                        <span role="button" @click="onChangeStatus(item)"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold cursor-pointer transition-colors"
                            :class="item.is_active
                                ? 'bg-emerald-500/20 text-emerald-300 hover:bg-emerald-500/40'
                                : 'bg-[#DF7DAC]/20 text-[#DF7DAC] hover:bg-[#DF7DAC]/40'">
                            <span class="w-1.5 h-1.5 rounded-full"
                                :class="item.is_active ? 'bg-emerald-400' : 'bg-[#DF7DAC]'"></span>
                            {{ item.is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    <!-- TITULO -->
                    <h2 class="text-white font-semibold text-sm leading-snug line-clamp-2" :title="item.topic">
                        {{ item.topic }}
                    </h2>

                    <!-- ORGANIZADOR -->
                    <p class="mt-2 text-xs text-[#51DBFD]/70 flex items-center gap-1.5">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                        </svg>
                        {{ item.organized_by }}
                    </p>
                </div>

                <!-- CUERPO -->
                <div class="flex flex-col gap-3 px-5 py-4 pl-6 flex-1">

                    <!-- DESCRIPCION -->
                    <p class="text-xs text-[#72689D] dark:text-[#72689D]/80 line-clamp-2 leading-relaxed"
                        :title="item.description">
                        {{ item.description }}
                    </p>

                    <!-- FECHAS -->
                    <div class="flex flex-wrap gap-1.5">
                        <template v-if="item.sessions && item.sessions.length > 0">
                            <span v-for="(s, idx) in item.sessions.slice(0, 2)" :key="idx"
                                class="inline-flex items-center gap-1 bg-[#E6F0F7] dark:bg-gray-800 border border-[#E6F0F7] dark:border-gray-700 rounded-lg px-2 py-1 text-xs text-[#373C8B] dark:text-gray-300 font-medium">
                                <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24" class="text-[#3157F8]">
                                    <rect x="3" y="4" width="18" height="18" rx="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
                                {{ formatDate(s.date) }}
                            </span>
                            <span v-if="item.sessions.length > 2"
                                class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-[#E6F0F7] dark:bg-gray-800 text-[#72689D] dark:text-gray-400 border border-[#E6F0F7] dark:border-gray-700">
                                +{{ item.sessions.length - 2 }} más
                            </span>
                        </template>
                        <span v-else class="text-xs text-[#72689D]/60">Sin fechas registradas</span>
                    </div>

                    <!-- DURACION -->
                    <div class="flex items-center gap-1.5 text-xs text-[#72689D] dark:text-[#72689D]/70">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        {{ item.duration }} {{ item.duration > 1 ? 'horas' : 'hora' }} de duración
                    </div>

                    <!-- PRECIO -->
                    <div class="grid grid-cols-3 gap-2 pt-1">
                        <div
                            class="bg-[#E6F0F7]/60 dark:bg-gray-800 border border-[#E6F0F7] dark:border-gray-700 rounded-xl p-2 text-center">
                            <p class="text-xs text-[#72689D] mb-0.5">Miembro</p>
                            <p class="text-xs font-bold" :class="parseFloat(item.member_price) === 0
                                ? 'text-emerald-600 dark:text-emerald-400'
                                : 'text-[#3157F8] dark:text-[#3157F8]'">
                                {{ formatPrice(item.member_price) }}
                            </p>
                        </div>
                        <div
                            class="bg-[#E6F0F7]/60 dark:bg-gray-800 border border-[#E6F0F7] dark:border-gray-700 rounded-xl p-2 text-center">
                            <p class="text-xs text-[#72689D] mb-0.5">Residente</p>
                            <p class="text-xs font-bold" :class="parseFloat(item.resident_price) === 0
                                ? 'text-emerald-600 dark:text-emerald-400'
                                : 'text-[#373C8B] dark:text-[#51DBFD]'">
                                {{ formatPrice(item.resident_price) }}
                            </p>
                        </div>
                        <div
                            class="bg-[#E6F0F7]/60 dark:bg-gray-800 border border-[#E6F0F7] dark:border-gray-700 rounded-xl p-2 text-center">
                            <p class="text-xs text-[#72689D] mb-0.5">Invitado</p>
                            <p class="text-xs font-bold" :class="parseFloat(item.guest_price) === 0
                                ? 'text-emerald-600 dark:text-emerald-400'
                                : 'text-[#DF7DAC] dark:text-[#DF7DAC]'">
                                {{ formatPrice(item.guest_price) }}
                            </p>
                        </div>
                    </div>

                    <slot name="card-extra" :item="item" />
                </div>

                <!-- FOOTER ACCIONES -->
                <div class="border-t border-[#E6F0F7] dark:border-gray-700 px-5 py-3 pl-6 flex items-center gap-2">

                    <!-- EDITAR -->
                    <button @click="handleOnEdit(item)" title="Editar"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-[#E6F0F7] dark:border-gray-700 bg-[#E6F0F7]/40 dark:bg-gray-800 text-[#72689D] dark:text-gray-400 hover:border-[#3157F8] hover:text-[#3157F8] hover:bg-[#3157F8]/10 dark:hover:bg-[#3157F8]/20 dark:hover:text-[#3157F8] transition-colors">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                    </button>

                    <!-- ELIMINAR -->
                    <button @click="handleOnDelete(item)" title="Eliminar"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-[#E6F0F7] dark:border-gray-700 bg-[#E6F0F7]/40 dark:bg-gray-800 text-[#72689D] dark:text-gray-400 hover:border-[#DF7DAC] hover:text-[#DF7DAC] hover:bg-[#DF7DAC]/10 dark:hover:bg-[#DF7DAC]/20 dark:hover:text-[#DF7DAC] transition-colors">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <polyline points="3 6 5 6 21 6" />
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                            <path d="M10 11v6M14 11v6" />
                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                        </svg>
                    </button>

                    <!-- PDF -->
                    <button @click="openPdf(item)" :title="item.program_url ? 'Ver programa PDF' : 'Sin programa PDF'"
                        :disabled="!item.program_url"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg border transition-colors"
                        :class="item.program_url
                            ? 'border-[#DF7DAC]/30 bg-[#DF7DAC]/10 text-[#DF7DAC] hover:bg-[#DF7DAC] hover:text-white hover:border-[#DF7DAC]'
                            : 'border-[#E6F0F7] dark:border-gray-700 bg-[#E6F0F7]/40 dark:bg-gray-800 text-[#E6F0F7] dark:text-gray-600 cursor-not-allowed'">
                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                            <path
                                d="M14 4.5V14a2 2 0 0 1-2 2h-1v-1h1a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5zM1.6 11.85H0v3.999h.791v-1.342h.803q.43 0 .732-.173.305-.175.463-.474a1.4 1.4 0 0 0 .161-.677q0-.375-.158-.677a1.2 1.2 0 0 0-.46-.477q-.3-.18-.732-.179m.545 1.333a.8.8 0 0 1-.085.38.57.57 0 0 1-.238.241.8.8 0 0 1-.375.082H.788V12.48h.66q.327 0 .512.181.185.183.185.522m1.217-1.333v3.999h1.46q.602 0 .998-.237a1.45 1.45 0 0 0 .595-.689q.196-.45.196-1.084 0-.63-.196-1.075a1.43 1.43 0 0 0-.589-.68q-.396-.234-1.005-.234zm.791.645h.563q.371 0 .609.152a.9.9 0 0 1 .354.454q.118.302.118.753a2.3 2.3 0 0 1-.068.592 1.1 1.1 0 0 1-.196.422.8.8 0 0 1-.334.252 1.3 1.3 0 0 1-.483.082h-.563zm3.743 1.763v1.591h-.79V11.85h2.548v.653H7.896v1.117h1.606v.638z" />
                        </svg>
                    </button>

                    <!-- GALERIA -->
                    <button @click="openGallery(item)" title="Ver galería"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-[#373C8B]/20 bg-[#373C8B]/10 text-[#373C8B] dark:text-[#51DBFD] hover:bg-[#373C8B] hover:text-white hover:border-[#373C8B] dark:hover:bg-[#3157F8] transition-colors">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <rect x="3" y="3" width="18" height="18" rx="2" />
                            <circle cx="8.5" cy="8.5" r="1.5" />
                            <polyline points="21 15 16 10 5 21" />
                        </svg>
                    </button>

                    <slot name="card-actions" :item="item" />
                </div>
            </div>
        </div>

        <!-- SIN DATOS  -->
        <div v-else
            class="flex flex-col items-center justify-center py-20 bg-white dark:bg-gray-900 border border-dashed border-[#E6F0F7] dark:border-gray-700 rounded-2xl">
            <div
                class="w-14 h-14 rounded-2xl bg-[#E6F0F7] dark:bg-gray-800 flex items-center justify-center mb-4 text-[#72689D]">
                <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M12 2L2 7l10 5 10-5-10-5z" />
                    <path d="M2 17l10 5 10-5M2 12l10 5 10-5" />
                </svg>
            </div>
            <p class="font-semibold text-[#191319] dark:text-gray-300">No se encontraron {{ entityLabel }}s</p>
            <p class="text-sm text-[#72689D] mt-1">Intenta ajustar los filtros o crea uno nuevo</p>
            <button @click="handleOnCreate"
                class="mt-5 inline-flex items-center gap-2 rounded-xl bg-[#373C8B] hover:bg-[#3157F8] px-4 py-2 text-sm font-semibold text-white transition">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                {{ createLabel }}
            </button>
        </div>

        <!-- PAGINACION  -->
        <div v-if="paginator && paginator.last_page > 1"
            class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white dark:bg-gray-900 border border-[#E6F0F7] dark:border-gray-700 rounded-2xl px-5 py-3 shadow-sm">
            <p class="text-sm text-[#72689D] dark:text-gray-400">
                Mostrando
                <span class="font-semibold text-[#191319] dark:text-white">{{ paginator.from }}–{{ paginator.to
                    }}</span>
                de
                <span class="font-semibold text-[#191319] dark:text-white">{{ paginator.total }}</span>
                resultados
            </p>
            <div class="flex items-center gap-1">
                <button v-for="link in paginator.links" :key="link.label" @click="goToPage(link.url)"
                    :disabled="!link.url || link.active" v-html="link.label"
                    class="min-w-[34px] h-8 px-2 rounded-lg text-sm border transition-colors"
                    :class="link.active
                        ? 'bg-[#373C8B] text-white border-[#373C8B] font-semibold'
                        : link.url
                            ? 'border-[#E6F0F7] dark:border-gray-700 text-[#72689D] dark:text-gray-300 hover:border-[#3157F8] hover:text-[#3157F8] bg-white dark:bg-gray-800'
                            : 'border-[#E6F0F7] dark:border-gray-800 text-[#E6F0F7] dark:text-gray-600 cursor-not-allowed bg-[#E6F0F7]/30 dark:bg-gray-900'" />
            </div>
        </div>
    </div>

    <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
        :buttonText="alertState.buttonText" :cancelText="alertState.cancelText"
        @confirm="alertState.onConfirm ? alertState.onConfirm() : hideAlert()"
        @cancel="alertState.onCancel ? alertState.onCancel() : hideAlert()" @close="hideAlert()" />
</template>