<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { watch, ref, computed } from 'vue';
import { useAlert } from '@/composables/useAlert';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Alerta from '@/Components/Alerta.vue';
import DataTable from '@/Components/DataTable.vue';

defineOptions({
    layout: AuthenticatedLayout
})

const props = defineProps({
    conferences: {
        type: Object,
        default: () => ({})
    },
    eventName: {
        type: String,
        default: 'Congreso' //Congreso, Pre-congreso, Trans-congreso
    },
    prefix: {
        type: String,
        default: 'main' // main, pre, trans
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
const { alertState, success, errorA, warning, hideAlert, info } = useAlert()

watch(() => props.flash, (value) => {
    if (!value) return

    if (value.success) success(value.success)
    if (value.warning) warning(value.warning)
    if (value.error) errorA(value.error)
}, {immediate: true, deep: true})

//Table handlers
const handleOnCreate =  () => {
    router.get(route('conferences.new', props.prefix));
}

const handleOnEdit = (conference) => {
    router.get(route('conferences.edit', [props.prefix, conference.id]), {}, {
        preserveState: false
    });
}

const handleOnDelete = (conferenceId) => {
    let prefix = props.prefix == 'main' ? '' : props.prefix + '-';
    warning('¿Confirma que desea desactivar este ' + prefix +'congreso?.', {
        title: 'Desactivar ' + prefix + 'congreso',
        buttonText: 'Sí, desactivar',
        cancelText: 'Cancelar',
        onConfirm: () => {
            hideAlert();
            router.delete(route('conferences.delete', conferenceId));
        }
    })
}

const handleOnRestore = (conferenceId) => {
    let prefix = props.prefix == 'main' ? '' : props.prefix + '-';
    info('¿Confirma que desea activar este '+ prefix + 'congreso?.', {
        title: 'Activar ' + prefix + 'congreso',
        buttonText: 'Sí, activar',
        cancelText: 'Cancelar',
        onConfirm: () => {
            hideAlert();
            router.put(route('conferences.restore', conferenceId));
        }
    })
}

//String formatters
const truncate = (text, max = 50) => {
    if (!text) return '';
    return text.length > max ? text.substring(0, max) + '...' : text;
}

const formatAmount =  (amount) => {
    let options = { style: 'currency', currency: 'USD' }
    return new Intl.NumberFormat('en-US', options).format(amount);
}

const formattedDate = (item) => {
    if (item.sessions?.[0]?.date) {
        let fullDate = item.sessions?.[0]?.date + 'T00:00:00';
        const date = new Date(fullDate);
        const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
        return new Intl.DateTimeFormat('es-MX', options).format(date);
    }
}

const pluralName = computed(() => {
    return props.eventName + 's' ?? ''
})

const lower = (value) => {
    return value?.toLowerCase();
}

//Table actions
const openPdf = (conference) => {
    if (conference.program_url) {
        window.open(conference.program_url, '_blank');
    } else {
        warning('Este congreso no tiene un programa PDF asociado.', {
            title: 'Sin programa',
            buttonText: 'Aceptar'
        })
    }
}

const openGallery = (conference) => {
    router.get(route('albums.index', {
        event_type: conference.subtype ?? 'conference',
        event_id: conference.id,
    }))
}

const onChangeStatus = (conference) => {
    let action = conference.is_active ? 'desactivar' : 'activar';
    let title = conference.is_active ? 'Desactivar' : 'Activar';
    let message = conference.is_active 
                    ? 'Al hacerlo ya no podrá registrar más asistentes.'
                    : 'Al hacerlo podrá volver a registrar asistentes.';

    warning(`¿Confirma que desea ${action} este congreso? ${message} `, {
        title: `${title} congreso`,
        buttonText: `Sí, ${action}`,
        cancelText: 'Cancelar',
        onConfirm: () => {
            hideAlert();
            router.get(route('conferences.change-status', conference.id));
        }
    })
}

//Table filters
const status = ref(route().params.status ?? '')

const filters = {
    status: status,
}

const hasActiveFilters = () =>
    status.value


const clearFilters = () => {
    status.value = ''
}

</script>
<template :key="props.prefix">
    <Head :title="pluralName" />

    <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
        <div class="space-y-5">
            <div class="">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ pluralName }}</h3>
                <p class="text-sm text-gray-500">Administra los {{ lower(pluralName) }} de tu aplicación desde esta sección</p>
            </div>
            <DataTable
                :columns="[
                    { label: 'Título', key: 'name' },
                    { label: 'Inicio', key: 'date' },
                    { label: 'Formato', key: 'format' },
                    { label: 'Organiza', key: 'organized_by' },
                    { label: 'Costo Miembro', key: 'member_price' },
                    { label: 'Costo Residente', key: 'resident_price' },
                    { label: 'Costo Invitado', key: 'guest_price' },
                    { label: 'Link', key: 'link' },
                ]"
                :filter-values="filters"
                :paginator="props.conferences"
                :searchable="true"
                :per-page-options="[5, 10, 15]"
                :allow-create="true"
                :allow-actions="true"
                :allow-edit="true"
                :allow-delete="true"
                @create="handleOnCreate"
                @edit="handleOnEdit"
                @delete="handleOnDelete"
                @restore="handleOnRestore"
                :only="['conferences']"
                >

                <template #filters
                    class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex flex-wrap items-center gap-3">
                            <!-- Asistencia -->
                            <label for="per-page-select" class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" >
                                Estatus
                            </label>
                            <select id="attend" v-model="status" 
                                class="rounded-lg border max-w-sm border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-700 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                                >
                                <option value="all">Todos</option>
                                <option value="">Activos</option>
                                <option value="trashed">Inactivos</option> 
                            </select>
                            <!-- Limpiar filtros -->
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

                <template #cell-topic="{item}">
                    {{ truncate(item.name, 25) }}
                </template>

                <template #cell-date="{ item }">
                    {{ formattedDate(item) }}
                </template>

                <template #cell-organized_by="{ item }">
                    {{truncate(item.organized_by, 20)}}
                </template>

                <template #cell-format="{ item }">
                    {{ item.format == 'in_person' ? 'Presencial' : '' }}
                    {{ item.format == 'online' ? 'En línea' : '' }}
                    {{ item.format == 'hybrid' ? 'Hibrido' : '' }}
                </template>

                <template #cell-member_price="{ item }">
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium capitalize"
                        :class="item.member_price == '0.00' ? 'bg-emerald-200 text-emerald-700' : 'bg-indigo-200 text-indigo-700'"
                        >
                        {{ item.member_price === '0.00' ? 'Gratis' : formatAmount(item.member_price) }}
                    </span>
                </template>

                <template #cell-resident_price="{ item }">
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium capitalize"
                        :class="item.resident_price == '0.00' ? 'bg-emerald-200 text-emerald-700' : 'bg-sky-200 text-sky-700'"
                        >
                        {{ item.resident_price === '0.00' ? 'Gratis' : formatAmount(item.resident_price) }}
                    </span>
                </template>

                <template #cell-guest_price="{ item }">
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium capitalize"
                        :class="item.guest_price == '0.00' ? 'bg-emerald-200 text-emerald-700' : 'bg-sky-200 text-sky-700'"
                        >
                        {{ item.guest_price === '0.00' ? 'Gratis' : formatAmount(item.guest_price) }}
                    </span>
                </template>

                <template #cell-link="{ item }">
                    <a :href="item.link" class="text-blue-800">
                        {{ truncate(item.link ?? '--', 20) }}
                    </a>
                </template>

                <template #cell-is_active="{ item }">
                    <span role="button"
                        class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium capitalize"
                        :class="!item.deleted_at 
                            ? 'bg-emerald-200 text-emerald-700 hover:bg-emerald-300' 
                            : 'bg-orange-200 text-orange-700 hover:bg-orange-300'"
                    >
                        {{ !item.deleted_at ? 'Activo' : 'Inactivo' }}
                    </span>
                </template>

                <template #actionButtons="{ item }">
                    <button title="Ver Pdf" @click="openPdf(item)"
                        class="p-2 rounded-lg "
                        :class="
                            item.program_url == null || item.program_url == '' 
                            ? 'bg-red-20 text-red-500 transition-colors border border-red-100 hover:bg-red-600 hover:text-white hover:border-red-600' 
                            : 'bg-red-400 text-white transition-colors border border-red-400 hover:bg-red-600 hover:text-white hover:border-red-600'
                        ">
                        <svg width="18" height="18" fill="currentcolor"
                            class="bi bi-filetype-pdf text-8xl w-4 h-4 dark:text-stone-200" viewBox="0 0 16 16"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M14 4.5V14a2 2 0 0 1-2 2h-1v-1h1a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5zM1.6 11.85H0v3.999h.791v-1.342h.803q.43 0 .732-.173.305-.175.463-.474a1.4 1.4 0 0 0 .161-.677q0-.375-.158-.677a1.2 1.2 0 0 0-.46-.477q-.3-.18-.732-.179m.545 1.333a.8.8 0 0 1-.085.38.57.57 0 0 1-.238.241.8.8 0 0 1-.375.082H.788V12.48h.66q.327 0 .512.181.185.183.185.522m1.217-1.333v3.999h1.46q.602 0 .998-.237a1.45 1.45 0 0 0 .595-.689q.196-.45.196-1.084 0-.63-.196-1.075a1.43 1.43 0 0 0-.589-.68q-.396-.234-1.005-.234zm.791.645h.563q.371 0 .609.152a.9.9 0 0 1 .354.454q.118.302.118.753a2.3 2.3 0 0 1-.068.592 1.1 1.1 0 0 1-.196.422.8.8 0 0 1-.334.252 1.3 1.3 0 0 1-.483.082h-.563zm3.743 1.763v1.591h-.79V11.85h2.548v.653H7.896v1.117h1.606v.638z">
                            </path>
                        </svg>
                    </button>
                    <button 
                        title="Ver galería" @click="openGallery(item)"
                        class="p-2 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-colors border border-indigo-100 hover:border-indigo-600 hover:bg-indigo-600 hover:text-white">
                            <svg width="18" height="18" fill="currentColor"
                            class="text-8xl w-4 h-4" viewBox="0 0 17 17"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M13 10V0H0v13h13zM1 1h11v8h-.755L8.681 5.681 7.522 6.895 5.274 3.014 1.698 9H1zm8.982 8H2.863l2.398-4.014L7.325 8.55 8.6 7.213zM1 12v-2h11v2zm16-9v13H4v-1.984h1V15h11V4h-2V3z">
                            </path>
                        </svg>
                        </button>
                </template>
            </DataTable>
        </div>
    </div>

    <Alerta
        :show="alertState.show"
        :message="alertState.message"
        :title="alertState.title"
        :type="alertState.type"
        :buttonText="alertState.buttonText"
        :cancelText="alertState.cancelText"
        @confirm="alertState.onConfirm ? alertState.onConfirm() : hideAlert()"
        @cancel="alertState.onCancel ? alertState.onCancel() : hideAlert()"
        @close="hideAlert()"
    />
</template>