<script setup>
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { useAlert } from '@/composables/useAlert'
import { computed, onMounted, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Alerta from '@/Components/Alerta.vue';
import SettingsIcon from '@/icons/SettingsIcon.vue';


defineOptions({
    layout: AuthenticatedLayout
})

const props = defineProps({
    flash: { type: Object, default: () => ({}) },
    errors: { type: Object, default: () => ({}) },
    auth: { type: Object, default: () => ({}) },
    stripe: { type: Object, default: () => ({}) },
    settings: { type: Object, default: () => ({}) },
})

const { alertState, warning, hideAlert, success, errorA } = useAlert();
watch(() => props.flash, (value) => {
    if (!value) return

    if (value.success) success(value.success)
    if (value.warning) warning(value.warning)
    if (value.error) errorA(value.error)
}, {immediate: true, deep: true})


const isSubmitting = ref(false);
const stripeKey     = ref(props.settings?.[0] ?? null);
const stripeSecret  = ref(props.settings?.[1] ?? null);
const stripeWebhook = ref(props.settings?.[2] ?? null);

const form = useForm({
    stripe_key:             stripeKey.value?.value ?? null,
    stripe_secret :         stripeSecret.value?.value ?? null,
    stripe_webhook_secret:  stripeWebhook.value?.value ?? null,
})

const formatDate = (value) => {
    if (!value) return '--'

    let date = new Date(value);
    let options = { day: '2-digit', month: '2-digit', year: 'numeric' };
    return new Intl.DateTimeFormat('es-MX', options).format(date);
}

const handleSubmit = () => {
    form.post(route('settings.index'), {
        preserveScroll: true,
        onStart: () => isSubmitting.value = true,
        onFinish: () => isSubmitting.value = false,
    });
}

</script>

<template>
    <Head title="Configuración" />
    <div class="min-h-screeen">
        <div
            class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800">
            <div class="flex flex-col lg:flex-row">

                <div class="flex flex-1 flex-col justify-center p-8 lg:p-10">
                    <p class="text-sm font-medium text-brand-600">Configuración de la aplicacion</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-gray-800 dark:text-white/90 sm:text-3xl">
                        Configuración
                    </h1>
                    <p class="mt-3 max-w-md text-sm leading-relaxed text-gray-500">
                        Gestiona los datos de tu aplicación. Puedes actualizar su información en cualquier momento.
                    </p>
                </div>

                <!-- icono -->
                <div class="relative h-40 w-full lg:h-auto lg:w-64 xl:w-72 overflow-hidden">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-brand-600 via-brand-500 to-brand-400 opacity-10 dark:opacity-5" />
                    <div class="absolute inset-0 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" stroke="currentColor" stroke-width=".1" stroke-linecap="round"
                            class="text-brand-200 h-50"
                            viewBox="0 0 28 28">
                            <g fill="none">
                                <path
                                    d="M14 9.5a4.5 4.5 0 1 0 0 9a4.5 4.5 0 0 0 0-9zM11 14a3 3 0 1 1 6 0a3 3 0 0 1-6 0zm10.71 8.395l-1.728-.759a1.72 1.72 0 0 0-1.542.086c-.467.27-.765.747-.824 1.284l-.208 1.88a.923.923 0 0 1-.703.796a11.67 11.67 0 0 1-5.412 0a.923.923 0 0 1-.702-.796l-.208-1.877a1.701 1.701 0 0 0-.838-1.281a1.694 1.694 0 0 0-1.526-.086l-1.728.759a.92.92 0 0 1-1.043-.215a12.064 12.064 0 0 1-2.707-4.672a.924.924 0 0 1 .334-1.016l1.527-1.128a1.7 1.7 0 0 0 0-2.74l-1.527-1.125a.924.924 0 0 1-.334-1.017A12.059 12.059 0 0 1 5.25 5.821a.92.92 0 0 1 1.043-.214l1.72.757a1.707 1.707 0 0 0 2.371-1.376l.21-1.878a.923.923 0 0 1 .715-.799c.881-.196 1.78-.3 2.704-.311c.902.01 1.8.115 2.68.311a.922.922 0 0 1 .715.8l.209 1.878a1.701 1.701 0 0 0 1.688 1.518c.233 0 .464-.049.68-.144l1.72-.757a.92.92 0 0 1 1.043.214a12.057 12.057 0 0 1 2.708 4.667a.924.924 0 0 1-.333 1.016l-1.525 1.127c-.435.32-.698.829-.698 1.37c0 .54.263 1.049.699 1.37l1.526 1.126c.316.234.45.642.334 1.017a12.065 12.065 0 0 1-2.707 4.667a.92.92 0 0 1-1.043.215zm-5.447-.198a3.162 3.162 0 0 1 1.425-1.773a3.22 3.22 0 0 1 2.896-.161l1.344.59a10.565 10.565 0 0 0 1.97-3.398l-1.189-.877v-.001a3.207 3.207 0 0 1-1.309-2.578c0-1.027.497-1.98 1.307-2.576l.002-.001l1.187-.877a10.56 10.56 0 0 0-1.971-3.397l-1.333.586l-.002.001c-.406.18-.843.272-1.286.272a3.202 3.202 0 0 1-3.178-2.852v-.002l-.163-1.46a11.476 11.476 0 0 0-1.95-.193c-.674.009-1.33.074-1.975.193l-.163 1.461A3.207 3.207 0 0 1 7.41 7.737l-1.336-.588a10.558 10.558 0 0 0-1.971 3.397l1.19.877a3.201 3.201 0 0 1 0 5.155l-1.19.878a10.565 10.565 0 0 0 1.97 3.403l1.345-.59a3.194 3.194 0 0 1 2.878.16a3.2 3.2 0 0 1 1.579 2.411v.005l.162 1.464c1.297.255 2.63.255 3.927 0l.162-1.467c.024-.22.07-.437.138-.645z"
                                    fill="currentColor"></path>
                            </g>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6">

            <!-- Stripe -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800">
                <!-- Encabezado -->
                <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 px-8 py-5">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                            viewBox="0 0 16 16">
                            <g fill="none">
                                <path
                                    d="M10.5 10a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1h-2zM1 5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V5zm13 0a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1h12V5zM2 11a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V7H2v4z"
                                    fill="currentColor"></path>
                            </g>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Llaves de Stripe
                        </h2>
                        <p class="text-xs text-gray-500">Publica, Secreta y Webhooks</p>
                    </div>
                </div>
                <!-- Datos -->
                <div class="px-8 py-2 space-y-6 my-3">
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Llave publica</label>
                        <input type="text" v-model="form.stripe_key" maxlength="190"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        <span v-if="errors.stripe_key" class="text-red-500 text-xs flex justify-end mt-1">{{ errors.stripe_key }}</span>
                        <p class="text-xs text-gray-400 dark:text-gray-500 flex flex-row-reverse">Ultima actualizacion: {{ formatDate(stripeKey.updated_at) }}</p>
                    </div>

                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Llave secreta</label>
                        <input type="text" v-model="form.stripe_secret" maxlength="190"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        <span v-if="errors.stripe_secret" class="text-red-500 text-xs flex justify-end mt-1">{{ errors.stripe_secret }}</span>
                        <p class="text-xs text-gray-400 dark:text-gray-500 flex flex-row-reverse">Ultima actualizacion: {{ formatDate(stripeSecret.updated_at) }}</p>
                    </div>

                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Webhook</label>
                        <input type="text" v-model="form.stripe_webhook_secret" maxlength="190"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        <span v-if="errors.stripe_webhook_secret" class="text-red-500 text-xs flex justify-end mt-1">{{ errors.stripe_webhook_secret }}</span>
                        <p class="text-xs text-gray-400 dark:text-gray-500 flex flex-row-reverse">Ultima actualizacion: {{ formatDate(stripeWebhook.updated_at) }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div
            class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800">
            <div class="flex flex-row-reverse gap-3 px-8 py-5">
                <button @click="handleSubmit" :disabled="isSubmitting"
                    class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <span v-if="isSubmitting">Guardando...</span>
                    <span v-else>Guardar cambios</span>
                </button>
            </div>
        </div>
    </div>

    <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
        :buttonText="alertState.buttonText" :cancelText="alertState.cancelText"
        @confirm="alertState.onConfirm ? alertState.onConfirm() : hideAlert()" @cancel="hideAlert()"
        @close="hideAlert()" />
</template>