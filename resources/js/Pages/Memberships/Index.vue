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
    membership: { type: Object, default: () => null },
    flash: { type: Object, default: () => ({}) },
    errors: { type: Object, default: () => ({}) },
    auth: { type: Object, default: () => ({}) },
})

const page = usePage();

onMounted(() => {
    checkFlash(props.flash)
    if (Object.keys(props.errors).length > 0) {
        errorA('Hubo un error en los datos subidos.')
    }
})

watch(() => props.flash, (val) => {
    checkFlash(val)
}, { deep: true })

// Funcion auxiliar
const checkFlash = (flash) => {
    if (flash?.success) success(flash.success)
    if (flash?.error) errorA(flash.error)
    if (flash?.warning) warning(flash.warning)
}

// Al equvocarse
watch(() => props.errors, (val) => {
    if (Object.keys(val).length > 0) {
        errorA('Hubo un error en los datos subidos.')
    }
}, { deep: true })

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
    prices: [{ start_date: '', end_date: '', amount_general: '', amount_preferential: '' }],
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
            amount_general: p.amount_general ?? '',
            amount_preferential: p.amount_preferential ?? '',
        }))
        : [{ start_date: '', end_date: '', amount_general: '', amount_preferential: '' }];
};

watch(() => props.membership, (val) => {
    if (val?.id && Object.keys(props.errors).length === 0) {
        fillForm(val);
    }
}, { immediate: true });

// ---------------------------------
// Precios
// ---------------------------------

const canAddPrice = computed(() => formData.prices.length < MAX_PRICES);
const addPrice = () => {
    if (canAddPrice.value)
        formData.prices.push({ start_date: '', end_date: '', amount_general: '', amount_preferential: '' });
};
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
            preserveState: true,
            preserveScroll: true,
            onFinish: () => { isSubmitting.value = false; },
            onSuccess: () => success('Información actualizada con éxito.'),
            onError: () => errorA('Hubo un problema al actualizar la información.'),
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
            preserveState: true,
            preserveScroll: true,
            only: ['membership', 'flash', 'errors'],
            onFinish: () => { isSubmitting.value = false; },
            onSuccess: () => success('Información actualizada con éxito.'),
            onError: () => errorA('Hubo un problema al actualizar la información.'),
        })
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
    parseDate: (dateStr) => {
        const [year, month, day] = dateStr.split('-').map(Number);
        return new Date(year, month - 1, day);
    },
};
</script>

