<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Drawer from '@/Components/Drawer.vue';

defineOptions({
    layout: AuthenticatedLayout
})

const props = defineProps({
    data: {
        type: Object,
        default: () => ({})
    },
    show: {
        type: Boolean,
        default: false
    },
    flash: {
        type: Object,
        default: () => ({})
    },
    auth: {
        type: Object,
        default: () => ({})
    },
    errors: {
        type: Object,
        default: () => ({})
    }
})
const emit = defineEmits(['close', 'success', 'error'])


</script>
<template>
    <Drawer :show="show" title="Ver detalles" subtitle="Directorio" size="xl" @close="emit('close')">
        <div class="space-y-4">
            <!-- Nombre -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre </label>
                <input type="text" :value="data.name"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>

            <!-- Especialidad -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Especialidad</label>
                <input
                    type="text" :value="data.directory?.specialty"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
            </div>
            <!-- Ciudad y Estado -->
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado </label>
                    <input type="text" :value="data.directory?.state"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad </label>
                    <input type="text" :value="data.directory?.city"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
            </div>

            <hr class="border border-dashed">

            <p class="uppercase text-slate-500 font-medium text-sm mt-3">Consultorios</p>

            <div v-for="(clinic, index) in data.clinics" class="space-y-6 mt-4">
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
                </div>
                
                
                <hr class="border border-dashed">
            </div>
        </div>
        <template #footer>
            <div class="flex justify-end gap-2">
                <button type="button" class="rounded-lg border px-4 py-2 text-sm text-gray-600 hover:bg-gray-50"
                    @click="emit('close')">
                    Cerrar
                </button>
            </div>
        </template>
    </Drawer>
</template>