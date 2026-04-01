<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Alerta from '@/Components/Alerta.vue'
import { useAlert } from '@/composables/useAlert'
import { useBillingData } from '@/composables/useBillingData'

defineOptions({ layout: AuthenticatedLayout })

const { alertState, success, errorA, warning, hideAlert } = useAlert()

const props = defineProps({
    invoiceData: { type: Object, default: () => null },
    member: { type: Object, default: () => null },
    errors: { type: Object, default: () => ({}) },
    flash: { type: Object, default: () => ({}) },
})

const page = usePage()
const isSubmitting = ref(false)

// ----------------------------------
// Form
// ----------------------------------

const form = ref({
    tax_name: props.invoiceData?.name ?? '',
    address: props.invoiceData?.address ?? '',
    rfc: props.invoiceData?.rfc ?? '',
    postal_code: props.invoiceData?.postal_code ?? '',
    tax_person_type: props.invoiceData?.person_type ?? '',
    cfdi_use: props.invoiceData?.cfdi_use ?? '',
    tax_regime: props.invoiceData?.tax_regime ?? '',
})

// ----------------------------------
// Billing composable
// ----------------------------------

const {
    tiposPersona,
    usosCfdiDisponibles,
    regimenesDisponibles,
    isRfcValid,
    isCpValid,
    formatRfc,
    setPersonType,
    setCfdiUse,
} = useBillingData(form)

// ----------------------------------
// Computed
// ----------------------------------

const isEditing = computed(() => !!props.invoiceData?.id)

const hasChanges = computed(() => {
    if (!props.invoiceData) return true
    return (
        form.value.tax_name !== (props.invoiceData.name ?? '') ||
        form.value.address !== (props.invoiceData.address ?? '') ||
        form.value.rfc !== (props.invoiceData.rfc ?? '') ||
        form.value.postal_code !== (props.invoiceData.postal_code ?? '') ||
        form.value.tax_person_type !== (props.invoiceData.person_type ?? '') ||
        form.value.cfdi_use !== (props.invoiceData.cfdi_use ?? '') ||
        form.value.tax_regime !== (props.invoiceData.tax_regime ?? '')
    )
})

const canSubmit = computed(() =>
    form.value.tax_name.trim() &&
    form.value.rfc.trim() &&
    form.value.postal_code.trim() &&
    form.value.tax_person_type &&
    form.value.cfdi_use &&
    form.value.tax_regime &&
    form.value.address.trim() &&
    isRfcValid.value &&
    isCpValid.value &&
    hasChanges.value
)


const cfdiLabel = computed(() => {
    if (!props.invoiceData?.cfdi_use) return ''
    const uso = usosCfdiDisponibles.value.find(u => u.codigo === props.invoiceData.cfdi_use)
    return uso ? `${uso.codigo} – ${uso.descripcion}` : props.invoiceData.cfdi_use
})

const regimenLabel = computed(() => {
    if (!props.invoiceData?.tax_regime) return ''
    const reg = regimenesDisponibles.value.find(r => r.codigo === props.invoiceData.tax_regime)
    return reg ? `${reg.codigo} – ${reg.descripcion}` : props.invoiceData.tax_regime
})

// ----------------------------------
// Flash / Errores
// ----------------------------------

onMounted(() => {
    if (page.props.success || props.flash?.success) success(page.props.success || props.flash.success)
    if (page.props.error || props.flash?.error) errorA(page.props.error || props.flash.error)
    if (page.props.warning || props.flash?.warning) warning(page.props.warning || props.flash.warning)
    if (Object.keys(props.errors).length > 0) errorA('Por favor revisa los campos marcados en rojo.')
})

watch(() => props.errors, (val) => {
    if (Object.keys(val).length > 0) errorA('Por favor revisa los campos marcados en rojo.')
}, { deep: true })

watch(() => props.flash, (val) => {
    if (!val) return
    if (val.success) success(val.success)
    if (val.error) errorA(val.error)
    if (val.warning) warning(val.warning)
}, { deep: true })

// ----------------------------------
// Submit
// ----------------------------------

