<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive, computed, ref, watch } from 'vue';
import states from '@/composables/useStatesAndCities';
import { usePage } from '@inertiajs/vue3';
import Dropzone from '@/Components/Dropzone.vue';
import { useImageUpload } from '@/composables/useImageDropped';

defineOptions({
    layout: AuthenticatedLayout
})

const props = defineProps({
    flash: { type: Object, default: () => ({}) },
    errors: { type: Object, default: () => ({}) },
    auth: { type: Object, default: () => ({}) },
    member: { type: Number, default: 0 },
    directory: { type: Object, default: () => ({}) },
    clinics: { type: Object, default: () => ({}) },
})

//--------------- Constants and Computed -----------------------------------
const selectedState = ref(props.directory?.state ?? '');
const selectedCity = ref(props.directory?.city ?? '');
const isActive = ref(props.directory?.deleted_at == null)
const isSubmitting = ref(false)

const cities = computed(() => {
    return selectedState.value ? states[selectedState.value] : []
})

const user = computed(() => usePage().props.auth.user)

const initials = computed(() => {
    if (!user.value?.name) return 'DR'
    return user.value.name
        .split(' ')
        .slice(0, 2)
        .map(n => n[0])
        .join('')
        .toUpperCase()
})

//--------------- Form -----------------------------------

const formData = useForm({
    id: props.directory?.id ?? null,
    member_id : props.directory?.member_id ?? null,
    name: props.directory?.name ?? '',
    specialty: props.directory?.specialty ?? '',
    state: '',
    city: '',

    clinics: props.clinics && props.clinics.length > 0 
        ? props.clinics.map(c => ({
            id: c.id ?? null,
            hospital_name: c.hospital_name ?? '',
            address: c.address ?? '',
            phone: c.phone ?? '',
            schedule: c.schedule ?? ''
        }))
        : [{ id: null, hospital_name: '', address: '', phone: '', schedule: '' }],
});


const addClinic = () => {
    formData.clinics.push({hospital_name: '', address: '', phone: '', schedule: ''})
}

const removeClinic = (index) => {
    formData.clinics.splice(index, 1)
}

//--------------- Submit events -----------------------------------
const uploadAvatar = (event) => {
    const file = event.target.files?.[0]
    if (!file) return

    const formData = new FormData()
    formData.append('profile', file)

    router.post(route('directory.profile', props.directory.id), formData, {
        forceFormData: true,
        preserveScroll: true,
    })
}

const handleSubmit = () => {
    formData.state = selectedState.value;
    formData.city = selectedCity.value;

    formData.post(route('directory.save'), {
        preserveScroll: true,
        onStart: () => isSubmitting.value = true,
        onFinish: () => isSubmitting.value = false,
    });
}

//--------------- Watchers -----------------------------------
watch(selectedCity, (value) => {
    formData.city = value
})

watch(selectedState, (value, old) => {
    if (old !== '' && old !== formData.state) {
        selectedCity.value = ''
    }
    formData.state = value
})
</script>

