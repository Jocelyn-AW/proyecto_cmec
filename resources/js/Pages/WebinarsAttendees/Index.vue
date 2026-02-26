<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { Head, router, usePage } from '@inertiajs/vue3'
import { useAlert } from '@/composables/useAlert'
import { computed, onMounted } from 'vue';
import Alerta from '@/Components/Alerta.vue';
import Drawer from '@/Components/Drawer.vue';
import { ref, reactive } from 'vue';
import CreateAttendee from './CreateAttendee.vue';
import EditAttendee from './EditAttendee.vue';
import UploadDiploma from './UploadDiploma.vue';

defineOptions({
    layout: AuthenticatedLayout
})

const { alertState, success, errorA, warning, hideAlert } = useAlert()
const showCreateDrawer = ref(false);
const showEditDrawer = ref(false);
const showUploadDiploma = ref(false);
const selectedItem = ref(null);

const props = defineProps({
    attendees: {
        type: Object,
        default: () => ({})
    },
    events: {
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

const generateRandomString = (length = 5) => {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
};

const handleOnCreate = () => {
    showCreateDrawer.value = true;
}

const handleOnEdit = (attendee) => {
    selectedItem.value = attendee;
    showEditDrawer.value = true;
}

const handleOnDelete = (attendeeId) => {
    warning('¿Confirma que desea eliminar a este asistente? Esta acción no se puede deshacer.', {
        title: 'Eliminar registro',
        buttonText: 'Sí, eliminar',
        cancelText: 'Cancelar',
        onConfirm: () => {
            hideAlert();
            router.delete(route('attendees.delete', attendeeId));
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

    <Head title="Asistentes a webinars" />

    <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
        <div class="space-y-5">
            <div class="">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Asistentes a webinars</h3>
                <p class="text-sm text-gray-500">Administra los asistentes registrados a webinars desde esta sección</p>
            </div>
            <DataTable :columns="[
                { label: 'ID', key: 'id' },
                { label: 'Webinar', key: 'event_name' },
                { label: 'Asistente', key: 'name' },
                { label: 'Telefono', key: 'phone' },
                { label: 'Ciudad/Estado', key: 'origin' },
                { label: 'Tipo de Usuario', key: 'person_type', align: 'center' },
                { label: 'Estatus de Pago', key: 'status', align: 'center' },
                { label: 'Asistencia', key: 'did_attend', align: 'center' },
            ]" :paginator="props.attendees" :searchable="true" :per-page-options="[10, 25, 50, 100]"
                :allow-create="true" :allow-actions="true" :allow-edit="true" :allow-delete="true"
                @create="handleOnCreate" @edit="handleOnEdit" @delete="handleOnDelete" :only="['attendees']">

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
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium capitalize"
                        :class="item.status == 'pending' ? 'bg-amber-200 text-amber-700' :
                            (item.status == 'paid') ? 'bg-emerald-200 text-emerald-700' : 'bg-sky-200 text-sky-700'">
                        {{ item.status === 'paid' ? 'Pagado' : '' }}
                        {{ item.status === 'pending' ? 'Pendiente' : '' }}
                        {{ item.status === 'free' ? 'Gratis' : '' }}
                    </span>
                </template>

                <template #cell-did_attend="{ item }">
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium capitalize"
                        :class="item.did_attend ? 'bg-emerald-200 text-emerald-700' : 'bg-orange-200 text-orange-700'">
                        {{ item.did_attend ? 'Sí' : 'No' }}
                    </span>
                </template>

                <template #cell-event_name="{ item }">
                    {{ (item.event.topic || item.event.name) ?? 'N/A' }}

                </template>

                <template #actionButtons="{ item }">
                    <button title="Ver diploma" @click="openDiploma(item)"
                        class="p-2 rounded-lg bg-amber-20 text-amber-500 hover:bg-amber-600 hover:text-white transition-colors border border-amber-100 hover:border-amber-600">
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
            <CreateAttendee :show="showCreateDrawer" :event-name="props.eventName" :events="props.events"
                :errors="props.errors" @close="showCreateDrawer = false" @success="onCreateSuccess" />
            <EditAttendee :show="showEditDrawer" :event-name="props.eventName" :data="selectedItem"
                :events="props.events" :errors="props.errors" @close="showEditDrawer = false"
                @success="onEditSuccess" />

            <UploadDiploma :show="showUploadDiploma" :attendee="selectedItem" @close="showUploadDiploma = false" />

        </div>
    </div>
    <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
        :buttonText="alertState.buttonText" :cancelText="alertState.cancelText"
        @confirm="alertState.onConfirm ? alertState.onConfirm() : hideAlert()"
        @cancel="alertState.onCancel ? alertState.onCancel() : hideAlert()" @close="hideAlert()" />

</template>