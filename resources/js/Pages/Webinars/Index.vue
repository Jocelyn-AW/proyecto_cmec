<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3'
import { useAlert } from '@/composables/useAlert'
import { onMounted, reactive, watch } from 'vue';
import Alerta from '@/Components/Alerta.vue';
import DataTable from '@/Components/DataTable.vue';
import flatPickr from "vue-flatpickr-component";
import { Spanish } from "flatpickr/dist/l10n/es.js";
import "flatpickr/dist/flatpickr.css";

defineOptions({
    layout: AuthenticatedLayout
})

const { alertState, success, errorA, warning, hideAlert } = useAlert()

const props = defineProps({
    webinars: {
        type: Object,
        default: () => ({})
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    flash: {
        type: Object,
        default: () => ({})
    },
    auth: {
        type: Object,
        default: () => ({})
    },
    errors: {
        type: Object,
        default: () => ({})
    }
})

const page = usePage();

// Estado de filtros sincronizado con lo que llegó del servidor
const filterForm = reactive({
    search: props.filters.search ?? '',
    date: props.filters.date ?? '',
    organized_by: props.filters.organized_by ?? '',
    status: props.filters.status ?? '',
})

// Aplicar filtros con debounce al cambiar cualquier valor
let debounceTimer = null
watch(filterForm, () => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => applyFilters(), 350)
})

const applyFilters = () => {
    // Limpia valores vacíos para no ensuciar la URL
    const params = Object.fromEntries(
        Object.entries(filterForm).filter(([, v]) => v !== '' && v !== null)
    )
    router.get(route('webinars.index'), params, {
        preserveState: true,
        preserveScroll: true,
        only: ['webinars', 'filters'],
    })
}

const clearFilters = () => {
    filterForm.search = ''
    filterForm.date = ''
    filterForm.organized_by = ''
    filterForm.status = ''
}

const hasActiveFilters = () =>
    Object.values(filterForm).some(v => v !== '' && v !== null)

onMounted(() => {
    if (page.props.success || props.flash.success) {
        success(page.props.success || props.flash.success)
    }
    if (page.props.error || props.flash.error) {
        errorA(page.props.error || props.flash.error)
    }
    if (page.props.warning || props.flash.warning) {
        warning(page.props.warning || props.flash.warning)
    }
})

const flatpickrConfig = {
    locale: Spanish,
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "F j, Y",
    wrap: false,
};

const flatpickrTimeConfig = {
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
    time_24hr: false,
    minuteIncrement: 1,
    wrap: false,
};

const handleOnCreate = () => {
    router.get(route('webinars.new'));
}

const handleOnEdit = (webinar) => {
    router.get(route('webinars.edit', webinar.id), {}, { preserveState: false });
}

const handleOnDelete = (webinarId) => {
    warning('¿Confirma que desea eliminar este webinar? Esta acción no se puede deshacer.', {
        title: 'Eliminar webinar',
        buttonText: 'Sí, eliminar',
        cancelText: 'Cancelar',
        onConfirm: () => {
            hideAlert();
            router.delete(route('webinars.delete', webinarId));
        }
    })
}

const openPdf = (webinar) => {
    if (webinar.program_url) {
        window.open(webinar.program_url, '_blank');
    } else {
        warning('Este webinar no tiene un programa PDF asociado.', {
            title: 'Sin programa',
            buttonText: 'Aceptar'
        })
    }
}

const openGallery = (webinar) => {
    router.get(route('webinars.gallery', webinar.id));
}
</script>

