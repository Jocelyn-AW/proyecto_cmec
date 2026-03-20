<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import flatPickr from "vue-flatpickr-component";
import { Spanish } from "flatpickr/dist/l10n/es.js";
import "flatpickr/dist/flatpickr.css";
import Dropzone from "@/Components/Dropzone.vue";
import { useFileUpload, useImageUpload } from "@/composables/useImageDropped";
import { computed, reactive, watch, ref } from "vue";
import SponsorsSection from '@/Components/SponsorsSection.vue'

defineOptions({
    layout: AuthenticatedLayout,
});

const props = defineProps({
    webinar: {
        type: Object,
        default: () => ({}),
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
    auth: {
        type: Object,
        default: () => ({}),
    },
    bank_details: {
        type: Array,
        default: () => [],
    },
});

// ---------------------------------
// Estado
// ---------------------------------

const isSubmitting = ref(false);
const sponsorsRef = ref(null);
const isTrashed = computed(() => !!props.webinar?.deleted_at);

const formData = reactive({
    id: null,
    topic: '',
    description: '',
    duration: '',
    objectives: '',
    organized_by: '',
    link: '',
    member_price: '',
    resident_price: '',
    guest_price: '',
    format: '',
    address: '',
    additional_info: '',
    bank_detail_id: '',
    sessions: [{ date: '', time: '' }],
});

// ---------------------------------
// Media
// ---------------------------------

const cover = useImageUpload({
    maxSizeMB: 1,
    acceptedTypes: ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'],
    onError: (msg) => alert(msg),
});

const previewCover = useImageUpload({
    maxSizeMB: 1,
    dimensions: { minWidth: 400, minHeight: 400, maxWidth: 800, maxHeight: 800 },
    acceptedTypes: ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'],
    onError: (msg) => alert(msg),
});

const pdf = useFileUpload({
    acceptedTypes: ['application/pdf'],
    maxSizeMB: 5,
    onError: (msg) => alert(msg),
});

// ---------------------------------
// Computeds de media
// ---------------------------------

const currentCover = computed(() =>
    cover.file.value ? cover.preview.value : (props.webinar?.cover_url ?? null)
);

const currentPreview = computed(() =>
    previewCover.file.value ? previewCover.preview.value : (props.webinar?.cover_preview_url ?? null)
);

const currentPdf = computed(() => {
    if (pdf.file.value) return pdf.preview.value;
    if (props.webinar?.program_url) {
        return {
            type: 'application/pdf',
            isFile: true,
            size: null,
            name: props.webinar.program_url.split('/').pop(),
        };
    }
    return null;
});

// ---------------------------------
// Fill form
// ---------------------------------

const fillForm = (webinar) => {
    formData.id = webinar.id ?? null;
    formData.topic = webinar.topic ?? '';
    formData.description = webinar.description ?? '';
    formData.objectives = webinar.objectives ?? '';
    formData.duration = webinar.duration ?? '';
    formData.organized_by = webinar.organized_by ?? '';
    formData.format = webinar.format ?? '';
    formData.link = webinar.link ?? '';
    formData.address = webinar.address ?? '';
    formData.additional_info = webinar.additional_info ?? '';
    formData.member_price = webinar.member_price ?? '';
    formData.resident_price = webinar.resident_price ?? '';
    formData.guest_price = webinar.guest_price ?? '';
    formData.bank_detail_id = webinar.bank_detail_id ?? '';

    formData.sessions = webinar.sessions?.length
        ? webinar.sessions.map(s => ({
            date: s.date?.split(/[T ]/)[0] ?? '',
            time: s.time?.substring(0, 5) ?? '',
        }))
        : [{ date: '', time: '' }];
};

watch(() => props.webinar, (val) => {
    if (val?.id) fillForm(val);
}, { immediate: true });

// ---------------------------------
// Sesiones
// ---------------------------------

const addSession = () => formData.sessions.push({ date: '', time: '' });
const removeSession = (index) => formData.sessions.splice(index, 1);

// ---------------------------------
// Submit
// ---------------------------------

const handleSubmit = () => {
    if (isTrashed.value || isSubmitting.value) return;

    if (!cover.file.value && !props.webinar?.cover_url) {
        alert('Por favor selecciona una imagen de portada para el webinar');
        return;
    }

    if (!previewCover.file.value && !props.webinar?.cover_preview_url) {
        alert('Por favor selecciona una imagen de preview para el webinar');
        return;
    }

    isSubmitting.value = true;

    const sponsorsData = sponsorsRef.value.getData();
    const data = new FormData();

    data.append('_method', 'PUT');
    data.append('id', formData.id);
    data.append('topic', formData.topic);
    data.append('description', formData.description);
    data.append('objectives', formData.objectives ?? '');
    data.append('duration', formData.duration);
    data.append('organized_by', formData.organized_by);
    data.append('member_price', formData.member_price);
    data.append('resident_price', formData.resident_price ?? '');
    data.append('guest_price', formData.guest_price ?? '');
    data.append('format', formData.format ?? '');
    data.append('link', formData.link ?? '');
    data.append('address', formData.address ?? '');
    data.append('additional_info', formData.additional_info ?? '');
    data.append('bank_detail_id', formData.bank_detail_id ?? '');

    formData.sessions.forEach((s, i) => {
        data.append(`sessions[${i}][date]`, s.date);
        data.append(`sessions[${i}][time]`, s.time);
    });

    if (cover.file.value) data.append('cover_image', cover.file.value);
    if (previewCover.file.value) data.append('cover_preview_image', previewCover.file.value);
    if (pdf.file.value) data.append('program_pdf', pdf.file.value);

    sponsorsData.platinum_sponsors.forEach(f => data.append('platinum_sponsors[]', f));
    sponsorsData.golden_sponsors.forEach(f => data.append('golden_sponsors[]', f));
    sponsorsData.silver_sponsors.forEach(f => data.append('silver_sponsors[]', f));
    sponsorsData.platinum_delete.forEach(id => data.append('platinum_delete[]', id));
    sponsorsData.golden_delete.forEach(id => data.append('golden_delete[]', id));
    sponsorsData.silver_delete.forEach(id => data.append('silver_delete[]', id));

    router.post(route('webinars.update', formData.id), data, {
        forceFormData: true,
        onFinish: () => { isSubmitting.value = false; },
    });
};

const handleCancel = () => router.get('/webinars');

// ---------------------------------
// Flatpickr configs
// ---------------------------------

const flatpickrConfig = {
    locale: Spanish,
    dateFormat: 'Y-m-d',
    altInput: true,
    altFormat: 'F j, Y',
    wrap: false,
};

const flatpickrTimeConfig = {
    enableTime: true,
    noCalendar: true,
    dateFormat: 'H:i',
    time_24hr: false,
    minuteIncrement: 1,
    wrap: false,
};
</script>

<template>

    <Head title="Webinars" />

    <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
        <div class="space-y-5">

            <!-- Encabezado -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Editar Webinar</h3>
                <p class="text-sm text-gray-500">Edita los detalles de un webinar desde esta sección</p>
            </div>

            <!-- Aviso webinar eliminado -->
            <div v-if="isTrashed"
                class="flex items-center gap-3 rounded-xl border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-700 dark:border-orange-500/30 dark:bg-orange-500/10 dark:text-orange-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                Este webinar ha sido eliminado. No es posible modificarlo hasta que sea restaurado.
            </div>

            <!-- DATOS GENERALES -->
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="grid grid-cols-4 gap-4 p-6">
                    <div>
                        <span class="text-sm text-gray-700">Datos Generales</span>
                    </div>
                    <div class="lg:col-span-3 space-y-6">

                        <!-- Imagenes -->
                        <div class="grid lg:grid-cols-3 gap-3">
                            <div class="lg:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Foto de Portada
                                </label>
                                <Dropzone :preview="currentCover" :is-dragging="cover.isDragging.value"
                                    hint="JPG, PNG, WEBP (max. 1MB)" @change="cover.handleChange"
                                    @drop="cover.handleDrop" @drag-enter="cover.handleDragEnter"
                                    @drag-leave="cover.handleDragLeave" @remove="cover.reset" />
                                <span v-if="errors.cover_image" class="text-red-500 text-xs flex justify-end">
                                    {{ errors.cover_image }}
                                </span>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Previsualización
                                </label>
                                <Dropzone :preview="currentPreview" :is-dragging="previewCover.isDragging.value"
                                    hint="Min. 400 x 400 (max. 1MB)" @change="previewCover.handleChange"
                                    @drop="previewCover.handleDrop" @drag-enter="previewCover.handleDragEnter"
                                    @drag-leave="previewCover.handleDragLeave" @remove="previewCover.reset" />
                                <span v-if="errors.cover_preview_image" class="text-red-500 text-xs flex justify-end">
                                    {{ errors.cover_preview_image }}
                                </span>
                            </div>
                        </div>

                        <!-- Tema -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Tema del Webinar
                            </label>
                            <input type="text" v-model="formData.topic"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            <span v-if="errors.topic" class="text-red-500 text-xs flex justify-end">{{ errors.topic
                                }}</span>
                        </div>

                        <!-- Descripcion -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Descripción
                            </label>
                            <textarea v-model="formData.description" rows="3" maxlength="5000"
                                class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            <div class="flex justify-between items-center mt-1">
                                <span v-if="errors.description" class="text-red-500 text-sm font-medium">{{
                                    errors.description }}</span>
                                <p class="text-xs text-gray-400 ml-auto">{{ formData.description.length }}/5000</p>
                            </div>
                        </div>

                        <!-- Objetivos -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Objetivos
                            </label>
                            <textarea v-model="formData.objectives" rows="3" maxlength="2000"
                                class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            <div class="flex justify-between items-center mt-1">
                                <span v-if="errors.objectives" class="text-red-500 text-sm font-medium">{{
                                    errors.objectives }}</span>
                                <p class="text-xs text-gray-400 ml-auto">{{ formData.objectives.length }}/2000</p>
                            </div>
                        </div>

                        <!-- Organizado por -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Organizado por
                            </label>
                            <input type="text" v-model="formData.organized_by"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            <span v-if="errors.organized_by" class="text-red-500 text-xs flex justify-end">{{
                                errors.organized_by }}</span>
                        </div>

                    </div>
                </div>
            </div>

            <!-- SPONSORS -->
            <SponsorsSection ref="sponsorsRef" :initial-platinum="webinar.platinum_sponsors_urls"
                :initial-golden="webinar.golden_sponsors_urls" :initial-silver="webinar.silver_sponsors_urls"
                :errors="errors" />

            <!-- DETALLES ADICIONALES -->
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="grid grid-cols-4 gap-4 p-6">
                    <div>
                        <span class="text-sm text-gray-700">Detalles Adicionales</span>
                    </div>
                    <div class="col-span-3 space-y-6">

                        <!-- Modalidad -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Modalidad
                            </label>
                            <select v-model="formData.format"
                                class="w-full py-2.5 rounded-lg border border-gray-300 px-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white/90">
                                <option value="">Seleccionar modalidad</option>
                                <option value="in_person">Presencial</option>
                                <option value="hybrid">Híbrida</option>
                                <option value="online">En línea</option>
                            </select>
                            <span v-if="errors.format" class="text-red-500 text-xs flex">{{ errors.format }}</span>
                        </div>

                        <template v-if="formData.format !== ''">
                            <!-- Direccion -->
                            <div v-if="formData.format !== 'online'">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Dirección
                                </label>
                                <input type="text" v-model="formData.address"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                <span v-if="errors.address" class="text-red-500 text-xs flex">{{ errors.address
                                    }}</span>
                            </div>

                            <!-- Informacion adicional -->
                            <div v-if="formData.format !== 'online'">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Información adicional
                                    <span
                                        class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium capitalize bg-gray-200 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400">opcional</span>
                                </label>
                                <input type="text" v-model="formData.additional_info"
                                    placeholder="Ej. Planta Alta, Auditorio 2"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                <span v-if="errors.additional_info" class="text-red-500 text-xs flex">{{
                                    errors.additional_info }}</span>
                            </div>

                            <!-- Link -->
                            <div v-if="formData.format !== 'in_person'">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Link de conexión
                                    <span v-if="formData.format === 'hybrid'"
                                        class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium capitalize bg-gray-200 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400">opcional</span>
                                </label>
                                <input type="text" v-model="formData.link"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                <span v-if="errors.link" class="text-red-500 text-xs flex">{{ errors.link }}</span>
                            </div>
                        </template>

                        <!-- PDF -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Programa del Webinar
                                <span
                                    class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium capitalize bg-gray-200 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400">opcional</span>
                            </label>
                            <Dropzone :preview="currentPdf" :is-dragging="pdf.isDragging.value"
                                :accept="'application/pdf'" label="Arrastra tu PDF aquí o haz clic para seleccionar"
                                hint="PDF (max. 5MB)" @change="pdf.handleChange" @drop="pdf.handleDrop"
                                @drag-enter="pdf.handleDragEnter" @drag-leave="pdf.handleDragLeave"
                                @remove="pdf.reset" />
                            <span v-if="errors.program_pdf" class="text-red-500 text-xs flex">{{ errors.program_pdf
                                }}</span>
                        </div>

                        <!-- Sesiones -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Horarios
                            </label>
                            <div v-for="(session, index) in formData.sessions" :key="index"
                                class="relative grid grid-cols-2 gap-4 my-3">
                                <div>
                                    <flat-pickr v-model="session.time" :config="flatpickrTimeConfig"
                                        class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                        placeholder="Selecciona una hora" />
                                    <span v-if="errors[`sessions.${index}.time`]" class="text-red-500 text-xs">
                                        {{ errors[`sessions.${index}.time`] }}
                                    </span>
                                </div>
                                <div>
                                    <flat-pickr v-model="session.date" :config="flatpickrConfig"
                                        class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                        placeholder="Selecciona una fecha" />
                                    <span v-if="errors[`sessions.${index}.date`]" class="text-red-500 text-xs">
                                        {{ errors[`sessions.${index}.date`] }}
                                    </span>
                                </div>
                                <button v-if="formData.sessions.length > 1" @click="removeSession(index)" type="button"
                                    class="absolute -right-6 top-3 text-red-400 hover:text-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                            <button @click="addSession" type="button"
                                class="text-sm text-brand-500 hover:text-brand-600 flex items-center gap-1">
                                + Agregar fecha
                            </button>
                        </div>

                        <!-- Duracion -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Duración total (en horas)
                            </label>
                            <input type="number" v-model="formData.duration" min="1"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            <span v-if="errors.duration" class="text-red-500 text-xs flex justify-end">{{
                                errors.duration }}</span>
                        </div>

                    </div>
                </div>
            </div>

            <!-- COSTOS + DETALLES DE PAGO -->
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="grid grid-cols-4 gap-4 p-6">
                    <div>
                        <span class="text-sm text-gray-700">Costos</span>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Para
                            miembros</label>
                        <div class="relative">
                            <span
                                class="absolute left-0 top-1/2 -translate-y-1/2 border-r border-gray-200 px-3.5 py-1 text-gray-500 dark:border-gray-800 dark:text-gray-400">$</span>
                            <input v-model="formData.member_price" type="number" min="0" placeholder="0.00"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-[62px] text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>
                        <span v-if="errors.member_price" class="text-red-500 text-xs flex justify-end">{{
                            errors.member_price
                            }}</span>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Para
                            residentes</label>
                        <div class="relative">
                            <span
                                class="absolute left-0 top-1/2 -translate-y-1/2 border-r border-gray-200 px-3.5 py-1 text-gray-500 dark:border-gray-800 dark:text-gray-400">$</span>
                            <input v-model="formData.resident_price" type="number" min="0" placeholder="0.00"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-[62px] text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>
                        <span v-if="errors.resident_price" class="text-red-500 text-xs flex justify-end">{{
                            errors.resident_price
                            }}</span>
                    </div>

                    <div class="mb-3">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Para Invitados
                            (no
                            socios)</label>
                        <div class="relative">
                            <span
                                class="absolute left-0 top-1/2 -translate-y-1/2 border-r border-gray-200 px-3.5 py-1 text-gray-500 dark:border-gray-800 dark:text-gray-400">$</span>
                            <input v-model="formData.guest_price" type="number" min="0" placeholder="0.00"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-[62px] text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>
                        <span v-if="errors.guest_price" class="text-red-500 text-xs flex justify-end">{{
                            errors.guest_price
                            }}</span>
                    </div>

                    <div>
                        <span class="text-sm text-gray-700">Detalles de pago</span>
                    </div>
                    <div class="col-span-3">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Cuenta bancaria para transferencias
                        </label>
                        <div class="relative">
                            <span
                                class="absolute left-0 top-1/2 -translate-y-1/2 border-r border-gray-200 px-3.5 py-1 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                <svg width="20" height="20" viewBox="0 0 576 512" xmlns="http://www.w3.org/2000/svg"
                                    fill="currentColor">
                                    <path
                                        d="M470.1 231.3s7.6 37.2 9.3 45H446c3.3-8.9 16-43.5 16-43.5-.2.3 3.3-9.1 5.3-14.9zM576 80v352c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V80c0-26.5 21.5-48 48-48h480c26.5 0 48 21.5 48 48zM152.5 331.2 215.7 176h-42.5l-39.3 106-4.3-21.5-14-71.4c-2.3-9.9-9.4-12.7-18.2-13.1H32.7l-.7 3.1c15.8 4 29.9 9.8 42.2 17.1l35.8 135zm94.4.2L272.1 176h-40.2l-25.1 155.4zm139.9-50.8c.2-17.7-10.6-31.2-33.7-42.3-14.1-7.1-22.7-11.9-22.7-19.2.2-6.6 7.3-13.4 23.1-13.4 13.1-.3 22.7 2.8 29.9 5.9l3.6 1.7 5.5-33.6c-7.9-3.1-20.5-6.6-36-6.6-39.7 0-67.6 21.2-67.8 51.4-.3 22.3 20 34.7 35.2 42.2 15.5 7.6 20.8 12.6 20.8 19.3-.2 10.4-12.6 15.2-24.1 15.2-16 0-24.6-2.5-37.7-8.3l-5.3-2.5-5.6 34.9c9.4 4.3 26.8 8.1 44.8 8.3 42.2.1 69.7-20.8 70-53zM528 331.4 495.6 176h-31.1c-9.6 0-16.9 2.8-21 12.9l-59.7 142.5H426s6.9-19.2 8.4-23.3H486c1.2 5.5 4.8 23.3 4.8 23.3z" />
                                </svg>
                            </span>
                            <select v-model="formData.bank_detail_id"
                                class="w-full py-2.5 pl-[55px] rounded-lg border border-gray-300 px-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white/90">
                                <option value="">Seleccionar cuenta</option>
                                <option v-for="detail in bank_details" :key="detail.id" :value="detail.id">
                                    {{ detail.bank }} | {{ detail.account_number || detail.clabe_number }}
                                </option>
                            </select>
                        </div>
                        <span v-if="errors.bank_detail_id" class="text-red-500 text-xs flex justify-end">{{
                            errors.bank_detail_id
                            }}</span>
                    </div>
                </div>
            </div>

            <!-- ACCIONES -->
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="grid grid-cols-4 gap-4 p-6">
                    <div class="col-span-2" />
                    <div class="col-span-2 flex justify-end items-center gap-3">
                        <button @click="handleCancel"
                            class="rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                            Cancelar
                        </button>
                        <button @click="handleSubmit" :disabled="isTrashed || isSubmitting"
                            :title="isTrashed ? 'Restaura el webinar para poder editarlo' : ''"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-white transition-colors" :class="isTrashed
                                ? 'bg-gray-400 cursor-not-allowed opacity-60'
                                : 'bg-brand-500 hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed'">
                            <span v-if="isSubmitting">Guardando...</span>
                            <span v-else>Actualizar Webinar</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>