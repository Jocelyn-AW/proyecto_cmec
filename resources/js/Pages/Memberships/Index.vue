<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3'
import { useAlert } from '@/composables/useAlert'
import { onMounted, reactive, ref, computed, watch } from 'vue';
import Alerta from '@/Components/Alerta.vue';
import flatPickr from "vue-flatpickr-component";
import { Spanish } from "flatpickr/dist/l10n/es.js";
import "flatpickr/dist/flatpickr.css";

defineOptions({ layout: AuthenticatedLayout })

const { alertState, success, errorA, warning, hideAlert } = useAlert()

const props = defineProps({
    membership: {
        type: Object,
        default: () => null,
    },
    flash: {
        type: Object,
        default: () => ({})
    },
    errors: {
        type: Object,
        default: () => ({})
    },
    auth: {
        type: Object,
        default: () => ({})
    },
})

const page = usePage();

onMounted(() => {
    if (page.props.success || props.flash.success) success(page.props.success || props.flash.success)
    if (page.props.error || props.flash.error) errorA(page.props.error || props.flash.error)
    if (page.props.warning || props.flash.warning) warning(page.props.warning || props.flash.warning)
})

// ---------------------------------
// Estado
// ---------------------------------

const isSubmitting = ref(false);
const MAX_PRICES = 12;
const isNew = computed(() => !props.membership?.id);

const formData = reactive({
    id: null,
    name: '',
    description: '',
    benefits: '',
    prices: [{ start_date: '', end_date: '', amount: '' }],
});

// ---------------------------------
// Fill form
// ---------------------------------

const fillForm = (membership) => {
    formData.id = membership.id ?? null;
    formData.name = membership.name ?? '';
    formData.description = membership.description ?? '';
    formData.benefits = membership.benefits ?? '';

    formData.prices = membership.prices?.length
        ? membership.prices.map(p => ({
            start_date: p.start_date ?? '',
            end_date: p.end_date ?? '',
            amount: p.amount ?? '',
        }))
        : [{ start_date: '', end_date: '', amount: '' }];
};

watch(() => props.membership, (val) => {
    if (val?.id) fillForm(val);
}, { immediate: true });

// ---------------------------------
// Precios
// ---------------------------------

const canAddPrice = computed(() => formData.prices.length < MAX_PRICES);
const addPrice = () => { if (canAddPrice.value) formData.prices.push({ start_date: '', end_date: '', amount: '' }) };
const removePrice = (index) => formData.prices.splice(index, 1);

// ---------------------------------
// Submit
// ---------------------------------

const handleSubmit = () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;

    if (isNew.value) {
        router.post(route('memberships.store'), {
            name: formData.name,
            description: formData.description,
            benefits: formData.benefits,
            prices: formData.prices,
        }, {
            onFinish: () => { isSubmitting.value = false; },
        });
    } else {
        router.post(route('memberships.update', formData.id), {
            _method: 'PUT',
            id: formData.id,
            name: formData.name,
            description: formData.description,
            benefits: formData.benefits,
            prices: formData.prices,
        }, {
            onFinish: () => { isSubmitting.value = false; },
        });
    }
};

// ---------------------------------
// Flatpickr
// ---------------------------------

const flatpickrConfig = {
    locale: Spanish,
    dateFormat: 'Y-m-d',
    altInput: true,
    altFormat: 'F j, Y',
    wrap: false,
};
</script>

