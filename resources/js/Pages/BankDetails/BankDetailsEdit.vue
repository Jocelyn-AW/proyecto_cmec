<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    item: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['updated', 'error', 'warning', 'cancel'])

const isOpen = ref(false)
const isSaving = ref(false)
const errors = ref({})

const BANCOS = [
    'BBVA',
    'Banamex (Citibanamex)',
    'Santander',
    'Banorte',
    'HSBC',
    'Scotiabank',
    'Inbursa',
    'Afirme',
    'Bajío',
    'Banregio',
    'Banbajío',
    'Mifel',
    'Multiva',
    'Azteca',
    'BanBajío',
    'Compartamos',
    'Consubanco',
    'Famsa',
    'Intercam',
    'Invex',
    'Ixe',
    'Monexcb',
    'Sabadell',
    'Ve por Más (BX+)',
    'Otro',
]

const selectedBank = ref('')
const customBank = ref('')

// Valor real que se manda al backend
const bankValue = computed(() => {
    if (selectedBank.value === 'Otro') return customBank.value.trim()
    return selectedBank.value
})

const form = ref({
    account_number: '',
    clabe_number: '',
    reference: '',
    beneficiary: '',
    subsidiary: '',
})

const open = () => {
    // Si el banco guardado está en la lista, lo pre-selecciona; si no, pone "Otro"
    const match = BANCOS.find(b => b === props.item.bank)
    if (match && match !== 'Otro') {
        selectedBank.value = match
        customBank.value = ''
    } else if (props.item.bank) {
        selectedBank.value = 'Otro'
        customBank.value = props.item.bank
    } else {
        selectedBank.value = ''
        customBank.value = ''
    }

    form.value = {
        account_number: props.item.account_number ?? '',
        clabe_number: props.item.clabe_number ?? '',
        reference: props.item.reference ?? '',
        beneficiary: props.item.beneficiary ?? '',
        subsidiary: props.item.subsidiary ?? '',
    }
    errors.value = {}
    isOpen.value = true
}

const close = () => {
    isOpen.value = false
    errors.value = {}
    emit('cancel')
}

const submit = () => {
    errors.value = {}

    // Validación cliente
    if (!bankValue.value) {
        errors.value.bank = ['El nombre del banco es obligatorio.']
        return
    }
    if (!form.value.account_number.trim() && !form.value.clabe_number.trim()) {
        errors.value.account_number = ['Ingresa el número de cuenta o la CLABE.']
        return
    }

    isSaving.value = true

    router.put(`/bankdetails/${props.item.id}`, {
        bank: bankValue.value,
        account_number: form.value.account_number.trim() || null,
        clabe_number: form.value.clabe_number.trim() || null,
        reference: form.value.reference.trim() || null,
        beneficiary: form.value.beneficiary.trim() || null,
        subsidiary: form.value.subsidiary.trim() || null,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            emit('updated')
            isOpen.value = false
        },
        onError: (errs) => {
            errors.value = errs
            emit('warning', 'Revisa los campos del formulario.')
        },
        onFinish: () => {
            isSaving.value = false
        }
    })
}

defineExpose({ open, close })
</script>

