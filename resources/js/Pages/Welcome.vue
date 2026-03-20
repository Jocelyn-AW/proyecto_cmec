<script setup>
import { Head, Link } from '@inertiajs/vue3'

</script>

<template>
    <Head title="Bienvenido" />
    <main class="relative flex min-h-screen flex-col items-center justify-center px-4">
        <img src="/images/placeholders/medical-bg.jpg" alt="" class="absolute inset-0 h-full w-full object-cover" />

        <div class="absolute inset-0 bg-indigo-900/40" />

        <div class="relative z-10 w-full max-w-md text-center">
            <h1 class="text-4xl font-bold tracking-tight text-white">
                Bienvenido
            </h1>
            <p class="mt-3 text-base text-slate-200">
                Inicia sesion para continuar.
            </p>

            <div class="mt-10 flex flex-col gap-3">
                <Link :href="route('login')"
                    class="inline-flex h-11 items-center justify-center rounded-lg bg-indigo-800 px-6 text-sm font-medium text-white transition-colors hover:bg-indigo-700">
                    Iniciar sesion
                </Link>
            </div>
        </div>
    </main>
</template>