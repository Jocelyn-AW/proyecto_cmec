<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({
    layout: AuthenticatedLayout
})

const user = computed(() => usePage().props.auth.user)

const hour = new Date().getHours()
const greeting = hour < 12 ? 'Buenos dias' : hour < 18 ? 'Buenas tardes' : 'Buenas noches'

const links = [
    {
        title: 'Mi membresía',
        description: 'Consulta el estado de tu membresía y pagos pendientes.',
        icon: 'clipboard',
        goTo: route('dashboard')
    },
    {
        title: 'Directorio',
        description: 'Edita los datos que aparecen en el directorio medico.',
        icon: 'users',
        goTo: route('dashboard')
    },
    {
        title: 'Noticias',
        description: 'Ultimas noticias y comunicados institucionales.',
        icon: 'newspaper',
        goTo: route('news.index')
    },
    {
        title: 'Eventos',
        description: 'Conferencias, talleres y actividades del colegio.',
        icon: 'calendar',
        goTo: route('courses.index')
    },
    
    {
        title: 'Diplomas',
        description: 'Solicita constancias y certificados de colegiatura.',
        icon: 'award',
        goTo: route('dashboard')
    },
    {
        title: 'Soporte',
        description: 'Contacta con la administracion del colegio.',
        icon: 'message',
        goTo: route('dashboard')
    },
]
</script>

<template>

    <Head title="Dashboard" />

    <div class="min-h-screen">

        <!-- Main -->
        <!-- <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8"> -->
            <!-- Tarjeta de bienvenida -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="flex flex-col lg:flex-row">
                    <!-- Texto -->
                    <div class="flex flex-1 flex-col justify-center p-8 lg:p-10">
                        <p class="text-sm font-medium text-brand-600">{{ greeting }}</p>
                        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-800 sm:text-3xl">
                            Bienvenido, {{ user?.name ?? 'Doctor' }}
                        </h1>
                        <p class="mt-3 max-w-md text-sm leading-relaxed text-slate-500">
                            Accede a los recursos de tu membresía, consulta el directorio de
                            medicos, y mantente informado sobre las ultimas noticias y
                            eventos de nuestro colegio.
                        </p>

                        <div class="mt-6 flex flex-wrap gap-4">
                            <div class="flex flex-col">
                                <span class="text-2xl font-bold text-slate-800">1,248</span>
                                <span class="text-xs text-slate-500">Miembros activos</span>
                            </div>
                            <div class="h-12 w-px bg-slate-200" />
                            <div class="flex flex-col">
                                <span class="text-2xl font-bold text-slate-800">3</span>
                                <span class="text-xs text-slate-500">Eventos este mes</span>
                            </div>
                            <div class="h-12 w-px bg-slate-200" />
                            <div class="flex flex-col">
                                <span class="text-2xl font-bold text-slate-800">5</span>
                                <span class="text-xs text-slate-500">Noticias nuevas</span>
                            </div>
                        </div>
                    </div>

                    <!-- Imagen -->
                    <div class="relative h-48 w-full lg:h-auto lg:w-80 xl:w-96">
                        <img src="/images/placeholders/dashboard.avif" alt="Comunidad de medicos colegiados"
                            class="h-full w-full object-cover" />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-transparent lg:bg-gradient-to-r lg:from-white lg:via-transparent lg:to-transparent" />
                    </div>
                </div>
            </div>

            <!-- Accesos rapidos -->
            <section class="mt-8">
                <h2 class="mb-4 text-lg font-semibold text-slate-800">
                    Accesos rapidos
                </h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Link v-for="link in links" :key="link.title" :href="link.goTo"
                        class="group flex gap-4 rounded-xl border border-slate-200 bg-white p-5 transition-colors hover:border-brand-200 hover:bg-brand-50/50">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 transition-colors group-hover:bg-brand-100">
                            <!-- Clipboard -->
                            <svg v-if="link.icon === 'clipboard'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" class="h-6 w-6">
                                <path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                                <rect width="20" height="14" x="2" y="6" rx="2" />
                            </svg>
                            <!-- Users -->
                            <svg v-if="link.icon === 'users'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" class="h-6 w-6">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                            <!-- Calendar -->
                            <svg v-if="link.icon === 'calendar'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" class="h-6 w-6">
                                <path d="M8 2v4" />
                                <path d="M16 2v4" />
                                <rect width="18" height="18" x="3" y="4" rx="2" />
                                <path d="M3 10h18" />
                            </svg>
                            <!-- Newspaper -->
                            <svg v-if="link.icon === 'newspaper'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" class="h-6 w-6">
                                <path
                                    d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2" />
                                <path d="M18 14h-8" />
                                <path d="M15 18h-5" />
                                <path d="M10 6h8v4h-8V6Z" />
                            </svg>
                            <!-- Award -->
                            <svg v-if="link.icon === 'award'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" class="h-6 w-6">
                                <circle cx="12" cy="8" r="6" />
                                <path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11" />
                            </svg>
                            <!-- Message -->
                            <svg v-if="link.icon === 'message'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" class="h-6 w-6">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-semibold text-slate-800">
                                {{ link.title }}
                            </span>
                            <span class="mt-1 text-xs leading-relaxed text-slate-500">
                                {{ link.description }}
                            </span>
                        </div>
                    </Link>
                </div>
            </section>
        <!-- </main> -->
    </div>
</template>
