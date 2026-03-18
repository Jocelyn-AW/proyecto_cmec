<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { useAlert } from '@/composables/useAlert'
import { computed, onMounted, ref, reactive, watch } from 'vue';
import DataTable from '@/Components/DataTable.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Alerta from '@/Components/Alerta.vue';

import UploadDiploma from './UploadDiploma.vue';
import PaymentDetailsModal from './PaymentDetailsModal.vue';
import InvoiceDetailsModal from './InvoiceDetailsModal.vue';
import CreateAttendee from './CreateAttendee.vue';
import EditAttendee from './EditAttendee.vue';

defineOptions({
    layout: AuthenticatedLayout
})

const props = defineProps({
    attendees: {
        type: Object,
        default: () => ({})
    },
    allEvents: {
        type: Object,
        default: () => ({})
    },
    activeEvents: {
        type: Object,
        default: () => ({})
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

const { alertState, success, errorA, warning, hideAlert, info } = useAlert()

watch(() => props.flash, (value) => {
    if (!value) return

    if (value.success) success(value.success)
    if (value.warning) warning(value.warning)
    if (value.error) errorA(value.error)
}, {immediate: true, deep: true})

const showCreateDrawer = ref(false);
const showEditDrawer = ref(false);
const showUploadDiploma = ref(false);
const showPaymentDetails = ref(false);
const showInvoiceDetails = ref(false);
const selectedItem = ref(null);
const paymentDetails = ref(null);
const invoiceDetails = ref(null);


const event_id = ref(route().params.event_id ?? '')
const did_attend = ref(route().params.did_attend ?? '')
const status = ref(route().params.status ?? '')

//String Formatters
const truncate = (text, max = 50) => {
    if (!text) return '';
    return text.length > max ? text.substring(0, max) + '...' : text;
}

const formatAmount =  (amount) => {
    let options = { style: 'currency', currency: 'USD' }
    return new Intl.NumberFormat('en-US', options).format(amount);
}

const formattedDate = (originalDate) => {
    if (originalDate) {
        let fullDate = originalDate.slice(0,10) + 'T00:00:00';
        const date = new Date(fullDate);
        const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
        return new Intl.DateTimeFormat('es-MX', options).format(date);
    }
}

//Computed props
const pluralName = computed(() => {
    if (props.eventName?.toLowerCase() == 'sesion academica'){
        return 'sesiones académicas'
    }
    return props.eventName?.toLowerCase() + 's'
})

const isConference = computed(() => {
    const conferenceEvents = ['Congreso', 'Precongreso', 'Transcongreso'];
    return conferenceEvents.includes(props.eventName);
})

const tableColumns = computed(() => {
    const base = [
        { label: props.eventName, key: 'event_name' },
        { label: 'Asistente', key: 'name' },
        { label: 'Tipo de Usuario', key: 'person_type' },
        { label: 'Telefono', key: 'phone' },
        { label: 'Ciudad/Estado', key: 'origin' },

        ...(isConference.value ? [
            { label: 'Fecha Nacimiento', key: 'birth_date' },
            { label: 'Atención especial', key: 'special_needs' },
        ] : []),

        { label: 'Estatus de Pago', key: 'status' },
        { label: 'Datos fiscales', key: 'invoice_data' },
        { label: 'Asistencia', key: 'did_attend' },
    ]

    return base
})

//Table Filters
const filters = {
    event_id: event_id, 
    did_attend: did_attend,
    status: status
}

const hasActiveFilters = () =>
    event_id.value || did_attend.value || status.value


const clearFilters = () => {
    event_id.value = ''
    did_attend.value = ''
    status.value = ''
}

//Table Events
const openDiploma = (attendee) => {
    console.log(attendee);
    
    if (attendee.diploma_url) {
        window.open(attendee.diploma_url, '_blank');
    } else {
        selectedItem.value = attendee;
        showUploadDiploma.value = true;
    }
}

const openPaymentDetails = (attendee) => {
    paymentDetails.value = attendee.payments?.[0] ?? null;    
    showPaymentDetails.value = true;
}

const openInvoiceDetails = (attendee) => {
    invoiceDetails.value = attendee.invoice_data ?? null;    
    showInvoiceDetails.value = true;
}

const onChangeAttend = (attendee) => {    
    router.get(route('attendees.change-attend', attendee.id))
}

const handleOnCreate = () => {
    showCreateDrawer.value = true;
}

const handleOnEdit = (attendee) => { 
    selectedItem.value = attendee;
    showEditDrawer.value = true;
}

const handleOnDelete = (attendeeId) => {
    warning('¿Confirma que desea eliminar a este asistente?.', {
        title: 'Eliminar registro',
        buttonText: 'Sí, eliminar',
        cancelText: 'Cancelar',
        onConfirm: () => {
            hideAlert();
            router.delete(route('attendees.delete', attendeeId));
        }
    })
}

const handleOnRestore = (attendeeId) => {
    info('¿Confirma que desea restaurar a este asistente?.', {
        title: 'Restaurar registro',
        buttonText: 'Sí, restaurar',
        cancelText: 'Cancelar',
        onConfirm: () => {
            hideAlert();
            router.put(route('attendees.restore', attendeeId));
        }
    })
}

</script>
<template>
    <Head :title="`Asistentes a  ${ pluralName }`" />

    <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
        <div class="space-y-5">
            <div class="">
                <h3 class="text-lg font-semibold text-sky-800 dark:text-white/90">Asistentes a <span class="capitalize">{{ pluralName }}</span></h3>
                <p class="text-sm text-gray-500">Administra los asistentes registrados a {{ pluralName }} desde esta sección</p>
            </div>
            <DataTable
                :columns="tableColumns"
                :filter-values="filters"
                :paginator="props.attendees"
                :searchable="true"
                :per-page-options="[5, 10, 25, 50, 100]"
                :allow-create="true"
                :allow-actions="true"
                :allow-edit="true"
                :allow-delete="true"
                @create="handleOnCreate"
                @edit="handleOnEdit"
                @delete="handleOnDelete"
                @restore="handleOnRestore"
                :only="['attendees']"
                >

                <template #filters
                    class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex flex-wrap items-center gap-3">
                            <!-- Evento -->
                            <label for="per-page-select" class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 capitalize" >
                                {{ pluralName }}
                            </label>
                            <select id="event_id" v-model="event_id"
                                class="rounded-lg border max-w-sm border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-700 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                                >
                                <option value="">Todos</option>
                                <option v-for="option in allEvents" :key="option.id" :value="option.id" >
                                    <span v-if="isConference">{{ truncate(option.name, 25) }}</span>
                                    <span v-else>{{ truncate(option.topic, 25) }}</span>
                                    
                                </option>
                            </select>
                            <!-- Asistencia -->
                            <label for="per-page-select" class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" >
                                Asistencia
                            </label>
                            <select id="attend" v-model="did_attend" 
                                class="rounded-lg border max-w-sm border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-700 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                                >
                                <option value="">Todos</option>
                                <option :value="0">No</option>
                                <option :value="1">Si</option> 
                            </select>
                            <!-- Estatus -->
                            <label for="per-page-select" class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" >
                                Estatus
                            </label>
                            <select id="status" v-model="status" 
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

                <!-- Nombre del Evento -->
                <template #cell-event_name="{ item }">
                    {{ (truncate(item.event.topic, 30) || truncate(item.event.name, 30)) ?? 'N/A' }}
                </template>

                <!-- Fecha de nacimiento  -->
                <template v-if="isConference" #cell-birth_date="{ item }">
                    {{ formattedDate(item.birth_date) }}
                </template>

                <!-- Ciudad -->
                <template #cell-origin="{ item }">
                    {{ item.city ? item.city + ', ' : '' }}{{ item.state || '' }}.
                </template>
                
                <!-- Tipo de asistentes -->
                <template v-if="isConference"  #cell-person_type="{ item }">
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium "
                        :class="item.person_type == 'member' ? 'bg-indigo-200 text-indigo-700':
                        (item.person_type == 'resident') ? 'bg-sky-200 text-sky-700': 
                        (item.person_type == 'surgeon') ? 'bg-teal-200 text-teal-700': 
                        (item.person_type == 'nurse') ? 'bg-slate-200 text-slate-700' :'bg-gray-200 text-gray-700'"
                        >
                        {{ item.person_type === 'member' ? 'Miembro CMEC' : '' }}
                        {{ item.person_type === 'guest' ? 'No miembro / Invitado' : '' }}
                        {{ item.person_type === 'resident' ? 'Residente / Medico General' : '' }}
                        {{ item.person_type === 'surgeon' ? 'Residente de cirugía' : '' }}
                        {{ item.person_type === 'nurse' ? 'Enfermero / Estudiante' : '' }}
                    </span>
                </template>

                <template v-else #cell-person_type="{ item }">
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium capitalize"
                        :class="item.person_type == 'member' ? 'bg-emerald-200 text-emerald-700' :
                        (item.person_type == 'resident') ? 'bg-sky-200 text-sky-700': 'bg-gray-200 text-gray-700'"
                        >
                        {{ item.person_type === 'member' ? 'Miembro CMEC' : '' }}
                        {{ item.person_type === 'guest' ? 'Invitado' : '' }}
                        {{ item.person_type === 'resident' ? 'Residente' : '' }}
                    </span>
                </template>
                <!--  -->

                <!-- Detalles de Pago -->
                <template #cell-status="{ item }">
                    <span
                        role="button" @click="openPaymentDetails(item)"
                        class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium capitalize"
                        :class="item.status == 'pending' ? 'bg-amber-200 text-amber-700 hover:bg-amber-300' : 
                        (item.status == 'paid') 
                        ? 'bg-emerald-200 text-emerald-700 hover:bg-emerald-300' 
                        : 'bg-sky-200 text-sky-700 hover:bg-sky-300'"
                        >
                        {{ item.status === 'paid' ? 'Pagado' : '' }}
                        {{ item.status === 'pending' ? 'Pendiente' : '' }}
                        {{ item.status === 'free' ? 'Gratis' : '' }}
                    </span>
                </template>

                <!-- Datos Fiscales -->
                <template #cell-invoice_data="{ item }">
                    <span
                        role="button" @click="openInvoiceDetails(item)"
                        class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium capitalize"
                        :class="
                        (item.invoice_data) 
                        ? 'bg-emerald-200 text-emerald-700 hover:bg-emerald-300' 
                        : 'bg-sky-200 text-sky-700 hover:bg-sky-300'"
                        >
                        {{ item.invoice_data ? 'Ver Datos' : 'Sin datos' }}
                    </span>
                </template>

                <!-- Cambiar asistencia -->
                <template #cell-did_attend="{ item }">
                    <span role="button" @click="onChangeAttend(item)" 
                        class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium capitalize"
                        :class="item.did_attend 
                            ? 'bg-emerald-200 text-emerald-700 hover:bg-emerald-300' 
                            : 'bg-orange-200 text-orange-700 hover:bg-orange-300',
                            item.deleted_at ? 'disabled border border-gray-600' : ''"
                    >
                        {{ item.did_attend ? 'Sí' : 'No' }}
                    </span>
                </template>

                <template #actionButtons="{ item }">
                    <button title="Ver diploma" @click="openDiploma(item)"
                        class="p-2 rounded-lg "
                        :disabled="!item.did_attend"
                        :class="
                            item.diploma_url == null || item.diploma_url == '' 
                            ? 'bg-indigo-30 text-indigo-500 hover:bg-indigo-600 hover:text-white transition-colors border border-indigo-100 hover:border-indigo-600' 
                            : 'bg-indigo-500 text-white hover:bg-indigo-600 hover:text-white transition-colors border border-indigo-500 hover:border-indigo-100',
                            (!item.did_attend || item.deleted_at) && (item.diploma_url == null) ? 'disabled' : ''"
                        >
                        <svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                            class="text-8xl w-4 h-4">
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

            <CreateAttendee 
                :show="showCreateDrawer"
                :event-name="props.eventName"
                :events="props.activeEvents"
                :errors="props.errors"
                @close="showCreateDrawer = false"
            />

            <EditAttendee 
                :show="showEditDrawer"
                :event-name="props.eventName"
                :data="selectedItem"
                :events="selectedItem?.deleted_at ? props.allEvents : props.activeEvents"
                :errors="props.errors"
                @close="showEditDrawer = false"
            />
            
            <UploadDiploma
                :show="showUploadDiploma"
                :max-width="'lg'"
                :attendee="selectedItem"
                @close="showUploadDiploma = false"
            />

            <PaymentDetailsModal
                :show="showPaymentDetails"
                :max-width="'lg'"
                @close="showPaymentDetails = false"
                :payment-details="paymentDetails"
            />

            <InvoiceDetailsModal
                :show="showInvoiceDetails"
                :max-width="'xl'"
                @close="showInvoiceDetails = false"
                :billing-details="invoiceDetails"
                />
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