<script setup>
import { computed } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    maxWidth: {
        type: String,
        default: 'lg'
    },
    billingDetails: {
        type: Object,
        default: () => ({})
    }
})

const emit = defineEmits(['close'])

const close = () => emit('close')

const maxWidthClass = computed(() => {
    const sizes = {
        sm: 'max-w-sm',
        md: 'max-w-md',
        lg: 'max-w-lg',
        xl: 'max-w-xl',
        '2xl': 'max-w-2xl'
    }
    return sizes[props.maxWidth] || 'max-w-lg'
})

const personTypeLabel = computed(() => {
    return props.billingDetails?.person_type === 'moral'
        ? 'Persona Moral'
        : 'Persona Física'
})

const formatDate = (originalDate) => {
    if (!originalDate) return '—'
    const date = new Date(originalDate)
    const options = { day: '2-digit', month: 'long', year: 'numeric' }
    return new Intl.DateTimeFormat('es-MX', options).format(date)
}
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="close" />

                <!-- Modal -->
                <Transition
                    enter-active-class="duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-4"
                >
                    <div
                        v-if="show"
                        :class="[maxWidthClass, 'relative max-h-[80vh] mt-[5%] w-full bg-white rounded-2xl shadow-2xl overflow-auto']"
                    >
                        <!-- Header -->
                        <div class="px-6 pt-6 pb-4 border-b border-slate-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-50">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold text-slate-900">Datos de facturación</h2>
                                        <p class="text-sm text-slate-500">Información fiscal del receptor</p>
                                    </div>
                                </div>
                                <button
                                    @click="close"
                                    class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="px-6 py-4" v-if="billingDetails?.rfc">
                            <div class="space-y-4">

                                <!-- Tipo de persona + RFC destacados -->
                                <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-white shadow-sm">
                                            <!-- Persona física -->
                                            <svg v-if="billingDetails.person_type === 'fisica'" class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <!-- Persona moral -->
                                            <svg v-else class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Tipo de persona</p>
                                            <p class="text-sm font-semibold text-slate-900">{{ personTypeLabel }}</p>
                                        </div>
                                    </div>
                                    <!-- RFC badge -->
                                    <div class="text-right">
                                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">RFC</p>
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg bg-white border border-slate-200 text-sm font-mono font-semibold text-slate-900 shadow-sm tracking-wider">
                                            {{ billingDetails.rfc }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Nombre / Razón social -->
                                <div class="p-4 rounded-xl border border-slate-200">
                                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">
                                        {{ billingDetails.person_type === 'moral' ? 'Razón social' : 'Nombre completo' }}
                                    </p>
                                    <p class="text-sm font-semibold text-slate-900">{{ billingDetails.name }}</p>
                                </div>

                                <!-- Email -->
                                <div class="p-4 rounded-xl border border-slate-200">
                                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">
                                        Correo electrónico
                                    </p>
                                    <p class="text-sm font-medium text-slate-900">{{ billingDetails.email }}</p>
                                </div>

                                <!-- Uso CFDI + Régimen fiscal -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="p-4 rounded-xl border border-slate-200">
                                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Uso de CFDI</p>
                                        <p class="text-sm font-medium text-slate-900">{{ billingDetails.cfdi_use }}</p>
                                    </div>
                                    <div class="p-4 rounded-xl border border-slate-200">
                                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Régimen fiscal</p>
                                        <p class="text-sm font-medium text-slate-900">{{ billingDetails.tax_regime }}</p>
                                    </div>
                                </div>

                                <!-- Domicilio fiscal destacado -->
                                <div class="p-5 rounded-xl bg-blue-50 border border-blue-100">
                                    <div class="flex items-start gap-3">
                                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 shrink-0 mt-0.5">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-1">
                                                <p class="text-xs font-medium text-blue-700 uppercase tracking-wide">Domicilio fiscal</p>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-100 text-xs font-semibold text-blue-700">
                                                    CP {{ billingDetails.postal_code }}
                                                </span>
                                            </div>
                                            <p class="text-sm font-semibold text-blue-900 leading-snug">
                                                {{ billingDetails.address }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Fecha de registro -->
                                <div class="flex items-center justify-end gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-xs text-slate-400">
                                        Registrado el {{ formatDate(billingDetails.created_at) }}
                                    </p>
                                </div>

                            </div>
                        </div>

                        <!-- Empty state -->
                        <div v-else class="p-6">
                            <div class="flex flex-col items-center justify-center py-8 text-center">
                                <div class="flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-slate-900 mb-1">Sin datos fiscales</p>
                                <p class="text-sm text-slate-500">Este registro no tiene información de facturación</p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                            <div class="flex justify-end">
                                <button
                                    @click="close"
                                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                                >
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