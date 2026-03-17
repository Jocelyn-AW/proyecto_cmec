<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3'
import { useAlert } from '@/composables/useAlert'
import { onMounted, ref } from 'vue';
import Alerta from '@/Components/Alerta.vue';
import DataTable from '@/Components/DataTable.vue';

defineOptions({
    layout: AuthenticatedLayout
})

const { alertState, success, errorA, warning, hideAlert, info } = useAlert()

const props = defineProps({
    webinars: {
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

// ----------------------------------
// Handlers
// ----------------------------------

const handleOnCreate = () => {
    router.get(route('webinars.new'));
}

const handleOnEdit = (webinar) => {
    router.get(route('webinars.edit', webinar.id), {}, { preserveState: false });
}

const handleOnDelete = (webinarId) => {
    warning('¿Confirma que desea eliminar este webinar?.', {
        title: 'Eliminar webinar',
        buttonText: 'Sí, eliminar',
        cancelText: 'Cancelar',
        onConfirm: () => {
            hideAlert();
            router.delete(route('webinars.delete', webinarId));
        }
    })
}

const handleOnRestore = (webinarId) => {
    info('¿Confirma que desea restaurar este webinar?.', {
        title: 'Restaurar webinar',
        buttonText: 'Sí, restaurar',
        cancelText: 'Cancelar',
        onConfirm: () => {
            hideAlert();
            router.put(route('webinars.restore', webinarId));
        }
    })
}

const onChangeStatus = (webinar) => {
    const action = webinar.is_active ? 'desactivar' : 'activar'
    const title = webinar.is_active ? 'Desactivar' : 'Activar'
    const message = webinar.is_active
        ? 'Al hacerlo ya no podrá registrar más asistentes.'
        : 'Al hacerlo podrá volver a registrar asistentes.'

    warning(`¿Confirma que desea ${action} este webinar? ${message}`, {
        title: `${title} webinar`,
        buttonText: `Sí, ${action}`,
        cancelText: 'Cancelar',
        onConfirm: () => {
            hideAlert();
            router.get(route('webinars.change-status', webinar.id));
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
    router.get(route('albums.index', {
        event_type: webinar.sessions?.[0]?.sessionable_type ?? 'webinar',
        event_id: webinar.id,
    }))
}

// ----------------------------------
// Filtros
// ----------------------------------

const status = ref(route().params.status ?? '')

const filters = { status }

const hasActiveFilters = () => status.value

const clearFilters = () => {
    status.value = ''
}

// ----------------------------------
// Helpers
// ----------------------------------

const formatLabels = {
    online: 'En línea',
    in_person: 'Presencial',
    hybrid: 'Híbrido',
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
                { label: 'Duración', key: 'duration' },
                { label: 'Organiza', key: 'organized_by' },
                { label: 'Costo Miembro', key: 'member_price' },
                { label: 'Costo Residente', key: 'resident_price' },
                { label: 'Costo Invitado', key: 'guest_price' },
                { label: 'Modalidad', key: 'format' },
            ]" :filter-values="filters" :paginator="props.webinars" :searchable="true"
                :per-page-options="[5, 10, 15]" :allow-create="true" :allow-actions="true" :allow-edit="true"
                :allow-delete="true" @create="handleOnCreate" @edit="handleOnEdit" @restore="handleOnRestore"
                @delete="handleOnDelete" :only="['webinars']">

                <!-- Filtros -->
                <template #filters>
                    <div class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex flex-wrap items-center gap-3">
                            <label class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                Estatus
                            </label>
                            <select v-model="status"
                                class="rounded-lg border max-w-sm border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-700 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                <option value="all">Todos</option>
                                <option value="">Activos</option>
                                <option value="trashed">Inactivos</option>
                            </select>

                            <button v-if="hasActiveFilters()" @click="clearFilters"
                                class="inline-flex items-center gap-1.5 h-10 px-3 rounded-lg border border-gray-300 dark:border-gray-700 text-sm text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-red-500 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Limpiar
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Celdas -->
                <template #cell-topic="{ item }">
                    <span :title="item.topic" class="block max-w-[200px] truncate">{{ item.topic }}</span>
                </template>

                <template #cell-organized_by="{ item }">
                    <span :title="item.organized_by" class="block max-w-[200px] truncate">{{ item.organized_by }}</span>
                </template>

                <template #cell-date="{ item }">
                    <template v-if="item.sessions?.length > 0">
                        {{
                            (() => {
                                const parts = item.sessions[0].date.split(/[T ]/)[0].split('-')
                                return new Date(parts[0], parts[1] - 1, parts[2]).toLocaleDateString('es-MX', {
                                    day: '2-digit', month: 'short', year: 'numeric'
                                })
                            })()
                        }}
                        <span v-if="item.sessions.length > 1"
                            class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                            +{{ item.sessions.length - 1 }}
                        </span>
                    </template>
                    <span v-else class="text-gray-400 text-xs">—</span>
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

                <template #cell-format="{ item }">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium" :class="{
                        'bg-blue-100   text-blue-700': item.format === 'online',
                        'bg-green-100  text-green-700': item.format === 'in_person',
                        'bg-purple-100 text-purple-700': item.format === 'hybrid',
                    }">
                        {{ formatLabels[item.format] ?? item.format }}
                    </span>
                </template>

                <template #actionButtons="{ item }">
                    <button :title="item.program_url ? 'Ver programa PDF' : 'Sin programa PDF'" @click="openPdf(item)"
                        class="p-2 rounded-lg transition-colors border" :class="item.program_url
                            ? 'bg-red-500 text-white border-red-500 hover:bg-red-700 hover:border-red-700'
                            : 'bg-transparent text-gray-300 border-gray-200'">
                        <svg width="18" height="18" fill="currentColor" class="w-4 h-4" viewBox="0 0 16 16">
                            <path fill-rule="evenodd"
                                d="M14 4.5V14a2 2 0 0 1-2 2h-1v-1h1a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5zM1.6 11.85H0v3.999h.791v-1.342h.803q.43 0 .732-.173.305-.175.463-.474a1.4 1.4 0 0 0 .161-.677q0-.375-.158-.677a1.2 1.2 0 0 0-.46-.477q-.3-.18-.732-.179m.545 1.333a.8.8 0 0 1-.085.38.57.57 0 0 1-.238.241.8.8 0 0 1-.375.082H.788V12.48h.66q.327 0 .512.181.185.183.185.522m1.217-1.333v3.999h1.46q.602 0 .998-.237a1.45 1.45 0 0 0 .595-.689q.196-.45.196-1.084 0-.63-.196-1.075a1.43 1.43 0 0 0-.589-.68q-.396-.234-1.005-.234zm.791.645h.563q.371 0 .609.152a.9.9 0 0 1 .354.454q.118.302.118.753a2.3 2.3 0 0 1-.068.592 1.1 1.1 0 0 1-.196.422.8.8 0 0 1-.334.252 1.3 1.3 0 0 1-.483.082h-.563zm3.743 1.763v1.591h-.79V11.85h2.548v.653H7.896v1.117h1.606v.638z" />
                        </svg>
                    </button>

                    <button title="Ver galería" @click="openGallery(item)"
                        class="p-2 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-colors border border-indigo-100 hover:border-indigo-600">
                        <svg width="18" height="18" fill="currentColor" class="w-4 h-4" viewBox="0 0 17 17">
                            <path
                                d="M13 10V0H0v13h13zM1 1h11v8h-.755L8.681 5.681 7.522 6.895 5.274 3.014 1.698 9H1zm8.982 8H2.863l2.398-4.014L7.325 8.55 8.6 7.213zM1 12v-2h11v2zm16-9v13H4v-1.984h1V15h11V4h-2V3z" />
                        </svg>
                    </button>
                </template>

            </DataTable>
        </div>
    </div>

    <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
        :buttonText="alertState.buttonText" :cancelText="alertState.cancelText"
        @confirm="alertState.onConfirm ? alertState.onConfirm() : hideAlert()"
        @cancel="alertState.onCancel ? alertState.onCancel() : hideAlert()" @close="hideAlert()" />
</template>