<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Alerta from '@/Components/Alerta.vue'
import { useAlert } from '@/composables/useAlert'
import flatPickr from 'vue-flatpickr-component'
import { Spanish } from 'flatpickr/dist/l10n/es.js'
import 'flatpickr/dist/flatpickr.css'
import states from '@/composables/useStatesAndCities'

defineOptions({ layout: AuthenticatedLayout })

const { alertState, success, errorA, warning, hideAlert } = useAlert()

const props = defineProps({
    member: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) },
    flash:  { type: Object, default: () => ({}) },
})

const isSubmitting = ref(false)

const docFields = [
    { key: 'diploma_especialidad', label: 'Diploma de especialidad en coloproctología', urlKey: 'diploma_especialidad_url' },
    { key: 'titulo_medico', label: 'Título médico general', urlKey: 'titulo_medico_url' },
    { key: 'diploma_consejo', label: 'Diploma del consejo de coloproctología', urlKey: 'diploma_consejo_url' },
    { key: 'cedula_profesion', label: 'Cédula de profesión', urlKey: 'cedula_profesion_url' },
    { key: 'cedula_especialista', label: 'Cédula de especialista en coloproctología', urlKey: 'cedula_especialista_url' },
    { key: 'constancia_fiscal', label: 'Constancia fiscal', urlKey: 'constancia_fiscal_url' },
    { key: 'factura', label: 'Factura', urlKey: 'factura_url' },
]

const paymentMethods = [
    { value: 'cash', label: 'Efectivo' },
    { value: 'transfer', label: 'Transferencia' },
    { value: 'debit_card', label: 'Tarjeta de débito' },
    { value: 'credit_card', label: 'Tarjeta de crédito' },
    { value: 'stripe', label: 'Stripe' },
]

// ----------------------------------
// Form — pre-llenado con member
// ----------------------------------

const latestPayment = computed(() => props.member.payments?.[0] ?? null)

const form = ref({
    cmec_member_id: props.member.cmec_member_id ?? '',
    name: props.member.name ?? '',
    last_name: props.member.last_name ?? '',
    phone: props.member.phone ?? '',
    email: props.member.email ?? '',
    city: props.member.city ?? '',
    state: props.member.state ?? '',
    hospital: props.member.hospital ?? '',
    inscription_date: props.member.inscription_date ?? '',
    expiration_date: props.member.expiration_date ?? '',
    amount: latestPayment.value?.amount ?? '',
    payment_method: latestPayment.value?.payment_method ?? '',
    payment_date: latestPayment.value?.payment_date ?? '',
})

// ---------------------------
//
//----------------------------


const selectedState = ref(props.member.state ?? '')
const selectedCity = ref(props.member.city ?? '')
const cities = computed(() => selectedState.value ? states[selectedState.value] : [])

watch(selectedState, (val) => {
    selectedCity.value = ''
    form.value.state = val
})

watch(selectedCity, (val) => {
    form.value.city = val
})

const page = usePage()

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
// PDFs
// ----------------------------------

const MAX_TOTAL_PDF_MB = 10 // 7 PDFs x 10MB
const MAX_TOTAL_BYTES  = MAX_TOTAL_PDF_MB * 1024 * 1024

const pdfFiles = ref({})
const dragging = ref({})

const onPdfChange = (key, event) => {
    const file = event.target.files?.[0]
    if (!file) return
    if (file.size > 10 * 1024 * 1024) { warning('El PDF no puede pesar más de 10MB'); return }
    pdfFiles.value[key] = file
}
const removePdf = (key) => { delete pdfFiles.value[key] }
const onDragOver = (key, e) => { e.preventDefault(); dragging.value[key] = true }
const onDragLeave = (key, e) => { e.preventDefault(); dragging.value[key] = false }
const onDrop = (key, e) => {
    e.preventDefault(); dragging.value[key] = false
    const files = e.dataTransfer.files
    if (files?.length) onPdfChange(key, { target: { files } })
}

const openDoc = (url) => { if (url) window.open(url, '_blank') }

// ----------------------------------
// Flatpickr
// ----------------------------------

const flatpickrConfig = { locale: Spanish, dateFormat: 'Y-m-d', altInput: true, altFormat: 'F j, Y' }

// ----------------------------------
// Submit
// ----------------------------------

