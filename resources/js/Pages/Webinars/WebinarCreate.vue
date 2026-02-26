<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import flatPickr from "vue-flatpickr-component";
import { Spanish } from "flatpickr/dist/l10n/es.js";
import "flatpickr/dist/flatpickr.css";
import Dropzone from "@/Components/Dropzone.vue";
import { reactive } from "vue";
import { useFileUpload, useImageUpload } from "@/composables/useImageDropped";
import Alerta from '@/Components/Alerta.vue'
import { useAlert } from '@/composables/useAlert'

defineOptions({
    layout: AuthenticatedLayout,
});

defineProps({
    webinars: {
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
    }
});

const { alertState, success, errorA, warning } = useAlert()

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
    guest_price: ""
});

//una imagen
const cover = useImageUpload({
    maxSizeMB: 1,
    acceptedTypes: ["image/jpeg", "image/png", "image/jpg", "image/webp"],
    onError: (message) => {
        warning(message);
    },
});

const pdf = useFileUpload({
    acceptedTypes: ['application/pdf'],
    maxSizeMB: 5,
    onError: (msg) => warning(msg)
})


const handleSubmit = () => {
    if (!cover.file.value) {
        warning("Por favor selecciona una imagen de portada para el webinar");
        return;
    }

    const data = new FormData()
    data.append('topic', formData.topic)
    data.append('description', formData.description)
    data.append('objectives', formData.objectives ?? '')
    data.append('date', formData.date)
    data.append('time', formData.time)
    data.append('duration', formData.duration)
    data.append('organized_by', formData.organized_by)
    data.append('member_price', formData.member_price)
    data.append('resident_price', formData.resident_price ?? '')
    data.append('guest_price', formData.guest_price ?? '')
    data.append('link', formData.link ?? '')
    data.append('cover_image', cover.file.value)

    if (pdf.file.value) {
        data.append('program_pdf', pdf.file.value)
    }

    router.post('/webinars/new', data, {
        forceFormData: true,
        preserveScroll: true,
        preserveState: true,
    })
}

const handleConfirm = () => {
    alertState.value.onConfirm?.()
    alertState.value.show = false
}

