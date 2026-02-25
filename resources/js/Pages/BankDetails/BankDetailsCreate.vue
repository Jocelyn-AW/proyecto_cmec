<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['close', 'created', 'warning', 'error', 'info'])

const isSubmitting = ref(false)

// bancos principales de México
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

// el valor real que se manda al backend
const bankValue = computed(() => {
    if (selectedBank.value === 'Otro') return customBank.value.trim()
    return selectedBank.value
})

const form = ref({
    account_number: '',
    clabe_number: '',
    beneficiary: '',
    reference: '',
    subsidiary: '',
})

const resetForm = () => {
    selectedBank.value = ''
    customBank.value = ''
    form.value = {
        account_number: '',
        clabe_number: '',
        beneficiary: '',
        reference: '',
        subsidiary: '',
    }
}

const close = () => {
    resetForm()
    emit('close')
}

const submit = () => {
    if (!bankValue.value) {
        emit('info', 'El nombre del banco es obligatorio')
        return
    }

    if (!form.value.account_number.trim() && !form.value.clabe_number.trim()) {
        emit('info', 'Debes ingresar al menos el número de cuenta o la CLABE')
        return
    }

    isSubmitting.value = true

    const payload = {
        bank: bankValue.value,
        account_number: form.value.account_number.trim() || null,
        clabe_number: form.value.clabe_number.trim() || null,
        beneficiary: form.value.beneficiary.trim() || null,
        reference: form.value.reference.trim() || null,
        subsidiary: form.value.subsidiary.trim() || null,
    }

    router.post('/bankdetails', payload, {
        preserveScroll: true,
        onSuccess: () => {
            close()
            emit('created')
        },
        onError: (errors) => {
            emit('error', errors?.bank?.[0] || errors?.account_number?.[0] || errors?.clabe_number?.[0] || 'Error al crear la cuenta bancaria')
        },
        onFinish: () => {
            isSubmitting.value = false
        }
    })
}
</script>

<template>
    <Modal :show="show" @close="close" maxWidth="lg">
        <template #title>Agregar cuenta bancaria</template>

        <div class="p-6 space-y-4">

            <!-- selector de banco -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Banco <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select v-model="selectedBank"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 pr-8 text-sm appearance-none focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                        :class="!selectedBank ? 'text-gray-400 dark:text-white/30' : 'text-gray-800'">
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

                <!-- input libre "Otro" -->
                <div v-if="selectedBank === 'Otro'" class="mt-2">
                    <input v-model="customBank" type="text" placeholder="Escribe el nombre del banco..." maxlength="100"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
            </div>

            <!-- nota informativa -->
            <div
                class="rounded-lg bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 px-3 py-2.5 flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-4 h-4 text-blue-500 shrink-0 mt-0.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <p class="text-xs text-blue-700 dark:text-blue-300">
                    Debes ingresar al menos el <strong>número de cuenta</strong> o la <strong>CLABE
                        interbancaria</strong>.
                    Puedes llenar ambos si lo deseas.
                </p>
            </div>

            <!-- numero de cuenta -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Número de cuenta
                        <span class="text-gray-400 text-xs font-normal">(10-16 dígitos)</span>
                    </label>
                    <span class="text-xs tabular-nums transition-colors" :class="form.account_number.length > 0
                        ? (form.account_number.length < 10 || form.account_number.length > 16)
                            ? 'text-red-500'
                            : 'text-green-600 dark:text-green-400'
                        : 'text-gray-400'">
                        {{ form.account_number.length }}/16
                    </span>
                </div>
                <input v-model="form.account_number" type="text" inputmode="numeric" maxlength="16"
                    placeholder="Ej. 1234567890"
                    class="block w-full rounded-lg border px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1 transition-colors dark:bg-gray-900 dark:text-white/90"
                    :class="form.account_number.length > 0 && (form.account_number.length < 10 || form.account_number.length > 16)
                        ? 'border-red-400 focus:border-red-500 focus:ring-red-500 dark:border-red-500/50'
                        : 'border-gray-300 focus:border-green-500 focus:ring-green-500 dark:border-gray-700'">
                <p v-if="form.account_number.length > 0 && form.account_number.length < 10"
                    class="mt-1 text-xs text-red-500">
                    Mínimo 10 dígitos (faltan {{ 10 - form.account_number.length }})
                </p>
            </div>

            <!-- CLABE -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        CLABE interbancaria
                        <span class="text-gray-400 text-xs font-normal">(18 dígitos)</span>
                    </label>
                    <span class="text-xs tabular-nums transition-colors" :class="form.clabe_number.length > 0
                        ? form.clabe_number.length === 18
                            ? 'text-green-600 dark:text-green-400'
                            : 'text-red-500'
                        : 'text-gray-400'">
                        {{ form.clabe_number.length }}/18
                    </span>
                </div>
                <input v-model="form.clabe_number" type="text" inputmode="numeric" maxlength="18"
                    placeholder="Ej. 062180001234567890"
                    class="block w-full rounded-lg border px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1 transition-colors dark:bg-gray-900 dark:text-white/90"
                    :class="form.clabe_number.length > 0 && form.clabe_number.length !== 18
                        ? 'border-red-400 focus:border-red-500 focus:ring-red-500 dark:border-red-500/50'
                        : 'border-gray-300 focus:border-green-500 focus:ring-green-500 dark:border-gray-700'">
                <p v-if="form.clabe_number.length > 0 && form.clabe_number.length < 18"
                    class="mt-1 text-xs text-red-500">
                    Faltan {{ 18 - form.clabe_number.length }} dígitos
                </p>
            </div>

            <div class="border-t border-gray-100 dark:border-gray-800" />

            <!-- beneficiario -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Beneficiario
                    <span class="text-gray-400 text-xs font-normal">(opcional)</span>
                </label>
                <input v-model="form.beneficiary" type="text" placeholder="A nombre de..."
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
            </div>

            <!-- referencia + sucursal -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Referencia
                        <span class="text-gray-400 text-xs font-normal">(opcional)</span>
                    </label>
                    <input v-model="form.reference" type="text" placeholder="Ej. Nómina Quincenal"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Sucursal
                        <span class="text-gray-400 text-xs font-normal">(opcional)</span>
                    </label>
                    <input v-model="form.subsidiary" type="text" placeholder="Ej. Sucursal Centro"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
            </div>

        </div>

        <template #footer>
            <button @click="close" :disabled="isSubmitting"
                class="rounded-md border border-transparent py-2 px-4 text-sm text-slate-600 transition-all hover:bg-slate-100 disabled:opacity-50 dark:text-gray-400 dark:hover:bg-gray-800">
                Cancelar
            </button>
            <button @click="submit" :disabled="isSubmitting"
                class="inline-flex items-center gap-2 rounded-md bg-green-600 py-2 px-4 text-sm text-white transition-all shadow-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg v-if="isSubmitting" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                </svg>
                {{ isSubmitting ? 'Guardando...' : 'Guardar cuenta' }}
            </button>
        </template>
    </Modal>
</template>