const canSubmit = computed(() =>
    form.value.name.trim() && form.value.last_name.trim() &&
    form.value.email.trim() && form.value.phone.trim() &&
    form.value.city.trim() && form.value.inscription_date && form.value.expiration_date
)

const submit = () => {
    if (!canSubmit.value || isSubmitting.value) return

    // Validar tamaño total de PDFs antes de enviar
    const totalPdfSize = Object.values(pdfFiles.value)
        .reduce((sum, file) => sum + file.size, 0)

    if (totalPdfSize > MAX_TOTAL_BYTES) {
        errorA(`El tamaño total de los documentos excede ${MAX_TOTAL_PDF_MB}MB. Por favor reduce el tamaño de los archivos.`)
        return
    }

    isSubmitting.value = true

    isSubmitting.value = true

    const formData = new FormData()
    formData.append('_method', 'PUT')

    Object.entries(form.value).forEach(([key, val]) => {
        if (val !== null && val !== undefined && val !== '') formData.append(key, val)
    })

    Object.entries(pdfFiles.value).forEach(([key, file]) => formData.append(key, file))

    router.post(route('members.update', props.member.id), formData, {
        forceFormData: true,
        onError: (errors) => {
            const first = Object.values(errors)[0]
            if (first) errorA(first)
        },
        onFinish: () => { isSubmitting.value = false },
    })
}

const cancel = () => router.get(route('members.index'))

const handleConfirm = () => { alertState.value.onConfirm?.(); alertState.value.show = false }
const handleCancel = () => { alertState.value.onCancel?.(); alertState.value.show = false }
</script>