const submit = () => {
    if (!canSubmit.value || isSubmitting.value) return
    isSubmitting.value = true

    const payload = {
        tax_name: form.value.tax_name,
        address: form.value.address,
        rfc: form.value.rfc,
        postal_code: form.value.postal_code,
        tax_person_type: form.value.tax_person_type,
        cfdi_use: form.value.cfdi_use,
        tax_regime: form.value.tax_regime,
    }

    router.post(route('invoice-data.store'), payload, {
        onError: (errors) => {
            const first = Object.values(errors)[0]
            if (first) errorA(first)
        },
        onFinish: () => { isSubmitting.value = false },
    })
}

const goBack = () => router.get(route('profile.edit'))

const handleConfirm = () => { alertState.value.onConfirm?.(); alertState.value.show = false }
const handleCancel = () => { alertState.value.onCancel?.(); alertState.value.show = false }
</script>

<template>
    <div>

        <Head title="Datos de Facturación" />

        <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6 lg:p-8">
            <div class="space-y-6 max-w-6xl mx-auto">

                <!-- Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                            Datos de Facturación
                        </h3>
                        <p class="text-sm text-gray-500">
                            {{ isEditing ? 'Actualiza tu información fiscal para la emisión de facturas' : 'Completa tu información fiscal para solicitar facturas' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <!-- <button @click="goBack" :disabled="isSubmitting"
                            class="inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-transparent px-4 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors disabled:opacity-50">
                            Regresar
                        </button> -->
                        <button @click="submit" :disabled="isSubmitting || !canSubmit"
                            class="inline-flex h-10 items-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg v-if="isSubmitting" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                            </svg>
                            {{ isSubmitting ? 'Guardando...' : (isEditing ? 'Actualizar datos' : 'Guardar datos') }}
                        </button>
                    </div>
                </div>

                <!-- Resumen datos actuales (solo si ya existen datos guardados) -->
                <div v-if="isEditing"
                    class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03] overflow-hidden">
                    <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 px-6 py-4">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-50 dark:bg-green-500/10 text-green-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Datos fiscales actuales
                            </h2>
                            <p class="text-xs text-gray-500">Información registrada actualmente</p>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-gray-800/50">
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Razón social
                                </p>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white/90">{{ invoiceData.name
                                    }}</p>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-gray-800/50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">RFC
                                        </p>
                                        <p
                                            class="text-sm font-mono font-semibold text-slate-900 dark:text-white/90 tracking-wider">
                                            {{ invoiceData.rfc }}</p>
                                    </div>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 text-xs font-medium text-slate-600 dark:text-gray-300">
                                        {{ invoiceData.person_type === 'moral' ? 'Moral' : 'Física' }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-gray-800/50">
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Uso de CFDI
                                </p>
                                <p class="text-sm font-medium text-slate-900 dark:text-white/90">{{ cfdiLabel }}</p>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-gray-800/50">
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Régimen
                                    fiscal</p>
                                <p class="text-sm font-medium text-slate-900 dark:text-white/90">{{ regimenLabel }}</p>
                            </div>
                            <div
                                class="sm:col-span-2 lg:col-span-4 p-4 rounded-xl bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-1">
                                            <p
                                                class="text-xs font-medium text-blue-700 dark:text-blue-400 uppercase tracking-wide">
                                                Domicilio fiscal</p>
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-100 dark:bg-blue-500/20 text-xs font-semibold text-blue-700 dark:text-blue-400">
                                                CP {{ invoiceData.postal_code }}
                                            </span>
                                        </div>
                                        <p class="text-sm font-semibold text-blue-900 dark:text-blue-300 leading-snug">
                                            {{ invoiceData.address }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Datos del contribuyente -->
                    <div
                        class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03] overflow-hidden">
                        <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 px-6 py-4">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10 text-brand-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Información del
                                    contribuyente</h2>
                                <p class="text-xs text-gray-500">Razón social, RFC y domicilio fiscal</p>
                            </div>
                        </div>
                        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">

                            <!-- RAZON SOCIAL -->
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Razón social / Nombre completo <span class="text-red-500">*</span>
                                </label>
                                <input type="text" v-model="form.tax_name"
                                    placeholder="Ej. Walmart de México S.A.B. de C.V."
                                    class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 text-sm text-gray-800 dark:text-white/90 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900" />
                                <p v-if="errors.tax_name" class="mt-1 text-xs text-red-500">{{ errors.tax_name }}</p>
                            </div>

                            <!-- RFC -->
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    RFC <span class="text-red-500">*</span>
                                </label>
                                <input type="text" v-model="form.rfc" @input="formatRfc" maxlength="13"
                                    placeholder="Ej. XAXX010101000"
                                    class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 text-sm font-mono text-gray-800 dark:text-white/90 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900 uppercase tracking-wider" />
                                <p v-if="!isRfcValid" class="mt-1 text-xs text-red-500">El formato del RFC no es válido
                                </p>
                                <p v-else-if="errors.rfc" class="mt-1 text-xs text-red-500">{{ errors.rfc }}</p>
                                <p v-else class="mt-1 text-xs text-gray-400">12 caracteres (persona física) o 13
                                    (persona
                                    moral)</p>
                            </div>

                            <!-- CP -->
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Código Postal <span class="text-red-500">*</span>
                                </label>
                                <input type="text" v-model="form.postal_code" maxlength="5" placeholder="Ej. 27086"
                                    class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 text-sm text-gray-800 dark:text-white/90 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900" />
                                <p v-if="!isCpValid" class="mt-1 text-xs text-red-500">El código postal debe tener 5
                                    dígitos
                                </p>
                                <p v-else-if="errors.postal_code" class="mt-1 text-xs text-red-500">{{
                                    errors.postal_code }}
                                </p>
                            </div>

                            <!-- DOMICILIO FISCAL -->
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Domicilio fiscal <span class="text-red-500">*</span>
                                </label>
                                <input type="text" v-model="form.address"
                                    placeholder="Ej. Av. Insurgentes Sur 1602, Col. Crédito Constructor"
                                    class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 text-sm text-gray-800 dark:text-white/90 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900" />
                                <p v-if="errors.address" class="mt-1 text-xs text-red-500">{{ errors.address }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Configuracion fiscal SAT -->
                    <div
                        class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03] overflow-hidden">
                        <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 px-6 py-4">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10 text-brand-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Configuración fiscal
                                </h2>
                                <p class="text-xs text-gray-500">Tipo de persona, uso de CFDI y régimen fiscal</p>
                            </div>
                        </div>
                        <div class="p-6 grid grid-cols-1 gap-5">

                            <!-- TIPO DE PERSONA -->
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Tipo de persona <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select :value="form.tax_person_type" @change="setPersonType($event.target.value)"
                                        class="h-11 w-full appearance-none rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 pr-8 text-sm text-gray-800 dark:text-white/90 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900">
                                        <option value="">Seleccionar tipo de persona</option>
                                        <option v-for="t in tiposPersona" :key="t.value" :value="t.value">
                                            {{ t.label }}
                                        </option>
                                    </select>
                                    <span
                                        class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                                        <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16"
                                            fill="none">
                                            <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke-width="1.2"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                </div>
                                <p v-if="errors.tax_person_type" class="mt-1 text-xs text-red-500">{{
                                    errors.tax_person_type
                                    }}</p>
                            </div>

                            <!-- CFDI -->
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Uso de CFDI <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select :value="form.cfdi_use" @change="setCfdiUse($event.target.value)"
                                        :disabled="!form.tax_person_type"
                                        class="h-11 w-full appearance-none rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 pr-8 text-sm text-gray-800 dark:text-white/90 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900 disabled:cursor-not-allowed disabled:opacity-50">
                                        <option value="">{{ form.tax_person_type ? 'Seleccionar uso de CFDI' : 'Primero selecciona tipo de persona' }}</option>
                                        <option v-for="uso in usosCfdiDisponibles" :key="uso.codigo"
                                            :value="uso.codigo">
                                            {{ uso.codigo }} – {{ uso.descripcion }}
                                        </option>
                                    </select>
                                    <span
                                        class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                                        <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16"
                                            fill="none">
                                            <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke-width="1.2"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                </div>
                                <p v-if="errors.cfdi_use" class="mt-1 text-xs text-red-500">{{ errors.cfdi_use }}</p>
                                <p v-else-if="!form.tax_person_type" class="mt-1 text-xs text-gray-400">
                                    Selecciona primero el tipo de persona para ver las opciones disponibles
                                </p>
                            </div>

                            <!-- REGIMEN FISCAL -->
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Régimen fiscal <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select v-model="form.tax_regime" :disabled="!form.cfdi_use"
                                        class="h-11 w-full appearance-none rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 pr-8 text-sm text-gray-800 dark:text-white/90 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900 disabled:cursor-not-allowed disabled:opacity-50">
                                        <option value="">{{ form.cfdi_use ? 'Seleccionar régimen fiscal' : 'Primero selecciona uso de CFDI' }}</option>
                                        <option v-for="r in regimenesDisponibles" :key="r.codigo" :value="r.codigo">
                                            {{ r.codigo }} – {{ r.descripcion }}
                                        </option>
                                    </select>
                                    <span
                                        class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                                        <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16"
                                            fill="none">
                                            <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke-width="1.2"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                </div>
                                <p v-if="errors.tax_regime" class="mt-1 text-xs text-red-500">{{ errors.tax_regime }}
                                </p>
                                <p v-else-if="!form.cfdi_use" class="mt-1 text-xs text-gray-400">
                                    Selecciona primero el uso de CFDI para ver los regímenes disponibles
                                </p>
                            </div>

                        </div>
                    </div>

                </div>



                <!-- Resumen -->
                <div v-if="canSubmit && !isEditing"
                    class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03] overflow-hidden">
                    <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 px-6 py-4">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-50 dark:bg-green-500/10 text-green-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Resumen</h2>
                            <p class="text-xs text-gray-500">Verifica que los datos sean correctos antes de guardar</p>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-gray-800/50">
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Razón social
                                </p>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white/90">{{ form.tax_name }}
                                </p>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-gray-800/50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">RFC
                                        </p>
                                        <p
                                            class="text-sm font-mono font-semibold text-slate-900 dark:text-white/90 tracking-wider">
                                            {{ form.rfc }}</p>
                                    </div>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 text-xs font-medium text-slate-600 dark:text-gray-300">
                                        {{ form.tax_person_type === 'moral' ? 'Persona Moral' : 'Persona Física' }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-gray-800/50">
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Uso de CFDI
                                </p>
                                <p class="text-sm font-medium text-slate-900 dark:text-white/90">{{ form.cfdi_use }}</p>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-gray-800/50">
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Régimen
                                    fiscal</p>
                                <p class="text-sm font-medium text-slate-900 dark:text-white/90">{{ form.tax_regime }}
                                </p>
                            </div>
                            <div
                                class="sm:col-span-2 p-4 rounded-xl bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-1">
                                            <p
                                                class="text-xs font-medium text-blue-700 dark:text-blue-400 uppercase tracking-wide">
                                                Domicilio fiscal</p>
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-100 dark:bg-blue-500/20 text-xs font-semibold text-blue-700 dark:text-blue-400">
                                                CP {{ form.postal_code }}
                                            </span>
                                        </div>
                                        <p class="text-sm font-semibold text-blue-900 dark:text-blue-300 leading-snug">
                                            {{ form.address }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nota informativa -->
                <div
                    class="flex items-start gap-3 p-4 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-100 dark:border-amber-500/20">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-amber-800 dark:text-amber-400">Importante</p>
                        <p class="text-xs text-amber-700 dark:text-amber-500 mt-0.5">
                            Asegúrate de que tu información fiscal coincida exactamente con la registrada ante el SAT.
                            Datos incorrectos pueden ocasionar que tu factura sea rechazada.
                        </p>
                    </div>
                </div>

                <!-- Botones mobile -->
                <div class="flex flex-col gap-2 lg:hidden">
                    <button @click="submit" :disabled="isSubmitting || !canSubmit"
                        class="inline-flex h-10 w-full justify-center items-center rounded-lg bg-brand-500 text-sm font-medium text-white hover:bg-brand-600 transition-colors disabled:opacity-50">
                        {{ isSubmitting ? 'Guardando...' : (isEditing ? 'Actualizar datos' : 'Guardar datos') }}
                    </button>
                    <button @click="goBack"
                        class="inline-flex h-10 w-full justify-center items-center rounded-lg border border-gray-300 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        Regresar
                    </button>
                </div>

            </div>
        </div>

        <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
            :buttonText="alertState.buttonText" :cancelText="alertState.cancelText" @confirm="handleConfirm"
            @cancel="handleCancel" @close="alertState.show = false" />
    </div>
</template>