<template>

    <Head title="Membresías" />

    <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
        <div class="space-y-6">

            <!-- Encabezado -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Membresía</h3>
                <p class="text-sm text-gray-500">
                    {{ isNew ? 'Configura la membresía de tu aplicación' : 'Actualiza los datos de la membresía' }}
                </p>
            </div>

            <!-- DATOS DE LA MEMBRESIA -->
            <div
                class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800">

                <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 px-8 py-5">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="20" height="14" x="2" y="5" rx="2" />
                            <path d="M2 10h20" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Información de la membresía
                        </h2>
                        <p class="text-xs text-gray-500">Nombre, descripción y beneficios</p>
                    </div>
                </div>

                <div class="p-8 max-w-xl space-y-6">

                    <!-- Nombre -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Nombre
                        </label>
                        <input type="text" v-model="formData.name" placeholder="Ej. Membresía Anual"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        <span v-if="errors.name" class="text-red-500 text-xs flex justify-end mt-1">{{ errors.name
                            }}</span>
                    </div>

                    <!-- Descripcion -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Descripción
                        </label>
                        <input type="text" v-model="formData.description"
                            placeholder="Ej. Membresía para socios activos del año"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        <span v-if="errors.description" class="text-red-500 text-xs flex justify-end mt-1">{{
                            errors.description }}</span>
                    </div>

                    <!-- Beneficios -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Beneficios
                            <span
                                class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium capitalize bg-gray-200 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400 ml-1">opcional</span>
                        </label>
                        <textarea v-model="formData.benefits" rows="4"
                            placeholder="Describe los beneficios de esta membresía..."
                            class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        <span v-if="errors.benefits" class="text-red-500 text-xs flex justify-end mt-1">{{
                            errors.benefits }}</span>
                    </div>

                </div>
            </div>

            <!-- PERIODOS DE PRECIO -->
            <div
                class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800">

                <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 px-8 py-5">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Períodos de precio</h2>
                        <p class="text-xs text-gray-500">
                            {{ formData.prices.length }} de {{ MAX_PRICES }} períodos configurados
                        </p>
                    </div>
                </div>

                <div class="p-8">

                    <span v-if="errors.prices" class="text-red-500 text-xs block mb-4">{{ errors.prices }}</span>

                    <!-- Filas de precio -->
                    <div v-for="(price, index) in formData.prices" :key="index"
                        class="relative grid grid-cols-3 gap-4 my-3">

                        <!-- Fecha inicio -->
                        <div>
                            <label v-if="index === 0"
                                class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">
                                Fecha de inicio
                            </label>
                            <flat-pickr v-model="price.start_date" :config="flatpickrConfig"
                                placeholder="Fecha de inicio"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            <span v-if="errors[`prices.${index}.start_date`]" class="text-red-500 text-xs mt-0.5 block">
                                {{ errors[`prices.${index}.start_date`] }}
                            </span>
                        </div>

                        <!-- Fecha fin -->
                        <div>
                            <label v-if="index === 0"
                                class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">
                                Fecha de fin
                            </label>
                            <flat-pickr v-model="price.end_date" :config="flatpickrConfig" placeholder="Fecha de fin"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            <span v-if="errors[`prices.${index}.end_date`]" class="text-red-500 text-xs mt-0.5 block">
                                {{ errors[`prices.${index}.end_date`] }}
                            </span>
                        </div>

                        <!-- Monto -->
                        <div>
                            <label v-if="index === 0"
                                class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">
                                Monto
                            </label>
                            <div class="relative">
                                <span
                                    class="absolute left-0 top-1/2 -translate-y-1/2 border-r border-gray-200 px-3.5 py-1 text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                    $
                                </span>
                                <input v-model="price.amount" type="number" min="0" placeholder="0.00"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-[52px] text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            </div>
                            <span v-if="errors[`prices.${index}.amount`]" class="text-red-500 text-xs mt-0.5 block">
                                {{ errors[`prices.${index}.amount`] }}
                            </span>
                        </div>

                        <!-- Eliminar fila -->
                        <button v-if="formData.prices.length > 1" @click="removePrice(index)" type="button"
                            class="absolute -right-6 text-red-400 hover:text-red-600 transition-colors"
                            :class="index === 0 ? 'top-8' : 'top-3'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <button @click="addPrice" type="button" :disabled="!canAddPrice"
                        class="mt-2 text-sm flex items-center gap-1 transition-colors" :class="canAddPrice
                            ? 'text-brand-500 hover:text-brand-600'
                            : 'text-gray-300 cursor-not-allowed'">
                        + Agregar período
                        <span v-if="!canAddPrice" class="text-xs text-gray-400">(máximo {{ MAX_PRICES }})</span>
                    </button>

                </div>
            </div>

            <!-- ACCIONES -->
            <div
                class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800">
                <div class="flex items-center justify-end gap-3 p-6">
                    <button @click="handleSubmit" :disabled="isSubmitting"
                        class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span v-if="isSubmitting">Guardando...</span>
                        <span v-else>{{ isNew ? 'Crear Membresía' : 'Guardar Cambios' }}</span>
                    </button>
                </div>
            </div>

        </div>
    </div>

    <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
        :buttonText="alertState.buttonText" :cancelText="alertState.cancelText"
        @confirm="alertState.onConfirm ? alertState.onConfirm() : hideAlert()"
        @cancel="alertState.onCancel ? alertState.onCancel() : hideAlert()" @close="hideAlert()" />
</template>