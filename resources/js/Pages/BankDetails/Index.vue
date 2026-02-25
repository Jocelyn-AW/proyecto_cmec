<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Alerta from '@/Components/Alerta.vue'
import { useAlert } from '@/composables/useAlert'
import BankDetailsCreate from './BankDetailsCreate.vue'
import BankDetailsEdit from './BankDetailsEdit.vue'
import BankDetailsListItem from './BankDetailsListItem.vue'

defineOptions({
    layout: AuthenticatedLayout
})

const props = defineProps({
    bankDetails: {
        type: Array,
        default: () => []
    }
})

const { alertState, success, errorA, warning, info } = useAlert()

const showCreateModal = ref(false)
const editRefs = ref({})

const handleConfirm = () => {
    alertState.value.onConfirm?.()
    alertState.value.show = false
}

const handleCancel = () => {
    alertState.value.onCancel?.()
    alertState.value.show = false
}

const deleteBankDetail = (id) => {
    warning('¿Estás seguro de eliminar esta cuenta bancaria?', {
        title: 'Eliminar cuenta',
        buttonText: 'Eliminar',
        cancelText: 'Cancelar',
        onConfirm: () => {
            router.delete(`/bankdetails/${id}`, {
                preserveScroll: true,
                onSuccess: () => success('Cuenta bancaria eliminada correctamente'),
                onError: () => errorA('Error al eliminar la cuenta bancaria')
            })
        }
    })
}

const openEdit = (item) => {
    Object.entries(editRefs.value).forEach(([id, comp]) => {
        if (Number(id) !== item.id) comp?.close()
    })
    editRefs.value[item.id]?.open()
}

const handleUpdated = () => {
    router.reload({ only: ['bankDetails'], preserveScroll: true })
    success('Cuenta bancaria actualizada correctamente')
}
</script>

<template>
    <div>

        <Head title="Cuentas Bancarias" />

        <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6 lg:p-8">
            <div class="space-y-5">

                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Cuentas Bancarias</h3>
                    <p class="text-sm text-gray-500">Referencias de pago registradas</p>
                </div>

                <div
                    class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">

                    <div
                        class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">
                                Información bancaria guardada
                            </h4>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ props.bankDetails.length }}
                                {{ props.bankDetails.length === 1 ? 'cuenta registrada' : 'cuentas registradas' }}
                            </p>
                        </div>
                        <button @click="showCreateModal = true"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 shadow-sm transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                class="w-4 h-4">
                                <path
                                    d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                            </svg>
                            Agregar cuenta
                        </button>
                    </div>

                    <!-- LISTA -->
                    <div v-if="props.bankDetails.length > 0">
                        <div v-for="(item, index) in props.bankDetails" :key="item.id">

                            <!-- componente lista -->
                            <BankDetailsListItem :item="item" :is-last="index === props.bankDetails.length - 1"
                                @edit="openEdit" @delete="deleteBankDetail" />

                            <!-- acordeon -->
                            <BankDetailsEdit :ref="el => { if (el) editRefs[item.id] = el }" :item="item"
                                @updated="handleUpdated" @error="(msg) => errorA(msg)"
                                @warning="(msg) => warning(msg)" />

                        </div>
                    </div>

                    <!-- no hay nada -->
                    <div v-else class="flex flex-col items-center justify-center gap-3 py-16">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-10 h-10 text-gray-300 dark:text-gray-600">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                        </svg>
                        <p class="text-sm text-gray-400 dark:text-gray-500">No hay cuentas bancarias registradas</p>
                        <button @click="showCreateModal = true"
                            class="inline-flex h-10 shrink-0 justify-center items-center gap-2 rounded-lg bg-green-600 text-white px-4 text-sm font-medium hover:bg-green-700 transition-colors shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                class="w-4 h-4">
                                <path
                                    d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                            </svg>
                            Agregar una cuenta bancaria
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- CREAR MODAL -->
        <BankDetailsCreate :show="showCreateModal" @close="showCreateModal = false"
            @created="success('Cuenta bancaria creada correctamente')" @warning="(msg) => warning(msg)"
            @error="(msg) => errorA(msg)" @info="(msg) => info(msg)" />

        <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
            :buttonText="alertState.buttonText" :cancelText="alertState.cancelText" @confirm="handleConfirm"
            @cancel="handleCancel" @close="alertState.show = false" />
    </div>
</template>
