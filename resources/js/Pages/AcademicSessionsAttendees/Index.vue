<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { Head, router, usePage } from '@inertiajs/vue3'
import { useAlert } from '@/composables/useAlert'
import { computed, onMounted, ref } from 'vue';
import Alerta from '@/Components/Alerta.vue';
import CreateAttendee from './CreateAttendee.vue';
import EditAttendee from './EditAttendee.vue';
import UploadDiploma from '../Attendees/UploadDiploma.vue';
import PaymentDetailsModal from '../Attendees/PaymentDetailsModal.vue';

defineOptions({
    layout: AuthenticatedLayout
})

const { alertState, success, errorA, warning, hideAlert } = useAlert()
const showCreateDrawer = ref(false);
const showEditDrawer = ref(false);
const showUploadDiploma = ref(false);
const selectedItem = ref(null);

// INICIOS FILTROS REDIRECCION
// agregar un filtro nuevo:
// 1. agrega ref aquí
// 2. agregalo a activeFilters (lo que ven los componentes)
// 3. agregalo a filtersPayload (lo que recibe el controlador con prefijo _filters_)
// 
const event_id = ref(route().params.event_id ?? '')
const did_attend = ref(route().params.did_attend ?? '')
// const search  = ref(route().params.search ?? '')   // ejemplo

/**
 * objeto de filtros que se pasa a los componentes hijos (EditAttendee, UploadDiploma por ejemplo)
 * y que también usa DataTable para mantener los query params en la URL
 */
const activeFilters = computed(() => ({
    event_id: event_id.value,
    did_attend: did_attend.value,
    // search:  search.value,
}))

/**
 * Objeto con los mismos filtros pero con el prefijo _filters_ que espera el controlador
 * en los métodos que hacen redirect (update, delete, uploadDiploma, changeDidAttend).
 * Se usa en router.delete, router.get, etc.
 */
const filtersPayload = computed(() => ({
    _filters_event_id: event_id.value !== '' ? event_id.value : undefined,
    _filters_did_attend: did_attend.value !== '' ? did_attend.value : undefined,
    // _filters_search:  search.value !== '' ? search.value : undefined,
}))

const hasActiveFilters = computed(() =>
    Object.values(activeFilters.value).some(v => v !== '' && v !== null && v !== undefined)
)

const clearFilters = () => {
    event_id.value = ''
    did_attend.value = ''
    // search.value  = ''
}
// FINAL FILTROS REDIRECCION

const truncate = (text, max = 30) => {
    if (!text) return ''
    return text.length > max ? text.substring(0, max) + '…' : text
}

