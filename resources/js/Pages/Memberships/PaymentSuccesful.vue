<script setup>
import { Head } from '@inertiajs/vue3'
</script>

<template>
    <Head title="Membresía activada" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800 p-8 text-center">

                <!-- Ícono -->
                <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-green-50 dark:bg-green-500/10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-500" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                    ¡Membresía activada!
                </h1>

                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                    Tu membresía ha sido activada correctamente. En breve recibirás un correo con los detalles.
                </p>

                <div class="mt-8">
                    <a :href="route('dashboard')"
                        class="w-full rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors inline-block text-center">
                        Ir al dashboard
                    </a>
                </div>
            </div>

            <p class="mt-6 text-center text-xs text-gray-400">
                Pago procesado de forma segura por Stripe
            </p>
        </div>
    </div>
</template>