<template>
    <Head title="Directorio Medico" />
    <div class="min-h-screeen">
        <div
            class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800">
            <div class="flex flex-col lg:flex-row">

                <div class="flex flex-1 flex-col justify-center p-8 lg:p-10">
                    <p class="text-sm font-medium text-brand-600">Configuración del perfil</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-gray-800 dark:text-white/90 sm:text-3xl">
                        Directorio Médico
                    </h1>
                    <p class="mt-3 max-w-md text-sm leading-relaxed text-gray-500">
                        Gestiona los datos de tu directorio. Puedes actualizar su información y subir nuevo archivos en cualquier momento.
                    </p>
                    <div class="mt-5">
                        <span v-if="isActive"
                            class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700 dark:bg-green-500/10 dark:text-green-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                            Directorio activo
                        </span>
                        <span v-else
                            class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1 text-xs font-medium text-red-700 dark:bg-red-500/10 dark:text-red-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                            Directorio inactivo
                        </span>
                    </div>
                </div>

                <!-- icono -->
                <div class="relative h-40 w-full lg:h-auto lg:w-64 xl:w-72 overflow-hidden">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-brand-600 via-brand-500 to-brand-400 opacity-10 dark:opacity-5" />
                    <div class="absolute inset-0 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" stroke="currentColor"
                            viewBox="0 0 20 20" stroke-width=".1" stroke-linecap="round" stroke-linejoin="round"
                            class="h-50 text-brand-200 dark:text-brand-800">
                            <g fill="none">
                                <path
                                    d="M5 3a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4.22l.212-.845c.013-.052.027-.104.043-.155H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v4.232c.32-.137.659-.213 1-.229V5a2 2 0 0 0-2-2H5zm4.5 11h1.443l1-1H9.5a.5.5 0 0 0 0 1zm-2-6.75a.75.75 0 1 1-1.5 0a.75.75 0 0 1 1.5 0zM6.75 11a.75.75 0 1 0 0-1.5a.75.75 0 0 0 0 1.5zm0 3a.75.75 0 1 0 0-1.5a.75.75 0 0 0 0 1.5zM9.5 7a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1h-4zm0 3a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1h-4zm1.48 5.377l4.83-4.83a1.87 1.87 0 1 1 2.644 2.646l-4.83 4.829a2.197 2.197 0 0 1-1.02.578l-1.498.374a.89.89 0 0 1-1.079-1.078l.375-1.498c.096-.386.296-.74.578-1.02z"
                                    fill="currentColor"></path>
                            </g>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6">

            <!-- DATOS-->
            <div
                class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800">

                <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 px-8 py-5">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Información Personal
                        </h2>
                        <p class="text-xs text-gray-500">Datos personales, especialidad</p>
                    </div>
                </div>

                <div class="grid sm:grid-cols-1 lg:grid-cols-4 gap-4 ">
                    <div class="mt-6 flex flex-col items-center justify-around gap-4 pl-8 pb-8">
                        <!-- Avatar clickeable -->
                        <label class="relative cursor-pointer group">
                            <div
                                class="flex h-42 w-42 shrink-0 items-center justify-center rounded-xl bg-brand-200 text-white text-lg font-bold shadow-sm overflow-hidden">
                                <img v-if="directory.profile_url" :src="directory.profile_url" class="h-full w-full object-cover"
                                    alt="Avatar" />
                                <span v-else>{{ initials }}</span>
                            </div>
                            <!-- Overlay hover -->
                            <div
                                class="absolute inset-0 rounded-xl bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="17 8 12 3 7 8" />
                                    <line x1="12" y1="3" x2="12" y2="15" />
                                </svg>
                            </div>
                            <input type="file" class="hidden" accept="image/jpeg,image/png,image/webp"
                                @change="uploadAvatar" />
                        </label>

                        <div>
                            <p class="text-xs text-slate-400 mt-0.5">Haz clic para agregar una foto de perfil</p>
                        </div>
                    </div>
                    <div class="col-span-3 col-start-2 p-8 space-y-6 ">
                        <!-- Nombre -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nombre</label>
                            <input type="text" v-model="formData.name" maxlength="190"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            <span v-if="errors.name" class="text-red-500 text-xs flex justify-end mt-1">{{ errors.name }}</span>
                        </div>

                        <!-- Especialidad -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Especialidad</label>
                            <input type="text" v-model="formData.specialty" maxlength="190"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            <span v-if="errors.specialty" class="text-red-500 text-xs flex justify-end mt-1">{{ errors.specialty }}</span>
                        </div>
                        
                        <!-- Ciudad y Estado -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Estado</label>
                                <select name="state" id="state" v-model="selectedState" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Seleccionar estado</option>
                                    <option v-for="state in Object.keys(states)" :key="state" :value="state">{{ state }}</option>
                                </select>
                                <span v-if="errors?.state" class="grow text-red-500 text-xs flex justify-end">{{ errors?.state }}</span>
                            </div>
                            <div>
                                <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Ciudad</label>
                                <select name="event_id" id="event_id" v-model="selectedCity" :disabled="!selectedState"
                                    :class="!selectedState ? 'cursor-not-allowed' : ''"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Seleccionar ciudad</option>
                                    <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
                                </select>
                                <span v-if="errors?.city" class="grow text-red-500 text-xs flex justify-end">{{ errors?.city }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-6">
            <div
                class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800">

                <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 px-8 py-5">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                            viewBox="-3 -2 20 20">
                            <g fill="none">
                                <path
                                    d="M3.25 2a.25.25 0 0 0-.25.25L3 10.75c0 .137.112.25.25.25h1.75v1h-1.75c-.69 0-1.25-.56-1.25-1.251l.002-8.5c0-.69.56-1.25 1.25-1.25h4.503c.69 0 1.25.56 1.25 1.252l-.002 1.998c0 .138.111.25.25.25h.497A1.25 1.25 0 0 1 11 5.698a2.003 2.003 0 0 0-1.52-.199h-.227C8.56 5.5 8 4.94 8.002 4.248l.002-1.999a.25.25 0 0 0-.25-.25H3.25zm7.43 4.699a1 1 0 0 0-1.361 0l-2.84 2.637a1.5 1.5 0 0 0-.48 1.1v3.563a1 1 0 0 0 1 1h1.5a1 1 0 0 0 1-1v-1h1v1a1 1 0 0 0 1 1h1.5a1 1 0 0 0 1-1v-3.564a1.5 1.5 0 0 0-.48-1.099L10.68 6.7zm-3.521 3.37l2.84-2.637l2.84 2.637a.5.5 0 0 1 .16.366V14h-1.5v-1a1 1 0 0 0-1-1h-1a1 1 0 0 0-1 1v1h-1.5v-3.564a.5.5 0 0 1 .16-.366zm-2.16-6.57a.5.5 0 1 1-1 0a.5.5 0 0 1 1 0zm-.5 2.5a.5.5 0 1 0 0-1a.5.5 0 0 0 0 1zm.5 1.5a.5.5 0 1 1-1 0a.5.5 0 0 1 1 0zm1.5-3.5a.5.5 0 1 0 0-1a.5.5 0 0 0 0 1zm.5 1.5a.5.5 0 1 1-1 0a.5.5 0 0 1 1 0zm-.5 2.5a.5.5 0 1 0 0-1a.5.5 0 0 0 0 1z"
                                    fill="currentColor"></path>
                            </g>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Consultorios
                        </h2>
                        <p class="text-xs text-gray-500">Consultorio, direccion, horarios</p>
                    </div>
                </div>

                <div class="px-8 py-2">
                    <div v-for="(clinic, index) in formData.clinics" class="space-y-6 mt-4">
                        <div class="grid grid-cols-3 gap-3">
                            <!-- Hospital -->
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Hospital</label>
                                <input type="text" v-model="clinic.hospital_name" maxlength="190"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                <span v-if="errors[`clinics.${index}.hospital_name`]" class="text-red-500 text-xs flex justify-end mt-1">{{ errors[`clinics.${index}.hospital_name`] }}</span>
                            </div>
                            <!-- Direccion -->
                            <div class="col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Dirección</label>
                                <input type="text" v-model="clinic.address" maxlength="190"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                <span v-if="errors[`clinics.${index}.address`]" class="text-red-500 text-xs flex justify-end mt-1">{{ errors[`clinics.${index}.address`] }}</span>
                            </div>
                            <!-- Telefono -->
                            <div class="col-start-1">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Teléfono de contacto</label>
                                <input type="text" v-model="clinic.phone" maxlength="12"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                <span v-if="errors[`clinics.${index}.phone`]" class="text-red-500 text-xs flex justify-end mt-1">{{ errors[`clinics.${index}.phone`] }}</span>
                            </div>
                            <!-- Horario -->
                            <div class="col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Horario</label>
                                <input type="text" v-model="clinic.schedule" maxlength="190"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                <span v-if="errors[`clinics.${index}.schedule`]" class="text-red-500 text-xs flex justify-end mt-1">{{ errors[`clinics.${index}.schedule`] }}</span>
                            </div>
                            <div class="col-span-3 flex justify-end">
                                <button type="button" class="top-3 text-red-400 hover:text-red-600"
                                v-if="formData.clinics.length > 1"
                                @click="removeClinic(index)" >
                                
                                <span class="">
                                    <svg class="h-6"
                                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 24 24"><path d="M16 9v10H8V9h8m-1.5-6h-5l-1 1H5v2h14V4h-3.5l-1-1zM18 7H6v12c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7z" fill="currentColor"></path></svg>
                                </span>
                            </button>
                            </div>
                        </div>
                        
                        
                        <hr class="bg-blue-500">
                    </div>
                    
                    <button @click="addClinic" type="button"
                        class="text-sm mt-3 rounded-xl p-3 font-semibold text-indigo-400 hover:text-brand-600 flex items-center gap-1">
                        + Agregar cosultorio
                    </button>
                </div>
            </div>
        </div>

        <div
            class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800">
            <div class="flex items-center justify-between gap-3 px-8 py-5">
                <p class="text-xs text-gray-400 dark:text-gray-500">
                    {{ `Última actualización: ${directory?.updated_at ? new
                            Date(directory.updated_at).toLocaleDateString('es-MX', {
                                day: 'numeric', month: 'long', year:
                                    'numeric'
                            }) : '—'}` }}
                            </p>
                <button @click="handleSubmit" :disabled="isSubmitting"
                    class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <span v-if="isSubmitting">Guardando...</span>
                    <span v-else>Guardar cambios</span>
                </button>
            </div>
        </div>
    </div>
    
</template>