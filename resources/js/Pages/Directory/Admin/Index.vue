<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useAlert } from '@/composables/useAlert';
import { watch, ref } from 'vue';
import DataTable from '@/Components/DataTable.vue';
import SeeDetails from './SeeDetails.vue';
import Alerta from '@/Components/Alerta.vue';
import { Head, router } from '@inertiajs/vue3';
defineOptions({
    layout: AuthenticatedLayout
})

const props = defineProps({
    members: {
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

const { alertState, success, errorA, warning, hideAlert, info } = useAlert()

watch(() => props.flash, (value) => {
    if (!value) return

    if (value.success) success(value.success)
    if (value.warning) warning(value.warning)
    if (value.error) errorA(value.error)
}, {immediate: true, deep: true})

const showDetails = ref(false)
const selectedItem = ref(null)

const handleOnView = (directory) => {
    selectedItem.value = directory;
    showDetails.value = true;   
}

const handleOnDelete = (member) => {
    warning('¿Confirma que desea desactivar este directorio?.', {
        title: 'Desactivar directorio',
        buttonText: 'Sí, desactivar',
        cancelText: 'Cancelar',
        onConfirm: () => {
            hideAlert();
            router.delete(route('directory.delete', member))
        }
    })
}

const handleOnRestore = (member) => {
    info('¿Confirma que desea activar este directorio?.', {
        title: 'Activar directorio',
        buttonText: 'Sí, activar',
        cancelText: 'Cancelar',
        onConfirm: () => {
            hideAlert();
            router.put(route('directory.restore', member));
        }
    })
}

const truncate = (text, max = 50) => {
    if (!text) return '';
    return text.length > max ? text.substring(0, max) + '...' : text;
}

const lower = (value) => {
    return value?.toLowerCase();
}

const formattedDate = (item) => {
    if (item.sessions?.[0]?.date) {
        let fullDate = item.sessions?.[0]?.date + 'T00:00:00';
        const date = new Date(fullDate);
        const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
        return new Intl.DateTimeFormat('es-MX', options).format(date);
    }
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

<template>
    <Head title="Directorio" />
    <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
        <div class="space-y-5">
            <div class="">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Directorio Médico</h3>
                <p class="text-sm text-gray-500">Administra los registros del directorio desde esta sección</p>
            </div>

            <DataTable
                :columns="[
                    { label: 'Cmec id', key: 'cmec_member_id' },
                    { label: 'Nombre', key: 'name' },
                    { label: 'Especialidad', key: 'specialty' },
                    { label: 'Ciudad', key: 'city' },
                    { label: 'Estado', key: 'state' },
                    { label: 'No. Consultorios', key: 'clinics_number', align: 'center'},
                    { label: 'Hospital', key: 'clinic_hospital' },
                    { label: 'Telefono', key: 'clinic_phone' },
                    { label: 'Horario', key: 'clinic_schedule' },
                ]"
                :filter-values="filters"
                :paginator="props.members"
                :searchable="true"
                :per-page-options="[5, 10, 15]"
                :allow-create="false"
                :allow-actions="true"
                :allow-edit="false"
                :allow-delete="true"
                @delete="handleOnDelete"
                @restore="handleOnRestore"
                :only="['members']"
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

                <template #cell-specialty="{item}">
                    {{ truncate(item.directory?.specialty, 35) }}
                </template>

                <template #cell-clinics_number="{item}">
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 ml-1">
                        {{ item.clinics.length }}
                    </span>
                    
                </template>

                <template #cell-clinic_hospital="{item}">
                    {{ item.clinics?.[0]?.hospital_name }}
                </template>

                <template #cell-clinic_phone="{item}">
                    {{ item.clinics?.[0]?.phone }}
                </template>

                <template #cell-clinic_schedule="{item}">
                    {{ truncate(item.clinics?.[0]?.schedule, 30) }}
                </template>

                <template #actionButtons="{ item }">
                    <button title="Ver" @click="handleOnView(item)"
                        class="p-2 rounded-lg bg-blue-50 text-blue-500 transition-colors border border-blue-100 hover:bg-blue-600 hover:text-white hover:border-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                            viewBox="0 0 576 512" width="18" heigth="18">
                            <path
                                d="M572.52 241.4C518.29 135.59 410.93 64 288 64S57.68 135.64 3.48 241.41a32.35 32.35 0 0 0 0 29.19C57.71 376.41 165.07 448 288 448s230.32-71.64 284.52-177.41a32.35 32.35 0 0 0 0-29.19zM288 400a144 144 0 1 1 144-144a143.93 143.93 0 0 1-144 144zm0-240a95.31 95.31 0 0 0-25.31 3.79a47.85 47.85 0 0 1-66.9 66.9A95.78 95.78 0 1 0 288 160z"
                                fill="currentColor"></path>
                        </svg>
                    </button>
                </template>
            </DataTable>

            <SeeDetails
                :show="showDetails"
                :data="selectedItem"
                :errors="props.errors"
                @close="showDetails = false"
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