<template>

    <Head title="Membresía" />



    <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
        :buttonText="alertState.buttonText" :cancelText="alertState.cancelText"
        @confirm="alertState.onConfirm ? alertState.onConfirm() : hideAlert()"
        @cancel="alertState.onCancel ? alertState.onCancel() : hideAlert()" @close="hideAlert()" />

    <div class="min-h-screen">

        <!-- ENCABEZADO -->
        <div
            class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800">
            <div class="flex flex-col lg:flex-row">

                <div class="flex flex-1 flex-col justify-center p-8 lg:p-10">
                    <p class="text-sm font-medium text-brand-600">Configuración de la aplicación</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-gray-800 dark:text-white/90 sm:text-3xl">
                        Membresía
                    </h1>
                    <p class="mt-3 max-w-md text-sm leading-relaxed text-gray-500">
                        {{ isNew
                            ? 'Configura la membresía de tu aplicación. Define el nombre, descripción, beneficios y los períodos de precio disponibles.'
                        : 'Gestiona la membresía activa. Puedes actualizar su información y ajustar los períodos de precio en cualquier momento.'
                        }}
                    </p>
                    <div class="mt-5">
                        <span v-if="!isNew"
                            class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700 dark:bg-green-500/10 dark:text-green-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                            Membresía activa
                        </span>
                        <span v-else
                            class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                            Sin configurar
                        </span>
                    </div>
                </div>

                <!-- DECORACION -->
                <div class="relative h-40 w-full lg:h-auto lg:w-64 xl:w-72 overflow-hidden">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-brand-600 via-brand-500 to-brand-400 opacity-10 dark:opacity-5" />
                    <div class="absolute inset-0 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="0.6" stroke-linecap="round" stroke-linejoin="round"
                            class="h-36 w-36 text-brand-200 dark:text-brand-800">
                            <rect width="20" height="14" x="2" y="5" rx="2" />
                            <path d="M2 10h20" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- FORMULARIO: DOS COLUMNAS -->
        <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,2fr)]">

            <!-- IZQUIERDA: DATOS -->
            <div
                class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800">

                <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 px-6 py-4">
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

                <div class="p-6 space-y-4">

                    <!-- NOMBRE -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nombre</label>
                        <input type="text" v-model="formData.name" placeholder="Ej. Membresía Anual"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        <span v-if="errors.name" class="text-red-500 text-xs flex justify-end mt-1">{{ errors.name
                            }}</span>
                    </div>

                    <!-- DESCRIPCION -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Descripción</label>
                        <input type="text" v-model="formData.description"
                            placeholder="Ej. Membresía para socios activos del año"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        <span v-if="errors.description" class="text-red-500 text-xs flex justify-end mt-1">{{
                            errors.description }}</span>
                    </div>

                    <!-- BENEFICIOS -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Beneficios
                            <span
                                class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400 ml-1">opcional</span>
                        </label>
                        <textarea v-model="formData.benefits" rows="3"
                            placeholder="Describe los beneficios de esta membresía..."
                            class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 resize-none" />
                        <span v-if="errors.benefits" class="text-red-500 text-xs flex justify-end mt-1">{{
                            errors.benefits }}</span>
                    </div>

                </div>
            </div>

            <!-- DERECHA: FECHAS -->
            <div
                class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800">

                <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 px-6 py-4">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Períodos de precio</h2>
                        <p class="text-xs text-gray-500">{{ formData.prices.length }} de {{ MAX_PRICES }} períodos
                            configurados</p>
                    </div>
                </div>

                <div class="p-6">

                    <span v-if="errors.prices" class="text-red-500 text-xs block mb-4">{{ errors.prices }}</span>

                    <!-- Cabecera -->
                    <div class="grid grid-cols-[1fr_1fr_1.5fr_1.5fr_20px] gap-3 mb-2 px-1">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Fecha inicio</span>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Fecha fin</span>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Precio General</span>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Precio Preferencial (Miembros)</span>
                        <span></span>
                    </div>

                    <!-- Todas las filas -->
                    <div class="space-y-3">
                        <div v-for="(price, index) in formData.prices" :key="index"
                            class="grid grid-cols-[1fr_1fr_1.5fr_1.5fr_20px] gap-3 items-start">

                            <!-- Fecha inicio -->
                            <div>
                                <flat-pickr v-model="price.start_date" :config="flatpickrConfig" placeholder="Inicio"
                                    class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                <span v-if="errors[`prices.${index}.start_date`]"
                                    class="text-red-500 text-xs mt-0.5 block">
                                    {{ errors[`prices.${index}.start_date`] }}
                                </span>
                            </div>

                            <!-- Fecha fin -->
                            <div>
                                <flat-pickr v-model="price.end_date" :config="flatpickrConfig" placeholder="Fin"
                                    class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                <span v-if="errors[`prices.${index}.end_date`]"
                                    class="text-red-500 text-xs mt-0.5 block">
                                    {{ errors[`prices.${index}.end_date`] }}
                                </span>
                            </div>

                            <!-- Monto General -->
                            <div>
                                <div class="relative">
                                    <span
                                        class="absolute left-0 top-1/2 -translate-y-1/2 border-r border-gray-200 px-3 py-1 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                        $
                                    </span>
                                    <input v-model="price.amount_general" type="number" min="0" placeholder="0.00"
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-9 pr-3 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                </div>
                                <span v-if="errors[`prices.${index}.amount_general`]"
                                    class="text-red-500 text-xs mt-0.5 block">
                                    {{ errors[`prices.${index}.amount_general`] }}
                                </span>
                            </div>

                            <!-- Monto Preferencial -->
                            <div>
                                <div class="relative">
                                    <span
                                        class="absolute left-0 top-1/2 -translate-y-1/2 border-r border-gray-200 px-3 py-1 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                        $
                                    </span>
                                    <input v-model="price.amount_preferential" type="number" min="0" placeholder="0.00"
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-9 pr-3 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                </div>
                                <span v-if="errors[`prices.${index}.amount_preferential`]"
                                    class="text-red-500 text-xs mt-0.5 block">
                                    {{ errors[`prices.${index}.amount_preferential`] }}
                                </span>
                            </div>

                            <!-- Eliminar -->
                            <div class="flex items-center h-11">
                                <button v-if="formData.prices.length > 1" @click="removePrice(index)" type="button"
                                    class="text-gray-300 hover:text-red-500 transition-colors dark:text-gray-600 dark:hover:text-red-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button @click="addPrice" type="button" :disabled="!canAddPrice"
                        class="mt-4 text-sm flex items-center gap-1 transition-colors"
                        :class="canAddPrice ? 'text-brand-500 hover:text-brand-600' : 'text-gray-300 cursor-not-allowed dark:text-gray-600'">
                        + Agregar período
                        <span v-if="!canAddPrice" class="text-xs text-gray-400">(máximo {{ MAX_PRICES }})</span>
                    </button>

                </div>
            </div>

        </div>

        <!-- ACCIONES -->
        <div
            class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800">
            <div class="flex items-center justify-between gap-3 px-8 py-5">
                <p class="text-xs text-gray-400 dark:text-gray-500">
                    {{ isNew
                        ? 'Los cambios se guardarán como la membresía activa de la aplicación.'
                        : `Última actualización: ${membership?.updated_at ? new
                            Date(membership.updated_at).toLocaleDateString('es-MX', {
                                day: 'numeric', month: 'long', year:
                                    'numeric'
                            }) : '—'}`
                    }}
                </p>
                <button @click="handleSubmit" :disabled="isSubmitting"
                    class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <span v-if="isSubmitting">Guardando...</span>
                    <span v-else>{{ isNew ? 'Crear Membresía' : 'Guardar Cambios' }}</span>
                </button>
            </div>
        </div>

    </div>
</template>