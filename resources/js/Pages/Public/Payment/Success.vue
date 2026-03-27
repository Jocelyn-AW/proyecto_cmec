<script setup>
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    event:      { type: Object, default: () => ({}) },
    event_type: { type: String, default: '' },
    event_name: { type: String, default: '' },
})
</script>

<template>
    <Head title="Pago exitoso" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800 p-8 text-center">

                <!-- Ícono de éxito -->
                <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-green-50 dark:bg-green-500/10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-500" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                    ¡Pago exitoso!
                </h1>

                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                    Tu registro ha sido confirmado. En breve recibirás un correo con los detalles de tu inscripción.
                </p>

                <div v-if="conference?.name" class="mt-6 rounded-xl bg-gray-50 dark:bg-gray-800/50 px-6 py-4 text-left">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Evento registrado</p>
                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">{{ event_name }}</p>
                </div>

                <div class="mt-8 flex flex-col gap-3">
                    <a :href="route('public.event.register', { eventType: event_type, eventId: event.id })"
                        class="w-full rounded-lg border border-gray-200 dark:border-gray-700 px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-center">
                        Volver al evento
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <p class="mt-6 text-center text-xs text-gray-400">
                Pago procesado de forma segura por Stripe
            </p>
        </div>
    </div>
</template>