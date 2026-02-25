<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Alerta from '@/Components/Alerta.vue'
import { useAlert } from '@/composables/useAlert'
import BankDetailsCreate from './BankDetailsCreate.vue'

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

const getBankColor = (bank) => {
    const map = {
        'bbva': { bg: 'bg-blue-100 dark:bg-blue-500/20', text: 'text-blue-700 dark:text-blue-300' },
        'banamex': { bg: 'bg-red-100 dark:bg-red-500/20', text: 'text-red-700 dark:text-red-300' },
        'citibanamex': { bg: 'bg-red-100 dark:bg-red-500/20', text: 'text-red-700 dark:text-red-300' },
        'santander': { bg: 'bg-red-100 dark:bg-red-500/20', text: 'text-red-700 dark:text-red-300' },
        'banorte': { bg: 'bg-orange-100 dark:bg-orange-500/20', text: 'text-orange-700 dark:text-orange-300' },
        'hsbc': { bg: 'bg-red-100 dark:bg-red-500/20', text: 'text-red-700 dark:text-red-300' },
        'scotiabank': { bg: 'bg-red-100 dark:bg-red-500/20', text: 'text-red-700 dark:text-red-300' },
        'inbursa': { bg: 'bg-yellow-100 dark:bg-yellow-500/20', text: 'text-yellow-700 dark:text-yellow-300' },
        'afirme': { bg: 'bg-green-100 dark:bg-green-500/20', text: 'text-green-700 dark:text-green-300' },
    }
    return map[bank?.toLowerCase()] ?? { bg: 'bg-gray-100 dark:bg-gray-700', text: 'text-gray-600 dark:text-gray-300' }
}

const getBankInitials = (bank) => {
    if (!bank) return '?'
    const words = bank.trim().split(' ')
    if (words.length >= 2) return (words[0][0] + words[1][0]).toUpperCase()
    return bank.slice(0, 2).toUpperCase()
}

const maskAccount = (value) => {
    if (!value) return null
    const str = String(value)
    if (str.length <= 4) return str
    return '•••• ' + str.slice(-4)
}

const maskClabe = (value) => {
    if (!value) return null
    const str = String(value)
    if (str.length < 8) return str
    return str.slice(0, 3) + ' ' + '•'.repeat(str.length - 7) + ' ' + str.slice(-4)
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
                        <div v-for="(item, index) in props.bankDetails" :key="item.id"
                            class="flex items-start gap-4 px-6 py-5 transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]"
                            :class="index !== props.bankDetails.length - 1 ? 'border-b border-gray-100 dark:border-gray-800' : ''">
                            <!-- Ícono banco -->
                            <div class="mt-0.5 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-xs font-bold"
                                :class="[getBankColor(item.bank).bg, getBankColor(item.bank).text]">
                                {{ getBankInitials(item.bank) }}
                            </div>

                            <!-- datos -->
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5 mb-1.5">
                                    <span class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ item.bank
                                    }}</span>
                                    <span v-if="item.beneficiary" class="text-xs text-gray-500 dark:text-gray-400">· {{
                                        item.beneficiary }}</span>
                                </div>
                                <div class="flex flex-wrap gap-x-5 gap-y-1 mb-1.5">
                                    <div v-if="item.account_number" class="flex items-center gap-1.5">
                                        <span class="text-xs text-gray-400 dark:text-gray-500">Cuenta</span>
                                        <span
                                            class="text-xs font-mono font-medium text-gray-700 dark:text-gray-300 tracking-widest">{{
                                                maskAccount(item.account_number) }}</span>
                                    </div>
                                    <div v-if="item.clabe_number" class="flex items-center gap-1.5">
                                        <span class="text-xs text-gray-400 dark:text-gray-500">CLABE</span>
                                        <span
                                            class="text-xs font-mono font-medium text-gray-700 dark:text-gray-300 tracking-widest">{{
                                                maskClabe(item.clabe_number) }}</span>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-x-4 gap-y-1">
                                    <div v-if="item.reference" class="flex items-center gap-1">
                                        <span class="text-xs text-gray-400 dark:text-gray-500">Ref.</span>
                                        <span class="text-xs font-medium text-gray-600 dark:text-gray-300">{{
                                            item.reference }}</span>
                                    </div>
                                    <div v-if="item.subsidiary" class="flex items-center gap-1">
                                        <span class="text-xs text-gray-400 dark:text-gray-500">Sucursal</span>
                                        <span class="text-xs text-gray-600 dark:text-gray-400">{{ item.subsidiary
                                        }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- acciones -->
                            <div class="flex flex-col items-end gap-2 shrink-0 ml-2">
                                <div class="flex items-center gap-2">
                                    <button title="Editar"
                                        class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline transition-colors">
                                        Editar
                                    </button>
                                    <span class="text-gray-300 dark:text-gray-700 select-none">|</span>
                                    <button @click="deleteBankDetail(item.id)" title="Eliminar"
                                        class="text-xs font-medium text-red-500 dark:text-red-400 hover:underline transition-colors">
                                        Eliminar
                                    </button>
                                </div>
                                <div v-if="item.updated_by" class="flex items-center gap-1 mt-auto">
                                    <p
                                        class="inline-flex shrink-0 items-center justify-center rounded-full text-gray-500 text-xs leading-none">
                                        Último editor: </p>
                                    <div
                                        class="inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs font-semibold leading-none">
                                        {{ item.updated_by.name?.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">{{
                                        item.updated_by.name }}</span>
                                </div>
                            </div>
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
