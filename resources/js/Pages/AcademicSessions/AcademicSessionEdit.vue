<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, usePage, router } from "@inertiajs/vue3";
import flatPickr from "vue-flatpickr-component";
import { Spanish } from "flatpickr/dist/l10n/es.js";
import "flatpickr/dist/flatpickr.css";
import Dropzone from "@/Components/Dropzone.vue";
import { useFileUpload, useImageUpload } from "@/composables/useImageDropped";
import Alerta from '@/Components/Alerta.vue';
import { useAlert } from '@/composables/useAlert';
import { ref, watch, computed } from "vue";
import SponsorsSection from '@/Components/SponsorsSection.vue'

defineOptions({
    layout: AuthenticatedLayout,
});

const props = defineProps({
    academicSession: {
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

const { alertState, warning, hideAlert } = useAlert();
const page = usePage()
const sponsorsRef = ref(null)
/* const updateBanner = ref(false); */

const form = useForm({
    id: null,
    topic: "",
    description: "",
    objectives: "",
    duration: "",
    organized_by: "",
    sponsored_by: "",
    format: "",
    link: "",
    address: "",
    additional_info: "",
    member_price: "",
    resident_price: "",
    guest_price: "",
    bank_detail_id: "",
    cover_image: null,
    program_pdf: null,
    sessions: [
        { date: "", time: "" }
    ],
});

const addSession = () => {
    form.sessions.push({ date: "", time: "" });
};

const removeSession = (index) => {
    form.sessions.splice(index, 1);
};

const cover = useImageUpload({
    maxSizeMB: 1,
    acceptedTypes: ["image/jpeg", "image/png", "image/jpg", "image/webp"],
    onError: (message) => warning(message),
});

const previewCover = useImageUpload({
    maxSizeMB: 1,
    dimensions: {
        minWidth: 400,
        minHeight: 400,
        maxWidth: 800,
        maxHeight: 800,
    },
    acceptedTypes: ["image/jpeg", "image/png", "image/jpg", "image/webp"],
    onError: (message) => {
        warning(message);
    },
});

const pdf = useFileUpload({
    acceptedTypes: ['application/pdf'],
    maxSizeMB: 5,
    onError: (msg) => warning(msg),
});

const currentCover = computed(() => {
    if (cover.file.value) {
        return cover.preview.value;
    } else if (props.academicSession?.cover_url) {
        return props.academicSession?.cover_url;
    } else {
        return null;
    }
});

const currentPreview = computed(() => {
    if (previewCover.file.value) {
        return previewCover.preview.value;
    } else if (props.academicSession?.cover_preview_url) {
        return props.academicSession?.cover_preview_url;
    } else {
        return null;
    }
});

const currentPdf = computed(() => {
    if (pdf.file.value) {
        return pdf.preview.value;
    } else if (props.academicSession?.program_url) {
        return {
            type: 'application/pdf',
            isFile: true,
            size: null,
            name: props.academicSession?.program_url.split('/').pop(),
        };
    } else {
        return null;
    }
});

const getDateFromDateTime = (dateTime) => {
    if (!dateTime) return "";
    return dateTime.split('T')[0].split(' ')[0];
};

const fillForm = (academicSession) => {
    form.id = academicSession.id || null;
    form.topic = academicSession.topic || "";
    form.description = academicSession.description || "";
    form.objectives = academicSession.objectives || "";
    form.duration = academicSession.duration || "";
    form.organized_by = academicSession.organized_by || "";
    form.sponsored_by = academicSession.sponsored_by || "";
    form.format = academicSession.format || "";
    form.link = academicSession.link || "";
    form.address = academicSession.address || "";
    form.additional_info = academicSession.additional_info || "";
    form.member_price = academicSession.member_price || "";
    form.resident_price = academicSession.resident_price || "";
    form.guest_price = academicSession.guest_price || "";
    form.bank_detail_id = academicSession.bank_detail_id || "";

    if (academicSession.sessions && academicSession.sessions.length > 0) {
        form.sessions = academicSession.sessions.map(session => ({
            date: getDateFromDateTime(session.date),
            time: session.time ? session.time.substring(0, 5) : ''
        }));
    } else {
        form.sessions = [{ date: '', time: '' }];
    }
};

watch(
    [() => page.props.flash, () => props.academicSession],
    ([flash, academicSession]) => {
        if (flash?.error) {
            warning(flash.error);
        }

        if (flash?.success) {
            warning(flash.success);
        }

        if (academicSession && academicSession.id) {
            fillForm(academicSession);
        }
    },
    { immediate: true }
);

const handleSubmit = () => {
    const data = new FormData();
    const sponsorsData = sponsorsRef.value.getData()

    data.append('_method', 'PUT');

    data.append('topic', form.topic ?? '');
    data.append('description', form.description ?? '');
    data.append('objectives', form.objectives ?? '');
    data.append('duration', form.duration ?? '');
    data.append('organized_by', form.organized_by ?? '');
    data.append('sponsored_by', form.sponsored_by ?? '');
    data.append('format', form.format ?? '');
    data.append('link', form.link ?? '');
    data.append('address', form.address ?? '');
    data.append('additional_info', form.additional_info ?? '');

    data.append('member_price', form.member_price ?? '');
    data.append('resident_price', form.resident_price ?? '');
    data.append('guest_price', form.guest_price ?? '');
    data.append('bank_detail_id', form.bank_detail_id ?? '');

    form.sessions.forEach((session, index) => {
        data.append(`sessions[${index}][date]`, session.date ?? '');
        data.append(`sessions[${index}][time]`, session.time ?? '');
    });

    if (cover.file.value) {
        data.append('cover_image', cover.file.value);
    }

    if (previewCover.file.value) {
        data.append('cover_preview_image', previewCover.file.value);
    }

    if (pdf.file.value) {
        data.append('program_pdf', pdf.file.value);
    }

    // archivos nuevos
    sponsorsData.platinum_sponsors.forEach(f => data.append('platinum_sponsors[]', f))
    sponsorsData.golden_sponsors.forEach(f => data.append('golden_sponsors[]', f))
    sponsorsData.silver_sponsors.forEach(f => data.append('silver_sponsors[]', f))

    // ids a eliminar
    sponsorsData.platinum_delete.forEach(id => data.append('platinum_delete[]', id))
    sponsorsData.golden_delete.forEach(id => data.append('golden_delete[]', id))
    sponsorsData.silver_delete.forEach(id => data.append('silver_delete[]', id))

    /* if (updateBanner.value) {
        data.append('update_banner', '1');
        data.append('banner_title', form.topic ?? '');

        if (cover.file.value) {
            data.append('banner_image', cover.file.value);
        }

        if (form.link && form.link.trim() !== '') {
            data.append('banner_link', form.link);
        }
    } */

    router.post(`/academicsessions/${form.id}`, data, {
        forceFormData: true,
        onError: (errors) => {
            console.error('Errores de validación:', errors);
        },
    });
};

const handleCancel = () => {
    router.get(route('academicsessions.index'));
};

// Flatpickr
const flatpickrConfig = {
    locale: Spanish,
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "F j, Y",
};

const flatpickrTimeConfig = {
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
    time_24hr: false,
    minuteIncrement: 1,
};
</script>

<template>

    <Head title="Editar Sesión Académica" />

    <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
        <div class="space-y-5">

            <!-- ENCABEZADO -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Editar Sesión Académica</h3>
                <p class="text-sm text-gray-500">Modifica los datos de la sesión académica</p>
            </div>

            <!--  DATOS GENERALES -->
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="grid grid-cols-4 gap-4 p-6">
                    <div>
                        <span class="text-sm text-gray-700 dark:text-gray-400">Datos Generales</span>
                    </div>
                    <div class="col-span-3 space-y-6">
                        <div class="grid lg:grid-cols-3 gap-3">
                            <!-- FOTO -->
                            <div class="lg:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Foto de Portada
                                </label>
                                <Dropzone :preview="currentCover" :is-dragging="cover.isDragging.value"
                                    hint="JPG, PNG, WEBP (max. 1MB)" @change="cover.handleChange"
                                    @drop="cover.handleDrop" @drag-enter="cover.handleDragEnter"
                                    @drag-leave="cover.handleDragLeave" @remove="cover.reset" />
                                <span v-if="form.errors?.cover_image"
                                    class="text-red-500 text-sm flex justify-start mt-1">
                                    {{ form.errors?.cover_image }}
                                </span>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Previsualización
                                </label>
                                <Dropzone :preview="currentPreview" :is-dragging="previewCover.isDragging.value"
                                    hint="Min. 400 x 400 (max. 1MB) " @change="previewCover.handleChange"
                                    @drop="previewCover.handleDrop" @drag-enter="previewCover.handleDragEnter"
                                    @drag-leave="previewCover.handleDragLeave" @remove="previewCover.reset" />

                                <span v-if="errors.cover_image" class="text-red-500 text-xs flex justify-end">{{
                                    errors.cover_image }}</span>
                            </div>
                        </div>
                        <!-- TEMA -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Tema de la Sesión
                            </label>
                            <input type="text" v-model="form.topic"
                                placeholder="Ej. Inteligencia Artificial en Medicina"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            <span v-if="form.errors?.topic" class="text-red-500 text-sm flex justify-start mt-1">
                                {{ form.errors?.topic }}
                            </span>
                        </div>

                        <!-- DESCRIPCION -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Descripción
                            </label>
                            <textarea v-model="form.description" rows="3" maxlength="5000"
                                class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            <div class="flex justify-between items-center mt-1">
                                <span v-if="form.errors?.description" class="text-red-500 text-sm font-medium">
                                    {{ form.errors?.description }}
                                </span>
                                <p class="text-xs text-gray-400 ml-auto">{{ form.description.length }}/5000</p>
                            </div>
                        </div>

                        <!-- OBJETIVOS -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Objetivos
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 ml-1">
                                    opcional
                                </span>
                            </label>
                            <textarea v-model="form.objectives" rows="3" maxlength="2000"
                                class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            <div class="flex justify-between items-center mt-1">
                                <span v-if="form.errors?.objectives" class="text-red-500 text-sm font-medium">
                                    {{ form.errors?.objectives }}
                                </span>
                                <p class="text-xs text-gray-400 ml-auto">{{ form.objectives.length }}/2000</p>
                            </div>
                        </div>

                        <!-- ORGANIZADO POR -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Organizado por
                            </label>
                            <input type="text" v-model="form.organized_by" placeholder="Ej. Dpto. de Medicina Interna"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            <span v-if="form.errors?.organized_by" class="text-red-500 text-sm flex justify-start mt-1">
                                {{ form.errors?.organized_by }}
                            </span>
                        </div>

                        <!-- PATROCINIO -->
                        <!-- <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Patrocinado por
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 ml-1">
                                    opcional
                                </span>
                            </label>
                            <input type="text" v-model="form.sponsored_by" placeholder="Ej. Laboratorio XYZ"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            <span v-if="form.errors?.sponsored_by" class="text-red-500 text-sm flex justify-start mt-1">
                                {{ form.errors?.sponsored_by }}
                            </span>
                        </div> -->

                        <!-- ACTUALIZAR BANNER -->
                        <!-- <div
                            class="flex items-center justify-between rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 px-4 py-3">
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    ¿Actualizar banner de esta sesión?
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Se actualizará el banner (si existe) usando la portada, el título y el link de la
                                    sesión.
                                </p>
                            </div>
                            <button type="button" @click="updateBanner = !updateBanner"
                                :class="updateBanner ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-700'"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none">
                                <span :class="updateBanner ? 'translate-x-5' : 'translate-x-0'"
                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" />
                            </button>
                        </div> -->

                    </div>
                </div>
            </div>

            <SponsorsSection ref="sponsorsRef" :initial-platinum="academicSession.platinum_sponsors_urls"
                :initial-golden="academicSession.golden_sponsors_urls"
                :initial-silver="academicSession.silver_sponsors_urls" :errors="errors" @error="warning" />

            <!-- DETALLES ADICIONALES -->
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="grid grid-cols-4 gap-4 p-6">
                    <div>
                        <span class="text-sm text-gray-700 dark:text-gray-400">Detalles Adicionales</span>
                    </div>
                    <div class="col-span-3 space-y-6">

                        <!-- MODALIDAD -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Modalidad
                            </label>
                            <select v-model="form.format"
                                class="w-full py-2.5 rounded-lg border border-gray-300 px-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white/90">
                                <option value="">Seleccionar modalidad</option>
                                <option value="in_person">Presencial</option>
                                <option value="hybrid">Híbrida</option>
                                <option value="online">En línea</option>
                            </select>
                            <span v-if="form.errors?.format" class="text-red-500 text-sm flex justify-start mt-1">
                                {{ form.errors?.format }}
                            </span>
                        </div>

                        <!-- EXTENSIONES POR MODALIDAD -->
                        <template v-if="form.format !== ''">

                            <!-- DIRECCIÓN (PRESENCIAL / HÍBRIDO) -->
                            <div v-if="form.format !== 'online'">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Dirección
                                </label>
                                <input type="text" v-model="form.address"
                                    placeholder="Ej. Av. Universidad 3000, Ciudad de México"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                <span v-if="form.errors?.address" class="text-red-500 text-sm flex justify-start mt-1">
                                    {{ form.errors?.address }}
                                </span>
                            </div>

                            <!-- INFO ADICIONAL (PRESENCIAL / HÍBRIDO) -->
                            <div v-if="form.format !== 'online'">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Información adicional
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 ml-1">
                                        opcional
                                    </span>
                                </label>
                                <input type="text" v-model="form.additional_info"
                                    placeholder="Ej. Planta Alta, Auditorio 2"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                <span v-if="form.errors?.additional_info"
                                    class="text-red-500 text-sm flex justify-start mt-1">
                                    {{ form.errors?.additional_info }}
                                </span>
                            </div>

                            <!-- LINK (ONLINE / HÍBRIDO) -->
                            <div v-if="form.format !== 'in_person'">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Link de conexión
                                    <span v-if="form.format === 'hybrid'"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 ml-1">
                                        opcional
                                    </span>
                                </label>
                                <input type="url" v-model="form.link" placeholder="https://meet.google.com/..."
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                <span v-if="form.errors?.link" class="text-red-500 text-sm flex justify-start mt-1">
                                    {{ form.errors?.link }}
                                </span>
                            </div>

                        </template>

                        <!-- PDF -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Programa de la Sesión
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 ml-1">
                                    opcional
                                </span>
                            </label>
                            <Dropzone :preview="currentPdf" :is-dragging="pdf.isDragging.value"
                                :accept="'application/pdf'" label="Arrastra tu PDF aquí o haz clic para seleccionar"
                                hint="PDF (max. 5MB)" @change="pdf.handleChange" @drop="pdf.handleDrop"
                                @drag-enter="pdf.handleDragEnter" @drag-leave="pdf.handleDragLeave"
                                @remove="pdf.reset" />
                            <span v-if="form.errors?.program_pdf" class="text-red-500 text-sm flex justify-start mt-1">
                                {{ form.errors?.program_pdf }}
                            </span>
                        </div>

                        <!-- HORARIOS -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Horarios
                            </label>

                            <div v-for="(session, index) in form.sessions" :key="index"
                                class="relative grid grid-cols-2 gap-4 my-3">

                                <!-- HORA -->
                                <div>
                                    <flat-pickr v-model="session.time" :config="flatpickrTimeConfig"
                                        placeholder="Selecciona una hora"
                                        class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                    <span v-if="form.errors?.[`sessions.${index}.time`]"
                                        class="text-red-500 text-xs mt-0.5 block">
                                        {{ form.errors?.[`sessions.${index}.time`] }}
                                    </span>
                                </div>

                                <!-- FECHA -->
                                <div>
                                    <flat-pickr v-model="session.date" :config="flatpickrConfig"
                                        placeholder="Selecciona una fecha"
                                        class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                    <span v-if="form.errors?.[`sessions.${index}.date`]"
                                        class="text-red-500 text-xs mt-0.5 block">
                                        {{ form.errors?.[`sessions.${index}.date`] }}
                                    </span>
                                </div>

                                <!-- ELIMINAR SESIÓN -->
                                <button v-if="form.sessions.length > 1" type="button" @click="removeSession(index)"
                                    class="absolute -right-6 top-3 text-red-400 hover:text-red-600 transition-colors"
                                    title="Eliminar esta fecha">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>

                            <span v-if="form.errors?.sessions" class="text-red-500 text-sm block mb-2">
                                {{ form.errors?.sessions }}
                            </span>

                            <button type="button" @click="addSession"
                                class="text-sm text-brand-500 hover:text-brand-600 flex items-center gap-1 transition-colors mt-2">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <line x1="12" y1="5" x2="12" y2="19" />
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                </svg>
                                Agregar fecha
                            </button>
                        </div>

                        <!-- DURACIÓN -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Duración total (en horas)
                            </label>
                            <input type="number" v-model="form.duration" min="1" placeholder="Ej. 3"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            <span v-if="form.errors?.duration" class="text-red-500 text-sm flex justify-start mt-1">
                                {{ form.errors?.duration }}
                            </span>
                        </div>

                    </div>
                </div>
            </div>

            <!-- COSTOS + DETALLES DE PAGO -->
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="grid grid-cols-4 gap-4 p-6">

                    <div>
                        <span class="text-sm text-gray-700 dark:text-gray-400">Costos</span>
                    </div>

                    <!-- MIEMBRO -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Para miembros
                        </label>
                        <div class="relative">
                            <span
                                class="absolute left-0 top-1/2 -translate-y-1/2 border-r border-gray-200 px-3.5 py-1 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                $
                            </span>
                            <input v-model="form.member_price" type="number" min="0" placeholder="0.00"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-[62px] text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>
                        <span v-if="form.errors?.member_price" class="text-red-500 text-sm flex justify-start mt-1">
                            {{ form.errors?.member_price }}
                        </span>
                    </div>

                    <!-- RESIDENTE -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Para residentes
                        </label>
                        <div class="relative">
                            <span
                                class="absolute left-0 top-1/2 -translate-y-1/2 border-r border-gray-200 px-3.5 py-1 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                $
                            </span>
                            <input v-model="form.resident_price" type="number" min="0" placeholder="0.00"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-[62px] text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>
                        <span v-if="form.errors?.resident_price" class="text-red-500 text-xs flex justify-end mt-1">
                            {{ form.errors?.resident_price }}
                        </span>
                    </div>

                    <!-- INVITADO -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Para no miembros (invitados)
                        </label>
                        <div class="relative">
                            <span
                                class="absolute left-0 top-1/2 -translate-y-1/2 border-r border-gray-200 px-3.5 py-1 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                $
                            </span>
                            <input v-model="form.guest_price" type="number" min="0" placeholder="0.00"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-[62px] text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>
                        <span v-if="form.errors?.guest_price" class="text-red-500 text-xs flex justify-end mt-1">
                            {{ form.errors?.guest_price }}
                        </span>
                    </div>

                    <!-- DETALLES PAGO -->
                    <div>
                        <span class="text-sm text-gray-700 dark:text-gray-400">Detalles de pago</span>
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
                            <select v-model="form.bank_detail_id"
                                class="w-full py-2.5 pl-[55px] rounded-lg border border-gray-300 px-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white/90">
                                <option value="">Seleccionar cuenta</option>
                                <option v-for="detail in bank_details" :key="detail.id" :value="detail.id">
                                    {{ detail.bank }} | {{ detail.account_number || detail.clabe_number }}
                                </option>
                            </select>
                        </div>
                        <span v-if="form.errors?.bank_detail_id" class="text-red-500 text-xs flex justify-end mt-1">
                            {{ form.errors?.bank_detail_id }}
                        </span>
                    </div>

                </div>
            </div>

            <!-- ACCIONES -->
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-end gap-3 p-6">
                    <button type="button" @click="handleCancel"
                        class="rounded-lg border border-gray-300 bg-transparent px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors">
                        Cancelar
                    </button>
                    <button type="button" @click="handleSubmit" :disabled="form.processing"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                        </svg>
                        <span>{{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}</span>
                    </button>
                </div>
            </div>

        </div>
    </div>

    <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
        :buttonText="alertState.buttonText" :cancelText="alertState.cancelText"
        @confirm="alertState.onConfirm ? alertState.onConfirm() : hideAlert()" @cancel="hideAlert()"
        @close="hideAlert()" />
</template>