<template>

    <Head title="Webinars" />

    <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
        <div class="space-y-5">

            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Webinars</h3>
                <p class="text-sm text-gray-500">Administra los webinars de tu aplicación desde esta sección</p>
            </div>

            <DataTable :columns="[
                { label: 'Título', key: 'topic' },
                { label: 'Fecha', key: 'date' },
                { label: 'Descripción', key: 'description' },
                { label: 'Duración', key: 'duration' },
                { label: 'Organiza', key: 'organized_by' },
                { label: 'Costo Miembro', key: 'member_price' },
                { label: 'Costo Residente', key: 'resident_price' },
                { label: 'Costo Invitado', key: 'guest_price' },
                { label: 'Link', key: 'link' },
                { label: 'Estado', key: 'is_active', align: 'center' },
            ]" :paginator="props.webinars" :searchable="false" :per-page-options="[5, 10, 15]" :allow-create="false"
                :allow-actions="true" :allow-edit="true" :allow-delete="true"
                @edit="handleOnEdit" @delete="handleOnDelete" :only="['webinars']">

                <template #cell-topic="{ item }">
                    <span :title="item.topic" class="block max-w-[200px] truncate">{{ item.topic }}</span>
                </template>

                <template #cell-description="{ item }">
                    <span :title="item.description" class="block max-w-[200px] truncate">{{ item.description }}</span>
                </template>

                <template #cell-date="{ item }">
                    {{ new Date(item.date).toLocaleDateString('es-MX', {
                        day: '2-digit', month: 'short', year: 'numeric'
                    }) }}
                </template>

                <template #cell-duration="{ item }">
                    {{ item.duration }} {{ item.duration > 1 ? 'horas' : 'hora' }}
                </template>

                <template #cell-member_price="{ item }">
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium"
                        :class="item.member_price == '0.00' ? 'bg-emerald-200 text-emerald-700' : 'bg-indigo-200 text-indigo-700'">
                        {{ item.member_price === '0.00' ? 'Gratis' : '$' + item.member_price }}
                    </span>
                </template>

                <template #cell-resident_price="{ item }">
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium"
                        :class="item.resident_price == '0.00' ? 'bg-emerald-200 text-emerald-700' : 'bg-sky-200 text-sky-700'">
                        {{ item.resident_price === '0.00' ? 'Gratis' : '$' + item.resident_price }}
                    </span>
                </template>

                <template #cell-guest_price="{ item }">
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium"
                        :class="item.guest_price == '0.00' ? 'bg-emerald-200 text-emerald-700' : 'bg-sky-200 text-sky-700'">
                        {{ item.guest_price === '0.00' ? 'Gratis' : '$' + item.guest_price }}
                    </span>
                </template>

                <template #cell-link="{ item }">
                    <a v-if="item.link" :href="item.link" target="_blank" class="text-blue-800 hover:underline text-sm">
                        {{ item.link.length > 25 ? item.link.substring(0, 25) + '...' : item.link }}
                    </a>
                    <span v-else class="text-gray-400 text-xs">—</span>
                </template>

                <!-- Columna Estado -->
                <template #cell-is_active="{ item }">
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium"
                        :class="item.is_active ? 'bg-emerald-200 text-emerald-700' : 'bg-gray-200 text-gray-500'">
                        {{ item.is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                </template>

                <template #actionButtons="{ item }">
                    <!-- PDF: rojo activo si tiene PDF, gris si no -->
                    <button :title="item.program_url ? 'Ver programa PDF' : 'Sin programa PDF'" @click="openPdf(item)"
                        :class="item.program_url
                            ? 'bg-red-500 text-white border-red-500 hover:bg-red-700 hover:border-red-700'
                            : 'bg-transparent text-gray-300 border-gray-200'"
                        class="p-2 rounded-lg transition-colors border">
                        <svg width="18" height="18" fill="currentColor" class="w-4 h-4" viewBox="0 0 16 16"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M14 4.5V14a2 2 0 0 1-2 2h-1v-1h1a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5zM1.6 11.85H0v3.999h.791v-1.342h.803q.43 0 .732-.173.305-.175.463-.474a1.4 1.4 0 0 0 .161-.677q0-.375-.158-.677a1.2 1.2 0 0 0-.46-.477q-.3-.18-.732-.179m.545 1.333a.8.8 0 0 1-.085.38.57.57 0 0 1-.238.241.8.8 0 0 1-.375.082H.788V12.48h.66q.327 0 .512.181.185.183.185.522m1.217-1.333v3.999h1.46q.602 0 .998-.237a1.45 1.45 0 0 0 .595-.689q.196-.45.196-1.084 0-.63-.196-1.075a1.43 1.43 0 0 0-.589-.68q-.396-.234-1.005-.234zm.791.645h.563q.371 0 .609.152a.9.9 0 0 1 .354.454q.118.302.118.753a2.3 2.3 0 0 1-.068.592 1.1 1.1 0 0 1-.196.422.8.8 0 0 1-.334.252 1.3 1.3 0 0 1-.483.082h-.563zm3.743 1.763v1.591h-.79V11.85h2.548v.653H7.896v1.117h1.606v.638z">
                            </path>
                        </svg>
                    </button>

                    <!-- Galería -->
                    <button title="Ver galería" @click="openGallery(item)"
                        class="p-2 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-colors border border-indigo-100 hover:border-indigo-600">
                        <svg width="18" height="18" fill="currentColor" class="w-4 h-4" viewBox="0 0 17 17"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M13 10V0H0v13h13zM1 1h11v8h-.755L8.681 5.681 7.522 6.895 5.274 3.014 1.698 9H1zm8.982 8H2.863l2.398-4.014L7.325 8.55 8.6 7.213zM1 12v-2h11v2zm16-9v13H4v-1.984h1V15h11V4h-2V3z">
                            </path>
                        </svg>
                    </button>
                </template>

                <template #actions class="w-full sm:w-auto">

                    <div class="flex flex-wrap items-center gap-3">

                        <!-- Buscar -->
                        <input v-model="filterForm.search" type="text" placeholder="Buscar por título..."
                            class="px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800" />

                        <!-- Fecha -->
                        <flat-pickr v-model="filterForm.date" :config="flatpickrConfig"
                            class="px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800"
                            placeholder="Fecha" />

                        <!-- Organizador -->
                        <input v-model="filterForm.organized_by" type="text" placeholder="Organizador..."
                            class="px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800" />

                        <!-- Estado -->
                        <select v-model="filterForm.status"
                            class="px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800">
                            <option value="">Todos</option>
                            <option value="active">Activo</option>
                            <option value="inactive">Inactivo</option>
                        </select>

                        <!-- Limpiar -->
                        <button v-if="hasActiveFilters()" @click="clearFilters"
                            class="px-3 py-2 text-sm text-red-600 bg-red-50 rounded-lg">
                            Limpiar
                        </button>

                        <button @click="handleOnCreate"
                            class="inline-flex items-center gap-1 rounded-lg bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700">
                            Nuevo
                        </button>
                    </div>
                </template>
            </DataTable>
        </div>
    </div>

    <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
        :buttonText="alertState.buttonText" :cancelText="alertState.cancelText"
        @confirm="alertState.onConfirm ? alertState.onConfirm() : hideAlert()"
        @cancel="alertState.onCancel ? alertState.onCancel() : hideAlert()" @close="hideAlert()" />
</template>