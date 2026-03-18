<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3'
import { useAlert } from '@/composables/useAlert'
import { onMounted, ref } from 'vue';
import Alerta from '@/Components/Alerta.vue';
import DataTable from '@/Components/DataTable.vue';

defineOptions({ layout: AuthenticatedLayout })

const { alertState, success, errorA, warning, hideAlert, info } = useAlert()

const props = defineProps({
    members: { type: Object, default: () => ({}) },
    flash: { type: Object, default: () => ({}) },
    errors: { type: Object, default: () => ({}) },
    auth: { type: Object, default: () => ({}) },
})

const page = usePage();

onMounted(() => {
    if (page.props.success || props.flash.success) success(page.props.success || props.flash.success)
    if (page.props.error || props.flash.error) errorA(page.props.error || props.flash.error)
    if (page.props.warning || props.flash.warning) warning(page.props.warning || props.flash.warning)
})

// ----------------------------------
// Handlers
// ----------------------------------

const handleOnCreate = () => router.get(route('members.new'))

const handleOnEdit = (member) => router.get(route('members.edit', member.id), {}, { preserveState: false })

const handleOnDelete = (memberId) => {
    warning('¿Confirma que desea eliminar este miembro?', {
        title: 'Eliminar miembro',
        buttonText: 'Sí, eliminar',
        cancelText: 'Cancelar',
        onConfirm: () => {
            hideAlert()
            router.delete(route('members.delete', memberId))
        }
    })
}

const handleOnRestore = (memberId) => {
    info('¿Confirma que desea restaurar este miembro?', {
        title: 'Restaurar miembro',
        buttonText: 'Sí, restaurar',
        cancelText: 'Cancelar',
        onConfirm: () => {
            hideAlert()
            router.put(route('members.restore', memberId))
        }
    })
}

// ----------------------------------
// Filtros
// ----------------------------------

const status = ref(route().params.status ?? '')
const filters = { status }
const hasActiveFilters = () => status.value
const clearFilters = () => { status.value = '' }

// ----------------------------------
// Helpers
// ----------------------------------

const formatDate = (dateStr) => {
    if (!dateStr) return '—'
    const parts = dateStr.split(/[T ]/)[0].split('-')
    return new Date(parts[0], parts[1] - 1, parts[2]).toLocaleDateString('es-MX', {
        day: '2-digit', month: 'short', year: 'numeric'
    })
}

const isExpired = (dateStr) => {
    if (!dateStr) return false
    return new Date(dateStr.split(/[T ]/)[0]) < new Date()
}
</script>

<template>

    <Head title="Miembros" />

    <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
        <div class="space-y-5">

            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Miembros</h3>
                <p class="text-sm text-gray-500">Administra los miembros registrados de la asociación</p>
            </div>

            <DataTable :columns="[
                { label: 'ID / CMEC', key: 'cmec_member_id' },
                { label: 'Nombre', key: 'name' },
                { label: 'Correo', key: 'email' },
                { label: 'Teléfono', key: 'phone' },
                { label: 'Ciudad', key: 'city' },
                { label: 'Inscripción', key: 'inscription_date' },
                { label: 'Vencimiento', key: 'expiration_date' },
            ]" :filter-values="filters" :paginator="props.members" :searchable="true"
                :per-page-options="[5, 10, 15, 25]" :allow-create="true" :allow-actions="true" :allow-edit="true"
                :allow-delete="true" @create="handleOnCreate" @edit="handleOnEdit" @restore="handleOnRestore"
                @delete="handleOnDelete" :only="['members']">

                <!-- Filtros -->
                <template #filters>
                    <div class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex flex-wrap items-center gap-3">
                            <label class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Estatus</label>
                            <select v-model="status"
                                class="rounded-lg border max-w-sm border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                <option value="all">Todos</option>
                                <option value="">Activos</option>
                                <option value="trashed">Eliminados</option>
                            </select>
                            <button v-if="hasActiveFilters()" @click="clearFilters"
                                class="inline-flex items-center gap-1.5 h-10 px-3 rounded-lg border border-gray-300 dark:border-gray-700 text-sm text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-red-500 transition-colors">
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
                <template #cell-cmec_member_id="{ item }">
                    <span class="font-mono text-xs text-gray-500 dark:text-gray-400">
                        {{ item.cmec_member_id || '—' }}
                    </span>
                </template>

                <template #cell-name="{ item }">
                    <div class="flex items-center gap-2">
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 text-xs font-bold">
                            {{ item.name.charAt(0).toUpperCase() }}{{ item.last_name.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90 whitespace-nowrap">
                                {{ item.name }} {{ item.last_name }}
                            </p>
                            <p class="text-xs text-gray-400">{{ item.hospital || '' }}</p>
                        </div>
                    </div>
                </template>

                <template #cell-email="{ item }">
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ item.email }}</span>
                </template>

                <template #cell-phone="{ item }">
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ item.phone }}</span>
                </template>

                <template #cell-city="{ item }">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ item.city }}{{ item.state ? ', ' + item.state : '' }}
                    </span>
                </template>

                <template #cell-inscription_date="{ item }">
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ formatDate(item.inscription_date)
                        }}</span>
                </template>

                <template #cell-expiration_date="{ item }">
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                        :class="isExpired(item.expiration_date)
                            ? 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400'
                            : 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400'">
                        <span class="h-1.5 w-1.5 rounded-full"
                            :class="isExpired(item.expiration_date) ? 'bg-red-500' : 'bg-green-500'" />
                        {{ formatDate(item.expiration_date) }}
                    </span>
                </template>

            </DataTable>
        </div>
    </div>

    <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
        :buttonText="alertState.buttonText" :cancelText="alertState.cancelText"
        @confirm="alertState.onConfirm ? alertState.onConfirm() : hideAlert()"
        @cancel="alertState.onCancel ? alertState.onCancel() : hideAlert()" @close="hideAlert()" />
</template>