<template>
    <transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 -translate-y-1"
        enter-to-class="opacity-100 translate-y-0" leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-1">
        <div v-if="isOpen"
            class="border-t border-blue-100 dark:border-blue-900/40 bg-blue-50/60 dark:bg-blue-950/20 px-4 py-5 sm:px-6">
            <!-- Encabezado del panel -->
            <div class="flex items-center gap-2 mb-4">
                <span
                    class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/50">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        class="w-3 h-3 text-blue-600 dark:text-blue-400">
                        <path
                            d="M2.695 14.763l-1.262 3.154a.5.5 0 00.65.65l3.155-1.262a4 4 0 001.343-.885L17.5 5.5a2.121 2.121 0 00-3-3L3.58 13.42a4 4 0 00-.885 1.343z" />
                    </svg>
                </span>
                <span class="text-xs font-semibold text-blue-700 dark:text-blue-400 tracking-wide uppercase">
                    Editando cuenta
                </span>
                <span class="text-xs text-blue-500 truncate">· {{ props.item.bank }}</span>
            </div>

            <!-- Grid responsivo: 1 col móvil / 2 col tablet / 3 col desktop -->
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">

                <!-- ── Banco ─────────────────────────────────────────── -->
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                        Banco <span class="text-red-500">*</span>
                    </label>

                    <!-- Select -->
                    <div class="relative">
                        <select v-model="selectedBank"
                            class="block w-full rounded-lg border px-3 py-2 pr-8 text-sm appearance-none bg-white dark:bg-gray-800/80 focus:outline-none focus:ring-2 transition-colors"
                            :class="[
                                errors.bank
                                    ? 'border-red-400 dark:border-red-500 focus:ring-red-500/20'
                                    : 'border-gray-300 dark:border-gray-700 focus:border-blue-400 focus:ring-blue-500/20',
                                !selectedBank ? 'text-gray-400 dark:text-white/30' : 'text-gray-800 dark:text-white/90'
                            ]">
                            <option value="" disabled>Selecciona un banco...</option>
                            <option v-for="banco in BANCOS" :key="banco" :value="banco">{{ banco }}</option>
                        </select>
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                            <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="" stroke-width="1.2"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </div>

                    <!-- Input libre cuando "Otro" -->
                    <div v-if="selectedBank === 'Otro'" class="mt-2">
                        <input v-model="customBank" type="text" maxlength="100"
                            placeholder="Escribe el nombre del banco..."
                            class="block w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-gray-800/80 dark:text-white/90 focus:outline-none focus:ring-2 transition-colors"
                            :class="errors.bank
                                ? 'border-red-400 dark:border-red-500 focus:ring-red-500/20'
                                : 'border-gray-300 dark:border-gray-700 focus:border-blue-400 focus:ring-blue-500/20'">
                    </div>

                    <!-- Mensaje de error banco -->
                    <p v-if="errors.bank" class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">
                        {{ Array.isArray(errors.bank) ? errors.bank[0] : errors.bank }}
                    </p>
                </div>

                <!-- ── Número de cuenta ───────────────────────────────── -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                        Cuenta / tarjeta
                    </label>
                    <input v-model="form.account_number" type="text" maxlength="16" inputmode="numeric"
                        placeholder="10–16 dígitos"
                        class="block w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-gray-800/80 dark:text-white/90 shadow-sm focus:outline-none focus:ring-2 transition-colors font-mono tracking-wide"
                        :class="errors.account_number
                            ? 'border-red-400 dark:border-red-500 focus:ring-red-500/20'
                            : 'border-gray-300 dark:border-gray-700 focus:border-blue-400 focus:ring-blue-500/20'">
                    <p v-if="errors.account_number" class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">
                        {{ Array.isArray(errors.account_number) ? errors.account_number[0] : errors.account_number }}
                    </p>
                </div>

                <!-- ── CLABE ──────────────────────────────────────────── -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                        CLABE interbancaria
                    </label>
                    <input v-model="form.clabe_number" type="text" maxlength="18" inputmode="numeric"
                        placeholder="18 dígitos"
                        class="block w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-gray-800/80 dark:text-white/90 shadow-sm focus:outline-none focus:ring-2 transition-colors font-mono tracking-wide"
                        :class="errors.clabe_number
                            ? 'border-red-400 dark:border-red-500 focus:ring-red-500/20'
                            : 'border-gray-300 dark:border-gray-700 focus:border-blue-400 focus:ring-blue-500/20'">
                    <p v-if="errors.clabe_number" class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">
                        {{ Array.isArray(errors.clabe_number) ? errors.clabe_number[0] : errors.clabe_number }}
                    </p>
                </div>

                <!-- ── Beneficiario ───────────────────────────────────── -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                        Beneficiario
                    </label>
                    <input v-model="form.beneficiary" type="text" maxlength="255" placeholder="Nombre del titular"
                        class="block w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-gray-800/80 dark:text-white/90 shadow-sm focus:outline-none focus:ring-2 transition-colors"
                        :class="errors.beneficiary
                            ? 'border-red-400 dark:border-red-500 focus:ring-red-500/20'
                            : 'border-gray-300 dark:border-gray-700 focus:border-blue-400 focus:ring-blue-500/20'">
                    <p v-if="errors.beneficiary" class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">
                        {{ Array.isArray(errors.beneficiary) ? errors.beneficiary[0] : errors.beneficiary }}
                    </p>
                </div>

                <!-- ── Referencia ─────────────────────────────────────── -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                        Referencia
                    </label>
                    <input v-model="form.reference" type="text" maxlength="255" placeholder="Ej. Reembolso de insumos"
                        class="block w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-gray-800/80 dark:text-white/90 shadow-sm focus:outline-none focus:ring-2 transition-colors"
                        :class="errors.reference
                            ? 'border-red-400 dark:border-red-500 focus:ring-red-500/20'
                            : 'border-gray-300 dark:border-gray-700 focus:border-blue-400 focus:ring-blue-500/20'">
                    <p v-if="errors.reference" class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">
                        {{ Array.isArray(errors.reference) ? errors.reference[0] : errors.reference }}
                    </p>
                </div>

                <!-- ── Sucursal ───────────────────────────────────────── -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                        Sucursal
                    </label>
                    <input v-model="form.subsidiary" type="text" maxlength="255" placeholder="Nombre de la sucursal"
                        class="block w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-gray-800/80 dark:text-white/90 shadow-sm focus:outline-none focus:ring-2 transition-colors"
                        :class="errors.subsidiary
                            ? 'border-red-400 dark:border-red-500 focus:ring-red-500/20'
                            : 'border-gray-300 dark:border-gray-700 focus:border-blue-400 focus:ring-blue-500/20'">
                    <p v-if="errors.subsidiary" class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">
                        {{ Array.isArray(errors.subsidiary) ? errors.subsidiary[0] : errors.subsidiary }}
                    </p>
                </div>

            </div>

            <!-- Nota + botones -->
            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-gray-400 dark:text-gray-500">
                    * Debes ingresar al menos el número de cuenta o la CLABE.
                </p>
                <div class="flex items-center justify-end gap-2 shrink-0">
                    <button @click="close" :disabled="isSaving"
                        class="rounded-lg border border-gray-300 dark:border-gray-700 px-4 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors disabled:opacity-50">
                        Cancelar
                    </button>
                    <button @click="submit" :disabled="isSaving"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-blue-700 shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg v-if="isSaving" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                        </svg>
                        {{ isSaving ? 'Guardando…' : 'Guardar cambios' }}
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>
