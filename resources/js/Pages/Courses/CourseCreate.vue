<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import flatPickr from "vue-flatpickr-component";
import { Spanish } from "flatpickr/dist/l10n/es.js";
import "flatpickr/dist/flatpickr.css";
import Dropzone from "@/Components/Dropzone.vue";
import { reactive } from "vue";
import { useFileUpload, useImageUpload } from "@/composables/useImageDropped";

defineOptions({
    layout: AuthenticatedLayout,
});

defineProps({
    courses: {
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
        type: Object,
        default: () => ({}),
    }
});

const formData = reactive({
    example: "",
    topic: "",
    date: "",
    time: "",
    description: "",
    duration: "",
    objectives: "",
    organized_by: "",
    member_price: "",
    resident_price: "",
    guest_price: "",
    bank_detail_id: ""
});

//una imagen
const cover = useImageUpload({
    maxSizeMB: 1,
    acceptedTypes: ["image/jpeg", "image/png", "image/jpg", "image/webp"],
    onError: (message) => {
        alert(message);
    },
});

const pdf = useFileUpload({
    acceptedTypes: ['application/pdf'],
    maxSizeMB: 5,
    onError: (msg) => alert(msg)
})


const handleSubmit = () => {
    if (!cover.file.value) {
        alert("Por favor selecciona una imagen de portada para el curso");
        return;
    }
    formData.cover_image = cover.file.value;

    if (pdf.file.value) {
        formData.program_pdf = pdf.file.value;
    }

    router.post("/courses/new", formData);
};

const handleCancel = () => {
    router.get('/courses');
};

const flatpickrConfig = {
    locale: Spanish,
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "F j, Y",
    wrap: false,
};

const flatpickrTimeConfig = {
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
    time_24hr: false,
    minuteIncrement: 1,
    wrap: false,
};
</script>

