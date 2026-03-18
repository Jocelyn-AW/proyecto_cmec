<template>
    <div class="flex flex-col gap-2 mb-4">
        <!-- Razon Social -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Razón social </label>
            <input class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                type="text" v-model="form.tax_name"
            />
            <span v-if="errors?.tax_name" class="text-red-500 text-xs flex justify-end">{{ errors?.tax_name }}</span>
        </div>

        <!-- Domicilio -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Domicilio Fiscal </label>
            <input class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                type="text" v-model="form.address"
            />
            <span v-if="errors?.address" class="text-red-500 text-xs flex justify-end">{{ errors?.address }}</span>
        </div>

        <!-- RFC y codigo postal -->
        <div class="flex w-full gap-3">
            <div class="grow">
                <label class="block text-sm font-medium text-gray-700 mb-1 ">RFC</label>
                <input class="w-full grow rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    type="text" maxlength="13" v-model="form.rfc" @input="formatRfc"
                />
                <span v-if="!isRfcValid" class="text-red-500 text-xs flex justify-end">El formato del RFC no es válido</span>
                <span v-else-if="errors?.rfc" class="text-red-500 text-xs flex justify-end">{{ errors?.rfc }}</span>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Código Postal</label>
                <input class="w-full grow rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    type="text" maxlength="5" v-model="form.postal_code"
                />
                <span v-if="!isCpValid" class="text-red-500 text-xs flex justify-end">El código postal no es válido</span>
                <span v-else-if="errors?.postal_code" class="text-red-500 text-xs flex justify-end">{{ errors?.postal_code }}</span>
            </div>
        </div>
        
        <!-- Tipo de persona -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de persona</label>
            <select name="tax_person_type" id="tax_person_type" :value="form.tax_person_type" @change="setPersonType($event.target.value)"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Seleccionar</option>
                <option v-for="t in tiposPersona" :key="t.value" :value="t.value">
                    {{ t.label }}
                </option>
            </select>
            <span v-if="errors?.tax_person_type" class="text-red-500 text-xs flex justify-end">{{ errors?.tax_person_type }}</span>
        </div>
        

        <!-- Uso CFDI  -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Uso de CFDI</label>
            <select name="cfdi_use" id="cfdi_use" 
                :value="form.cfdi_use" :disabled="!form.tax_person_type" @change="setCfdiUse($event.target.value)"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Seleccionar</option>
                <option v-for="uso in usosCfdiDisponibles" :key="uso.codigo" :value="uso.codigo">
                    {{ uso.codigo }} – {{ uso.descripcion }}
                </option>
            </select>
            <span v-if="errors?.cfdi_use" class="text-red-500 text-xs flex justify-end">{{ errors?.cfdi_use }}</span>
        </div>

        <!-- Régimen fiscal  -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Régimen Fiscal</label>
            <select name="tax_regime" id="tax_regime" 
                v-model="form.tax_regime" :disabled="!form.cfdi_use"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Seleccionar</option>
                <option v-for="r in regimenesDisponibles" :key="r.codigo" :value="r.codigo">
                    {{ r.codigo }} – {{ r.descripcion }}
                </option>
            </select>
            <span v-if="errors?.tax_regime" class="text-red-500 text-xs flex justify-end">{{ errors?.tax_regime }}</span>
        </div>
    </div>
    <hr class="mb-4">
</template>

<script setup>
import { useBillingData } from '@/composables/useBillingData';
const props = defineProps({
    errors: {
        type: Object,
        default: () => ({})
    }
})

const form = defineModel({ required: true})

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
</script>