const handleCancel = () => {
    router.get('/webinars');
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

    <Head title="Webinars" />

    <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
        <div class="space-y-5">
            <div class="">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Nuevo Webinar
                </h3>
                <p class="text-sm text-gray-500">
                    Crea un nuevo webinar desde esta sección
                </p>
            </div>

            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="grid grid-cols-4 gap-4 p-6">
                    <div class="">
                        <span class="text-sm text-gray-700">
                            Datos Generales</span>
                    </div>
                    <div class="col-span-3 space-y-6">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Foto de Portada
                            </label>
                            <Dropzone :preview="cover.preview.value" :is-dragging="cover.isDragging.value"
                                hint="JPG, PNG, WEBP (max. 1MB)" @change="cover.handleChange" @drop="cover.handleDrop"
                                @drag-enter="cover.handleDragEnter" @drag-leave="cover.handleDragLeave"
                                @remove="cover.reset" />

                            <span v-if="errors.cover_image" class="text-red-500 text-sm flex justify-start">{{
                                errors.cover_image }}</span>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Tema del Webinar
                            </label>
                            <input type="text" v-model="formData.topic"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            <span v-if="errors.topic" class="text-red-500 text-sm flex justify-start">{{ errors.topic
                                }}</span>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Descripcion
                            </label>
                            <textarea type="text" v-model="formData.description" rows="4"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            <div class="flex justify-between items-center mt-1">
                                <div class="flex-1">
                                    <span v-if="errors.description" class="text-red-500 text-sm font-medium">
                                        {{ errors.description }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-400">
                                    {{ formData.description.length }}/500
                                </p>
                            </div>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Objetivos
                            </label>
                            <textarea type="text" v-model="formData.objectives" rows="4"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            <div class="flex justify-between items-center mt-1">
                                <div class="flex-1">
                                    <span v-if="errors.objectives" class="text-red-500 text-sm font-medium">
                                        {{ errors.objectives }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-400">
                                    {{ formData.objectives.length }}/1000
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Fecha y Hora de Inicio
                            </label>
                            <div class="relative grid grid-cols-2 gap-4">
                                <div>
                                    <flat-pickr v-model="formData.date" :config="flatpickrConfig"
                                        class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pl-4 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                        placeholder="Selecciona una fecha" />
                                    <span v-if="errors.date" class="text-red-500 text-sm flex justify-start">{{
                                        errors.date }}</span>
                                </div>
                                <div>
                                    <flat-pickr v-model="formData.time" :config="flatpickrTimeConfig"
                                        class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pl-4 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                        placeholder="Selecciona una hora" />
                                    <span
                                        class="absolute text-gray-500 -translate-y-1/2 right-3 top-1/2 dark:text-gray-400">
                                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M3.04175 9.99984C3.04175 6.15686 6.1571 3.0415 10.0001 3.0415C13.8431 3.0415 16.9584 6.15686 16.9584 9.99984C16.9584 13.8428 13.8431 16.9582 10.0001 16.9582C6.1571 16.9582 3.04175 13.8428 3.04175 9.99984ZM10.0001 1.5415C5.32867 1.5415 1.54175 5.32843 1.54175 9.99984C1.54175 14.6712 5.32867 18.4582 10.0001 18.4582C14.6715 18.4582 18.4584 14.6712 18.4584 9.99984C18.4584 5.32843 14.6715 1.5415 10.0001 1.5415ZM9.99998 10.7498C9.58577 10.7498 9.24998 10.4141 9.24998 9.99984V5.4165C9.24998 5.00229 9.58577 4.6665 9.99998 4.6665C10.4142 4.6665 10.75 5.00229 10.75 5.4165V9.24984H13.3334C13.7476 9.24984 14.0834 9.58562 14.0834 9.99984C14.0834 10.4141 13.7476 10.7498 13.3334 10.7498H10.0001H9.99998Z"
                                                fill="" />
                                        </svg>
                                    </span>
                                    <span v-if="errors.time" class="text-red-500 text-sm flex justify-start">{{
                                        errors.time }}</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Duracion (en horas)
                            </label>
                            <input type="number" v-model="formData.duration" min="1"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            <span v-if="errors.duration" class="text-red-500 text-sm flex justify-start">{{
                                errors.duration }}</span>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Organizado por
                            </label>
                            <input type="text" v-model="formData.organized_by"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            <span v-if="errors.organized_by" class="text-red-500 text-sm flex justify-start">{{
                                errors.organized_by }}</span>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Link de conexión
                                <span
                                    class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium capitalize bg-gray-200 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400">opcional</span>
                            </label>
                            <input type="text" v-model="formData.link"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            <span v-if="errors.link" class="text-red-500 text-sm flex justify-start">{{ errors.link
                                }}</span>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Programa del Webinar
                                <span
                                    class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium capitalize bg-gray-200 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400">opcional</span>
                            </label>
                            <Dropzone :preview="pdf.preview.value" :is-dragging="pdf.isDragging.value"
                                :accept="'application/pdf'" label="Arrastra tu PDF aqui o haz clic para seleccionar"
                                hint="PDF (max. 5MB)" @change="pdf.handleChange" @drop="pdf.handleDrop"
                                @drag-enter="pdf.handleDragEnter" @drag-leave="pdf.handleDragLeave"
                                @remove="pdf.reset" />

                            <span v-if="errors.pdf_file" class="text-red-500 text-sm flex justify-start">{{
                                errors.pdf_file }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="grid grid-cols-4 gap-4 p-6">
                    <div class="">
                        <span class="text-sm text-gray-700"> Costos</span>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Para miembros
                        </label>
                        <div class="relative">
                            <span
                                class="absolute left-0 top-1/2 -translate-y-1/2 border-r border-gray-200 px-3.5 py-1 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                $
                            </span>
                            <input v-model="formData.member_price" type="number" min="0" placeholder="0.00"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-[62px] text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>
                        <span v-if="errors.member_price" class="text-red-500 text-sm flex justify-start">{{
                            errors.member_price }}</span>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Para residentes
                        </label>
                        <div class="relative">
                            <span
                                class="absolute left-0 top-1/2 -translate-y-1/2 border-r border-gray-200 px-3.5 py-1 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                $
                            </span>
                            <input v-model="formData.resident_price" type="number" min="0" placeholder="0.00"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-[62px] text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Para no miembros (invitados)
                        </label>
                        <div class="relative">
                            <span
                                class="absolute left-0 top-1/2 -translate-y-1/2 border-r border-gray-200 px-3.5 py-1 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                $
                            </span>
                            <input v-model="formData.guest_price" type="number" min="0" placeholder="0.00"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-[62px] text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="grid grid-cols-4 gap-4 p-6">
                    <div class="col-span-2">
                        <span class="text-sm text-gray-700"></span>
                    </div>
                    <div class="col-span-2 flex justify-end items-center">
                        <button @click="handleCancel"
                            class="rounded-lg border border-gray-300 bg-transparent px-4 mx-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 ">
                            Cancelar
                        </button>
                        <button @click="handleSubmit"
                            class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 ">
                            Guardar Webinar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
            :buttonText="alertState.buttonText" :cancelText="alertState.cancelText" @confirm="handleConfirm"
            @close="alertState.show = false" />
    </div>
</template>