<template>
    <Head title="Cursos" />

    <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
        <div class="space-y-5">
            <div class="">
                <h3
                    class="text-lg font-semibold text-gray-800 dark:text-white/90"
                >
                    Nuevo Curso
                </h3>
                <p class="text-sm text-gray-500">
                    Crea un nuevo curso desde esta sección
                </p>
            </div>

            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <div class="grid grid-cols-4 gap-4 p-6">
                    <div class="">
                        <span class="text-sm text-gray-700">
                            Datos Generales</span
                        >
                    </div>
                    <div class="col-span-3 space-y-6">
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >
                                Foto de Portada
                            </label>
                            <Dropzone
                                :preview="cover.preview.value"
                                :is-dragging="cover.isDragging.value"
                                hint="JPG, PNG, WEBP (max. 1MB)"
                                @change="cover.handleChange"
                                @drop="cover.handleDrop"
                                @drag-enter="cover.handleDragEnter"
                                @drag-leave="cover.handleDragLeave"
                                @remove="cover.reset"
                            />
                            
                            <span v-if="errors.cover_image" class="text-red-500 text-xs flex justify-end">{{ errors.cover_image }}</span>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >
                                Tema del Curso
                            </label>
                            <input
                                type="text"
                                v-model="formData.topic"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                            />
                            <span v-if="errors.topic" class="text-red-500 text-xs flex justify-end">{{ errors.topic }}</span>
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >
                                Descripcion
                            </label>
                            <textarea
                                v-model="formData.description" rows="2" maxlength="500"
                                class="dark:bg-dark-900  w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                            />
                            <p class="mt-1 text-xs text-gray-400 text-right">{{ formData.description.length }}/500</p>
                            <span v-if="errors.description" class="text-red-500 text-xs flex justify-end">{{ errors.description }}</span>
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >
                                Objetivos
                            </label>
                            <textarea
                                v-model="formData.objectives" rows="3" maxlength="1000"
                                class="dark:bg-dark-900  w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                            />
                            
                            <p class="mt-1 text-xs text-gray-400 text-right">{{ formData.objectives.length }}/1000</p>
                            <span v-if="errors.objectives" class="text-red-500 text-xs flex justify-end">{{ errors.objectives }}</span>
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >
                                Organizado por
                            </label>
                            <input
                                type="text"
                                v-model="formData.organized_by"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                            />
                            <span v-if="errors.organized_by" class="text-red-500 text-xs flex justify-end">{{ errors.organized_by }}</span>
                        </div>
                        
                    </div>
                </div>
            </div>
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
                >
                <div class="grid grid-cols-4 gap-4 p-6">
                    <div class="">
                        <span class="text-sm text-gray-700">
                            Detalles Adicionales</span
                        >
                    </div>
                    <div class="col-span-3 space-y-6">
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >
                                Link de conexión 
                                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium capitalize bg-gray-200 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400">opcional</span>
                            </label>
                            <input
                                type="text"
                                v-model="formData.link"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                            />
                            <span v-if="errors.link" class="text-red-500 text-xs flex justify-end">{{ errors.link }}</span>
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >
                                Programa del Curso
                                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium capitalize bg-gray-200 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400">opcional</span>
                            </label>
                            <Dropzone
                                :preview="pdf.preview.value"
                                :is-dragging="pdf.isDragging.value"
                                :accept="'application/pdf'"
                                label="Arrastra tu PDF aqui o haz clic para seleccionar"
                                hint="PDF (max. 5MB)"
                                @change="pdf.handleChange"
                                @drop="pdf.handleDrop"
                                @drag-enter="pdf.handleDragEnter"
                                @drag-leave="pdf.handleDragLeave"
                                @remove="pdf.reset"
                            />
                            
                            <span v-if="errors.pdf_file" class="text-red-500 text-xs flex justify-end">{{ errors.pdf_file }}</span>
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >
                                Fecha y Hora de Inicio
                            </label>
                            <div class="relative grid grid-cols-2 gap-4">
                                <div>
                                    <flat-pickr
                                        v-model="formData.time"
                                        :config="flatpickrTimeConfig"
                                        class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pl-4 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                        placeholder="Selecciona una hora"
                                    />
                                    <span
                                        class="absolute text-gray-500 -translate-y-1/2 right-3 top-1/2 dark:text-gray-400"
                                    >
                                        <svg width="20" height="20" viewBox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="text-8xl text-stone-800 dark:text-stone-200">
                                            <path fill="currentColor"
                                                d="M5.673 0a.7.7 0 0 1 .7.7v1.309h7.517v-1.3a.7.7 0 0 1 1.4 0v1.3H18a2 2 0 0 1 2 1.999v13.993A2 2 0 0 1 18 20H2a2 2 0 0 1-2-1.999V4.008a2 2 0 0 1 2-1.999h2.973V.699a.7.7 0 0 1 .7-.699ZM1.4 7.742v10.259a.6.6 0 0 0 .6.6h16a.6.6 0 0 0 .6-.6V7.756zm5.267 6.877v1.666H5v-1.666zm4.166 0v1.666H9.167v-1.666zm4.167 0v1.666h-1.667v-1.666zm-8.333-3.977v1.666H5v-1.666zm4.166 0v1.666H9.167v-1.666zm4.167 0v1.666h-1.667v-1.666zM4.973 3.408H2a.6.6 0 0 0-.6.6v2.335l17.2.014V4.008a.6.6 0 0 0-.6-.6h-2.71v.929a.7.7 0 0 1-1.4 0v-.929H6.373v.92a.7.7 0 0 1-1.4 0z">
                                            </path>
                                        </svg>
                                    </span>
                                    <span v-if="errors.time" class="text-red-500 text-xs flex justify-end">{{ errors.time }}</span>
                                </div>
                                <div>
                                    <flat-pickr
                                        v-model="formData.date"
                                        :config="flatpickrConfig"
                                        class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pl-4 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                        placeholder="Selecciona una fecha"
                                    />
                                    <span v-if="errors.date" class="text-red-500 text-xs flex justify-end">{{ errors.date }}</span>
                                </div>
                                
                            </div>
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >
                                Duracion (en horas)
                            </label>
                            <input
                                type="number"
                                v-model="formData.duration"
                                min="1"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                            />
                            <span v-if="errors.duration" class="text-red-500 text-xs flex justify-end">{{ errors.duration }}</span>
                        </div>
                    
                    </div>
                </div>
            </div>
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
                >
                <div class="grid grid-cols-4 gap-4 p-6">
                    <div class="">
                        <span class="text-sm text-gray-700">Costos</span>
                    </div>
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        >
                            Para miembros
                        </label>
                        <div class="relative">
                            <span
                                class="absolute left-0 top-1/2 -translate-y-1/2 border-r border-gray-200 px-3.5 py-1 text-gray-500 dark:border-gray-800 dark:text-gray-400"
                            >
                                $
                            </span>
                            <input
                                v-model="formData.member_price"
                                type="number"
                                min="0"
                                placeholder="0.00"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-[62px] text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                            />
                        </div>
                        <span v-if="errors.member_price" class="text-red-500 text-xs flex justify-end">{{ errors.member_price }}</span>
                    </div>
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        >
                            Para residentes
                        </label>
                        <div class="relative">
                            <span
                                class="absolute left-0 top-1/2 -translate-y-1/2 border-r border-gray-200 px-3.5 py-1 text-gray-500 dark:border-gray-800 dark:text-gray-400"
                            >
                                $
                            </span>
                            <input
                                v-model="formData.resident_price"
                                type="number"
                                min="0"
                                placeholder="0.00"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-[62px] text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                            />
                            <span v-if="errors.resident_price" class="text-red-500 text-xs flex justify-end">{{ errors.resident_price }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        >
                            Para no miembros (invitados)
                        </label>
                        <div class="relative">
                            <span
                                class="absolute left-0 top-1/2 -translate-y-1/2 border-r border-gray-200 px-3.5 py-1 text-gray-500 dark:border-gray-800 dark:text-gray-400"
                            >
                                $
                            </span>
                            <input
                                v-model="formData.guest_price"
                                type="number"
                                min="0"
                                placeholder="0.00"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-[62px] text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                            />
                            <span v-if="errors.guest_price" class="text-red-500 text-xs flex justify-end">{{ errors.guest_price }}</span>
                        </div>
                    </div>
                    <div class="">
                        <span class="text-sm text-gray-700">Detalles de pago</span>
                    </div>
                    <div class="col-span-3">
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        >
                            Cuenta bancaria para transferencias
                        </label>
                        <div class="relative">
                            <span
                                class="absolute left-0 top-1/2 -translate-y-1/2 border-r border-gray-200 px-3.5 py-1 text-gray-500 dark:border-gray-800 dark:text-gray-400"
                            >
                                <svg width="20" height="20" viewBox="0 0 576 512" xmlns="http://www.w3.org/2000/svg"
                                    class="text-8xl" fill="currentColor">
                                    <path
                                        d="M470.1 231.3s7.6 37.2 9.3 45H446c3.3-8.9 16-43.5 16-43.5-.2.3 3.3-9.1 5.3-14.9zM576 80v352c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V80c0-26.5 21.5-48 48-48h480c26.5 0 48 21.5 48 48zM152.5 331.2 215.7 176h-42.5l-39.3 106-4.3-21.5-14-71.4c-2.3-9.9-9.4-12.7-18.2-13.1H32.7l-.7 3.1c15.8 4 29.9 9.8 42.2 17.1l35.8 135zm94.4.2L272.1 176h-40.2l-25.1 155.4zm139.9-50.8c.2-17.7-10.6-31.2-33.7-42.3-14.1-7.1-22.7-11.9-22.7-19.2.2-6.6 7.3-13.4 23.1-13.4 13.1-.3 22.7 2.8 29.9 5.9l3.6 1.7 5.5-33.6c-7.9-3.1-20.5-6.6-36-6.6-39.7 0-67.6 21.2-67.8 51.4-.3 22.3 20 34.7 35.2 42.2 15.5 7.6 20.8 12.6 20.8 19.3-.2 10.4-12.6 15.2-24.1 15.2-16 0-24.6-2.5-37.7-8.3l-5.3-2.5-5.6 34.9c9.4 4.3 26.8 8.1 44.8 8.3 42.2.1 69.7-20.8 70-53zM528 331.4 495.6 176h-31.1c-9.6 0-16.9 2.8-21 12.9l-59.7 142.5H426s6.9-19.2 8.4-23.3H486c1.2 5.5 4.8 23.3 4.8 23.3z">
                                    </path>
                                </svg>
                            </span>
                            <select name="bank_detail_id" id="bank_detail_id" v-model="formData.bank_detail_id" 
                                class="w-full py-2.5 pl-[55px] rounded-lg border border-gray-300 px-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Seleccionar cuenta</option>
                                <option v-for="detail in bank_details" :key="detail.id" :value="detail.id">
                                    {{ detail.bank }} | {{ detail.account_number || detail.clabe_number }}
                                </option>
                            </select>
                        </div>
                        <span v-if="errors.bank_detail_id" class="text-red-500 text-xs flex justify-end">{{ errors.bank_detail_id }}</span>
                    </div>
                </div>
            </div>
            
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
                >
                <div class="grid grid-cols-4 gap-4 p-6">
                    <div class="col-span-2">
                        <span class="text-sm text-gray-700"></span>
                    </div>
                    <div class="col-span-2 flex justify-end items-center">
                        <button @click="handleCancel"
                            class="rounded-lg border border-gray-300 bg-transparent px-4 mx-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 ">
                            Cancelar
                        </button>
                        <button 
                            @click="handleSubmit"
                            class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 ">
                            Guardar Curso
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
