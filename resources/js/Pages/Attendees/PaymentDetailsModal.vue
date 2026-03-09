<script setup>
import { computed } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    maxWidth: {
        type: String,
        default: 'md'
    },
    paymentDetails: {
        type: Object,
        default: () => ({})
    }
})

const emit = defineEmits(['close'])

const formatDate = (originalDate) => {
    let date = new Date(originalDate)
    let options = { day: '2-digit', month: 'long', year: 'numeric' }
    return new Intl.DateTimeFormat('es-MX', options).format(date)
}

const formatAmount = (amount) => {
    let options = { style: 'currency', currency: 'USD' }
    return new Intl.NumberFormat('en-US', options).format(amount)
}

const readableMethod = (paymentMethod) => {
    const methods = {
        cash: { label: 'Efectivo', icon: 'banknotes' },
        transfer: { label: 'Transferencia', icon: 'arrows' },
        debit_card: { label: 'Tarjeta de Débito', icon: 'card' },
        credit_card: { label: 'Tarjeta de Crédito', icon: 'card' },
        stripe: { label: 'En línea (Stripe)', icon: 'globe' }
    }
    return methods[paymentMethod] || { label: 'Desconocido', icon: 'question' }
}

const paymentMethod = computed(() => {
    return readableMethod(props.paymentDetails?.payment_method)
})

const close = () => {
    emit('close')
}

const maxWidthClass = computed(() => {
    const sizes = {
        sm: 'max-w-sm',
        md: 'max-w-md',
        lg: 'max-w-lg',
        xl: 'max-w-xl',
        '2xl': 'max-w-2xl'
    }
    return sizes[props.maxWidth] || 'max-w-md'
})
</script>

<template>
    <Teleport to="body">
        <Transition enter-active-class="duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="close" />

                <!-- Modal -->
                <Transition enter-active-class="duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0" leave-active-class="duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-4">
                    <div v-if="show"
                        :class="[maxWidthClass, 'relative w-full bg-white rounded-2xl shadow-2xl overflow-hidden']">
                        <!-- Header -->
                        <div class="px-6 pt-6 pb-4 border-b border-slate-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-50">
                                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold text-slate-900">
                                            Detalles del pago
                                        </h2>
                                        <p class="text-sm text-slate-500">
                                            Información de la transacción
                                        </p>
                                    </div>
                                </div>
                                <button @click="close"
                                    class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="px-6 py-4" v-if="paymentDetails">
                            <div class="space-y-4">
                                <!-- Método de pago -->
                                <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center w-10 h-10 rounded-lg bg-white shadow-sm">
                                            <!-- Cash icon -->
                                            <svg v-if="paymentMethod.icon === 'banknotes'"
                                                class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            <!-- Card icon -->
                                            <svg v-else-if="paymentMethod.icon === 'card'"
                                                class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                            </svg>
                                            <!-- Transfer icon -->
                                            <svg v-else-if="paymentMethod.icon === 'arrows'"
                                                class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                            </svg>
                                            <!-- Globe icon (Stripe) -->
                                            <svg v-else-if="paymentMethod.icon === 'globe'"
                                                class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                            </svg>
                                            <!-- Default icon -->
                                            <svg v-else class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">
                                                Método de pago
                                            </p>
                                            <p class="text-sm font-semibold text-slate-900">
                                                {{ paymentMethod.label }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Grid de detalles -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <!-- Referencia -->
                                    <div class="p-4 rounded-xl border border-slate-200 overflow-hidden">
                                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">
                                            Referencia
                                        </p>
                                        <p class="text-sm font-medium text-slate-900 truncate"
                                            :title="paymentDetails.reference">
                                            {{ paymentDetails.reference || 'No aplica' }}
                                        </p>
                                    </div>

                                    <!-- Fecha -->
                                    <div class="p-4 rounded-xl border border-slate-200">
                                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">
                                            Fecha de pago
                                        </p>
                                        <p class="text-sm font-medium text-slate-900">
                                            {{ formatDate(paymentDetails.created_at) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Cantidad pagada (destacada) -->
                                <div class="p-5 rounded-xl bg-emerald-50 border border-emerald-100">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p
                                                class="text-xs font-medium text-emerald-700 uppercase tracking-wide mb-1">
                                                Cantidad pagada
                                            </p>
                                            <p class="text-2xl font-bold text-emerald-700">
                                                {{ formatAmount(paymentDetails.amount) }}
                                            </p>
                                        </div>
                                        <div
                                            class="flex items-center justify-center w-12 h-12 rounded-full bg-emerald-100">
                                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty state -->
                        <div v-else class="p-6">
                            <div class="flex flex-col items-center justify-center py-8 text-center">
                                <div class="flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-slate-900 mb-1">
                                    Sin información
                                </p>
                                <p class="text-sm text-slate-500">
                                    No hay pagos relacionados
                                </p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                            <div class="flex justify-end">
                                <button @click="close"
                                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-medium shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors">
                                    Aceptar
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