<template>
    <div>

        <Head :title="`Editar — ${member.name} ${member.last_name}`" />

        <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6 lg:p-8">
            <div class="space-y-6 max-w-6xl mx-auto">

                <!-- Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                            {{ member.name }} {{ member.last_name }}
                        </h3>
                        <p class="text-sm text-gray-500">
                            Editar información del miembro
                            <span v-if="member.cmec_member_id" class="font-mono text-xs text-gray-400 ml-1">· {{
                                member.cmec_member_id }}</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button @click="cancel" :disabled="isSubmitting"
                            class="inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-transparent px-4 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors disabled:opacity-50">
                            Cancelar
                        </button>
                        <button @click="submit" :disabled="isSubmitting || !canSubmit"
                            class="inline-flex h-10 items-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg v-if="isSubmitting" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                            </svg>
                            {{ isSubmitting ? 'Guardando...' : 'Guardar cambios' }}
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                    <!-- Columna principal -->
                    <div class="lg:col-span-2 space-y-5">

                        <!-- Datos personales -->
                        <div
                            class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03] overflow-hidden">
                            <div
                                class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 px-6 py-4">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10 text-brand-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                        <circle cx="12" cy="7" r="4" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Datos personales
                                    </h2>
                                    <p class="text-xs text-gray-500">Información de contacto e identificación</p>
                                </div>
                            </div>
                            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        ID CMEC <span class="ml-1 text-xs text-gray-400 font-normal">(opcional)</span>
                                    </label>
                                    <input type="text" v-model="form.cmec_member_id" placeholder="Ej. CMEC-0001"
                                        class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 text-sm text-gray-800 dark:text-white/90 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900" />
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Nombre <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" v-model="form.name"
                                        class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 text-sm text-gray-800 dark:text-white/90 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900" />
                                    <p v-if="errors.name" class="mt-1 text-xs text-red-500">{{ errors.name }}</p>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Apellidos <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" v-model="form.last_name"
                                        class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 text-sm text-gray-800 dark:text-white/90 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900" />
                                    <p v-if="errors.last_name" class="mt-1 text-xs text-red-500">{{ errors.last_name }}
                                    </p>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Teléfono <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel" v-model="form.phone"
                                        class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 text-sm text-gray-800 dark:text-white/90 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900" />
                                    <p v-if="errors.phone" class="mt-1 text-xs text-red-500">{{ errors.phone }}</p>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Correo electrónico <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" v-model="form.email"
                                        class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 text-sm text-gray-800 dark:text-white/90 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900" />
                                    <p v-if="errors.email" class="mt-1 text-xs text-red-500">{{ errors.email }}</p>
                                </div>

                                <!-- Estado -->
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Estado
                                        <span class="ml-1 text-xs text-gray-400 font-normal">(opcional)</span>
                                    </label>
                                    <div class="relative">
                                        <select v-model="selectedState"
                                            class="h-11 w-full appearance-none rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 pr-8 text-sm text-gray-800 dark:text-white/90 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900">
                                            <option value="">Seleccionar estado</option>
                                            <option v-for="state in Object.keys(states)" :key="state" :value="state">{{
                                                state }}</option>
                                        </select>
                                        <span
                                            class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                                            <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16"
                                                fill="none">
                                                <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165"
                                                    stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>

                                <!-- Ciudad -->
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Ciudad <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <select v-model="selectedCity" :disabled="!selectedState"
                                            class="h-11 w-full appearance-none rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 pr-8 text-sm text-gray-800 dark:text-white/90 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900 disabled:cursor-not-allowed disabled:opacity-50">
                                            <option value="">{{ selectedState ? 'Seleccionar ciudad' : 'Primero elige un estado' }}</option>
                                            <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
                                        </select>
                                        <span
                                            class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                                            <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16"
                                                fill="none">
                                                <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165"
                                                    stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                    </div>
                                    <p v-if="errors.city" class="mt-1 text-xs text-red-500">{{ errors.city }}</p>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Hospital <span class="ml-1 text-xs text-gray-400 font-normal">(opcional)</span>
                                    </label>
                                    <input type="text" v-model="form.hospital"
                                        class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 text-sm text-gray-800 dark:text-white/90 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900" />
                                </div>
                            </div>
                        </div>

                        <!-- Vigencia -->
                        <div
                            class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03] overflow-hidden">
                            <div
                                class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 px-6 py-4">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10 text-brand-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                                        <line x1="16" x2="16" y1="2" y2="6" />
                                        <line x1="8" x2="8" y1="2" y2="6" />
                                        <line x1="3" x2="21" y1="10" y2="10" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Vigencia de
                                        membresía</h2>
                                    <p class="text-xs text-gray-500">Fechas de inscripción y vencimiento</p>
                                </div>
                            </div>
                            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Fecha de inscripción <span class="text-red-500">*</span>
                                    </label>
                                    <flat-pickr v-model="form.inscription_date" :config="flatpickrConfig"
                                        class="h-11 w-full appearance-none rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 text-sm text-gray-800 dark:text-white/90 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900" />
                                    <p v-if="errors.inscription_date" class="mt-1 text-xs text-red-500">{{
                                        errors.inscription_date }}</p>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Fecha de vencimiento <span class="text-red-500">*</span>
                                    </label>
                                    <flat-pickr v-model="form.expiration_date" :config="flatpickrConfig"
                                        class="h-11 w-full appearance-none rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 text-sm text-gray-800 dark:text-white/90 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900" />
                                    <p v-if="errors.expiration_date" class="mt-1 text-xs text-red-500">{{
                                        errors.expiration_date }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Pago -->
                        <div
                            class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03] overflow-hidden">
                            <div
                                class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 px-6 py-4">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10 text-brand-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Información de
                                        pago</h2>
                                    <p class="text-xs text-gray-500">
                                        {{ latestPayment ? 'Último pago registrado' : 'Sin pagos registrados' }}
                                    </p>
                                </div>
                            </div>
                            <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-5">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Cantidad <span class="ml-1 text-xs text-gray-400 font-normal">(opcional)</span>
                                    </label>
                                    <div class="relative">
                                        <span
                                            class="absolute left-0 top-1/2 -translate-y-1/2 border-r border-gray-200 dark:border-gray-700 px-3 py-1 text-sm text-gray-500">$</span>
                                        <input type="number" v-model="form.amount" min="0" placeholder="0.00"
                                            class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent pl-10 pr-4 text-sm text-gray-800 dark:text-white/90 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900" />
                                    </div>
                                    <p v-if="errors.amount" class="mt-1 text-xs text-red-500">{{ errors.amount }}</p>
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Método
                                        de pago</label>
                                    <div class="relative">
                                        <select v-model="form.payment_method"
                                            class="h-11 w-full appearance-none rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 pr-8 text-sm text-gray-800 dark:text-white/90 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900">
                                            <option value="">Seleccionar</option>
                                            <option v-for="m in paymentMethods" :key="m.value" :value="m.value">{{
                                                m.label }}</option>
                                        </select>
                                        <span
                                            class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                                            <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16"
                                                fill="none">
                                                <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165"
                                                    stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                    </div>
                                    <p v-if="errors.payment_method" class="mt-1 text-xs text-red-500">{{
                                        errors.payment_method }}</p>
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Fecha
                                        de pago</label>
                                    <flat-pickr v-model="form.payment_date" :config="flatpickrConfig"
                                        placeholder="Seleccionar fecha"
                                        class="h-11 w-full appearance-none rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 text-sm text-gray-800 dark:text-white/90 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900" />
                                    <p v-if="errors.payment_date" class="mt-1 text-xs text-red-500">{{
                                        errors.payment_date }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Botones móvil -->
                        <div class="flex flex-col gap-2 lg:hidden">
                            <button @click="submit" :disabled="isSubmitting || !canSubmit"
                                class="inline-flex h-10 w-full justify-center items-center rounded-lg bg-brand-500 text-sm font-medium text-white hover:bg-brand-600 transition-colors disabled:opacity-50">
                                {{ isSubmitting ? 'Guardando...' : 'Guardar cambios' }}
                            </button>
                            <button @click="cancel"
                                class="inline-flex h-10 w-full justify-center items-center rounded-lg border border-gray-300 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                Cancelar
                            </button>
                        </div>
                    </div>

                    <!-- Columna lateral: documentos -->
                    <div class="space-y-4">
                        <div
                            class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03] overflow-hidden">
                            <div
                                class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 px-6 py-4">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 dark:bg-red-500/10 text-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path
                                            d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                                        <polyline points="14 2 14 8 20 8" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Documentos</h2>
                                    <p class="text-xs text-gray-500">Haz clic en el ícono para ver el actual</p>
                                </div>
                            </div>
                            <div class="p-4 space-y-3">
                                <div v-for="doc in docFields" :key="doc.key">
                                    <div class="mb-1 flex items-center justify-between gap-2">
                                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400 truncate"
                                            :title="doc.label">
                                            {{ doc.label }}
                                        </p>
                                        <!-- Ver existente -->
                                        <button v-if="member[doc.urlKey] && !pdfFiles[doc.key]"
                                            @click="openDoc(member[doc.urlKey])" title="Ver documento actual"
                                            class="shrink-0 text-red-400 hover:text-red-600 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                                                <polyline points="15 3 21 3 21 9" />
                                                <line x1="10" x2="21" y1="14" y2="3" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Archivo nuevo seleccionado -->
                                    <div v-if="pdfFiles[doc.key]"
                                        class="flex items-center gap-2 p-2.5 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-100 dark:border-red-500/20">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500 shrink-0"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path
                                                d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                                        </svg>
                                        <span class="flex-1 text-xs text-red-700 dark:text-red-400 truncate">{{
                                            pdfFiles[doc.key].name }}</span>
                                        <button @click="removePdf(doc.key)"
                                            class="text-red-400 hover:text-red-600 transition-colors shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M18 6 6 18M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Zona drop -->
                                    <div v-else @dragover="onDragOver(doc.key, $event)"
                                        @dragleave="onDragLeave(doc.key, $event)" @drop="onDrop(doc.key, $event)"
                                        @click="$refs[doc.key][0].click()" :class="[
                                            'flex items-center justify-center gap-2 h-10 rounded-lg border-2 border-dashed cursor-pointer transition-all text-xs',
                                            dragging[doc.key]
                                                ? 'border-red-400 bg-red-50 dark:bg-red-500/10 text-red-500'
                                                : member[doc.urlKey]
                                                    ? 'border-green-200 dark:border-green-700 text-green-500 hover:border-green-300'
                                                    : 'border-gray-200 dark:border-gray-700 text-gray-400 hover:border-gray-300 hover:text-gray-500'
                                        ]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                            <polyline points="17 8 12 3 7 8" />
                                            <line x1="12" y1="3" x2="12" y2="15" />
                                        </svg>
                                        {{ dragging[doc.key] ? 'Suelta aquí' : member[doc.urlKey] ? 'Reemplazar PDF' :
                                            'Subir PDF' }}
                                    </div>

                                    <input :ref="doc.key" type="file" accept="application/pdf"
                                        @change="onPdfChange(doc.key, $event)" class="hidden" />

                                    <p v-if="errors[doc.key]" class="mt-0.5 text-xs text-red-500">{{ errors[doc.key] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
            :buttonText="alertState.buttonText" :cancelText="alertState.cancelText" @confirm="handleConfirm"
            @cancel="handleCancel" @close="alertState.show = false" />
    </div>
</template>