const props = defineProps({
    attendees: {
        type: Object,
        default: () => ({})
    },
    allEvents: {
        type: Array,
        default: () => []
    },
    activeEvents: {
        type: Array,
        default: () => []
    },
    eventName: {
        type: String,
        default: ''
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

const showPaymentDetails = ref(false);
const paymentDetails = ref(null);

const openPaymentDetails = (attendee) => {
    paymentDetails.value = attendee.payments?.[0] ?? null;
    showPaymentDetails.value = true;
}

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

const handleOnCreate = () => {
    showCreateDrawer.value = true;
}

const handleOnEdit = (attendee) => {
    selectedItem.value = attendee;
    showEditDrawer.value = true;
}

const onChangeAttend = (attendee) => {
    router.get(route('attendees.change-attend', attendee.id),
        filtersPayload.value,   // ADICION DE FILTROS
        { preserveScroll: true }
    )
}

const handleOnDelete = (attendeeId) => {
    warning('¿Confirma que desea eliminar a este asistente? Esta acción no se puede deshacer.', {
        title: 'Eliminar registro',
        buttonText: 'Sí, eliminar',
        cancelText: 'Cancelar',
        onConfirm: () => {
            hideAlert();
            router.delete(route('attendees.delete', attendeeId), {
                data: filtersPayload.value,   // ADICION DE FILTROS
                preserveScroll: true,
            })
        }
    })
}

const openDiploma = (attendee) => {
    if (attendee.diploma_url) {
        window.open(attendee.diploma_url, '_blank');
    } else {
        selectedItem.value = attendee;
        showUploadDiploma.value = true;
    }
}

const onCreateSuccess = () => {
    success('Asistente registrado exitosamente');
    showCreateDrawer.value = false;
}

const onEditSuccess = () => {
    success('Asistente actualizado exitosamente');
    showEditDrawer.value = false;
    selectedItem.value = null;
}
</script>

<template>

    <Head title="Asistentes a sesiones academicas" />

    <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
        <div class="space-y-5">
            <div class="">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Asistentes a sesiones academicas</h3>
                <p class="text-sm text-gray-500">Administra los asistentes registrados a sesiones academicas desde esta
                    sección</p>
            </div>
            <DataTable :columns="[
                { label: 'Sesión Academica', key: 'event_name' },
                { label: 'Asistente', key: 'name' },
                { label: 'Telefono', key: 'phone' },
                { label: 'Ciudad/Estado', key: 'origin' },
                { label: 'Tipo de Usuario', key: 'person_type', align: 'center' },
                { label: 'Estatus de Pago', key: 'status', align: 'center' },
                { label: 'Asistencia', key: 'did_attend', align: 'center' },
            ]" :paginator="props.attendees" :searchable="true" :per-page-options="[10, 25, 50, 100]"
                :allow-create="true" :allow-actions="true" :allow-edit="true" :allow-delete="true"
                @create="handleOnCreate" @edit="handleOnEdit" @delete="handleOnDelete" :only="['attendees']"
                :filter-values="activeFilters">

                <template #filters>
                    <div class="flex flex-wrap items-center gap-3 px-4 py-3">

                        <!-- Sesión académica -->
                        <label for="per-page-select" class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            Sesión académica
                        </label>
                        <select v-model="event_id" class="rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-2 text-sm text-gray-700
                       dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200
                       focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500">
                            <option value="">Todas las sesiones</option>
                            <option v-for="e in allEvents" :key="e.id" :value="e.id">
                                {{ truncate(e.topic, 35) }}
                            </option>
                        </select>

                        <!-- Asistencia -->
                        <label for="per-page-select" class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            Asistencia
                        </label>
                        <select v-model="did_attend" class="rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-700
                       dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200
                       focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500">
                            <option value="">Todos</option>
                            <option :value="1">Sí asistió</option>
                            <option :value="0">No asistió</option>
                        </select>

                        <!-- Limpiar -->
                        <button v-if="hasActiveFilters" @click="clearFilters" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg border border-gray-300
                       dark:border-gray-700 text-sm text-gray-500 hover:text-red-500
                       hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Limpiar
                        </button>
                    </div>
                </template>

                <template #cell-date="{ item }">
                    {{ new Date(item.date).toLocaleDateString('en-GB') }}
                </template>

                <template #cell-origin="{ item }">
                    {{ item.city ? item.city + ', ' : '' }}{{ item.state || '' }}.
                </template>

                <template #cell-person_type="{ item }">
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium capitalize"
                        :class="item.person_type == 'member' ? 'bg-emerald-200 text-emerald-700' :
                            (item.person_type == 'resident') ? 'bg-sky-200 text-sky-700' : 'bg-gray-200 text-gray-700'">
                        {{ item.person_type === 'member' ? 'Miembro CMEC' : '' }}
                        {{ item.person_type === 'guest' ? 'Invitado' : '' }}
                        {{ item.person_type === 'resident' ? 'Residente' : '' }}
                    </span>
                </template>

                <template #cell-status="{ item }">
                    <span role="button" @click="openPaymentDetails(item)"
                        class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium capitalize cursor-pointer"
                        :class="item.status == 'pending'
                            ? 'bg-amber-200 text-amber-700 hover:bg-amber-300'
                            : item.status == 'paid'
                                ? 'bg-emerald-200 text-emerald-700 hover:bg-emerald-300'
                                : 'bg-sky-200 text-sky-700 hover:bg-sky-300'">
                        {{ item.status === 'paid' ? 'Pagado' : '' }}
                        {{ item.status === 'pending' ? 'Pendiente' : '' }}
                        {{ item.status === 'free' ? 'Gratis' : '' }}
                        {{ item.status === 'cancelled' ? 'Cancelado' : '' }}
                    </span>
                </template>

                <template #cell-did_attend="{ item }">
                    <span role="button" @click="onChangeAttend(item)"
                        class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium capitalize" :class="item.did_attend
                            ? 'bg-emerald-200 text-emerald-700 hover:bg-emerald-300'
                            : 'bg-orange-200 text-orange-700 hover:bg-orange-300'">
                        {{ item.did_attend ? 'Sí' : 'No' }}
                    </span>
                </template>

                <template #cell-event_name="{ item }">
                    <span :title="item.event?.topic || item.event?.name" class="block max-w-[200px] truncate">
                        {{ (item.event?.topic || item.event?.name) ?? 'N/A' }}
                    </span>
                </template>

                <template #actionButtons="{ item }">
                    <button :title="item.diploma_url ? 'Ver diploma PDF' : 'Subir diploma'" @click="openDiploma(item)"
                        :disabled="!item.did_attend" :class="item.diploma_url
                            ? 'bg-amber-500 text-white border-amber-500 hover:bg-amber-600'
                            : 'bg-transparent text-gray-300 border-gray-200 hover:bg-gray-100 hover:text-gray-500',
                            item.did_attend ? '' : 'disabled text-white'"
                        class="p-2 rounded-lg transition-colors border">
                        <svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4">
                            <g fill="none">
                                <path
                                    d="M24 0v24H0V0zM12.593 23.258l-.011.002-.071.035-.02.004-.014-.004-.071-.035q-.016-.005-.024.005l-.004.01-.017.428.005.02.01.013.104.074.015.004.012-.004.104-.074.012-.016.004-.017-.017-.427q-.004-.016-.017-.018m.265-.113-.013.002-.185.093-.01.01-.003.011.018.43.005.012.008.007.201.093q.019.005.029-.008l.004-.014-.034-.614q-.005-.018-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014-.034.614q.001.018.017.024l.015-.002.201-.093.01-.008.004-.011.017-.43-.003-.012-.01-.01z">
                                </path>
                                <path fill="currentColor"
                                    d="M13.586 2a2 2 0 0 1 1.284.467l.13.119L19.414 7a2 2 0 0 1 .578 1.238l.008.176V20a2 2 0 0 1-1.85 1.995L18 22H6a2 2 0 0 1-1.995-1.85L4 20V4a2 2 0 0 1 1.85-1.995L6 2zM12 4H6v16h12V10h-4.5A1.5 1.5 0 0 1 12 8.5zm-.707 7.173a1 1 0 0 1 1.32-.083l.094.083 2.121 2.121a1 1 0 0 1-1.32 1.498l-.094-.084-.414-.414V17a1 1 0 0 1-1.993.117L11 17v-2.706l-.414.414a1 1 0 0 1-1.498-1.32l.084-.094zM14 4.414V8h3.586z">
                                </path>
                            </g>
                        </svg>
                    </button>
                </template>
            </DataTable>

            <CreateAttendee :show="showCreateDrawer" :event-name="props.eventName" :events="props.activeEvents"
                :errors="props.errors" @close="showCreateDrawer = false" @success="onCreateSuccess" />

            <EditAttendee :show="showEditDrawer" :event-name="props.eventName" :data="selectedItem"
                :events="props.allEvents" :errors="props.errors" :active-filters="activeFilters"
                @close="showEditDrawer = false" @success="onEditSuccess" />

            <UploadDiploma :show="showUploadDiploma" :attendee="selectedItem ?? {}"
                :diploma-url="selectedItem?.diploma_url ?? ''" :active-filters="activeFilters"
                @close="showUploadDiploma = false" />

            <PaymentDetailsModal :show="showPaymentDetails" :max-width="'lg'" :payment-details="paymentDetails"
                @close="showPaymentDetails = false" />
        </div>
    </div>

    <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
        :buttonText="alertState.buttonText" :cancelText="alertState.cancelText"
        @confirm="alertState.onConfirm ? alertState.onConfirm() : hideAlert()"
        @cancel="alertState.onCancel ? alertState.onCancel() : hideAlert()" @close="hideAlert()" />
</template>