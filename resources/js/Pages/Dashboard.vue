<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({
    layout: AuthenticatedLayout
})

const props = defineProps({
    flash:  { type: Object, default: () => ({})},
    auth:   { type: Object, default: () => ({})},
    errors: { type: Object, default: () => ({})},
    data: { type: Object, default: () => ({})},
})

const hour = new Date().getHours()
const greeting = hour < 12 ? 'Buenos dias' : hour < 18 ? 'Buenas tardes' : 'Buenas noches'

const links = computed(() => {
    if (props.auth.user.role == 'administrador') {
        return [
            {
                title: 'Membresía',
                description: 'Consulta el estado de la membresía y los periodos de pago.',
                icon: 'card',
                goTo: route('memberships.index')
            },
            {
                title: 'Directorio Médico',
                description: 'Gestiona los registros que aparecen en el directorio medico.',
                icon: 'directory',
                goTo: route('directory')
            },
            {
                title: 'Noticias',
                description: 'Ultimas noticias y comunicados institucionales.',
                icon: 'newspaper',
                goTo: route('news.index')
            },
            {
                title: 'Congresos',
                description: 'Consulta tus congresos registrados y agrega nueva informacion',
                icon: 'conference',
                goTo: route('courses.index')
            },
            
            {
                title: 'Miembros',
                description: 'Administra los usuarios registrados y gestiona su acceso.',
                icon: 'member',
                goTo: route('members.index')
            },
            {
                title: 'Configuración',
                description: 'Admnistra las claves de tu aplicación ',
                icon: 'config',
                goTo: route('settings.index')
            },
        ]
    } else {
        return [
            {
                title: 'Mi membresía',
                description: 'Consulta el estado de tu membresía y pagos pendientes.',
                icon: 'card',
                goTo: route('profile.edit')
            },
            {
                title: 'Directorio',
                description: 'Edita los datos que aparecen en el directorio medico.',
                icon: 'member',
                goTo: route('directory')
            },
            {
                title: 'Mi historial de eventos',
                description: 'Consulta tu historial de eventos, pagos y diplomas',
                icon: 'history',
                goTo: route('history.index')
            },
        ]
    }
})

</script>

<template>

    <Head title="Dashboard" />

    <div class="min-h-screen">

            <!-- Tarjeta de bienvenida -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="flex flex-col lg:flex-row">
                    <!-- Texto -->
                    <div class="flex flex-1 flex-col justify-center p-8 lg:p-10">
                        <p class="text-sm font-medium text-brand-600">{{ greeting }}</p>
                        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-800 sm:text-3xl">
                            Bienvenido, {{ props.auth?.user?.name ?? 'Doctor' }}
                        </h1>
                        <p v-if="props.auth.user.role == 'administrador'" class="mt-3 max-w-md text-sm leading-relaxed text-slate-500">
                            Administra membresías, accede al directorio de medicos, controla el acceso a tu aplicacion y registra nuevos eventos.
                        </p>
                        <p v-else class="mt-3 max-w-md text-sm leading-relaxed text-slate-500">
                            Accede a información de tu membresía, consulta tu directorio medico, y mantente informado sobre las ultimas noticias y
                            eventos de nuestro colegio.
                        </p>

                        <div v-if="props.auth.user.role == 'administrador'" class="mt-6 flex flex-wrap gap-4">
                            <div class="flex flex-col">
                                <span class="text-2xl font-bold text-slate-800">{{ props.data?.members_count }}</span>
                                <span class="text-xs text-slate-500">Miembros activos</span>
                            </div>
                            <div class="h-12 w-px bg-slate-200" />
                            <div class="flex flex-col">
                                <span class="text-2xl font-bold text-slate-800">{{ props.data.events_count }}</span>
                                <span class="text-xs text-slate-500">Eventos este mes</span>
                            </div>
                            <div class="h-12 w-px bg-slate-200" />
                            <div class="flex flex-col">
                                <span class="text-2xl font-bold text-slate-800">{{ props.data.news_count }}</span>
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
                    Accesos rápidos
                </h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Link v-for="link in links" :key="link.title" :href="link.goTo"
                        class="group flex gap-4 rounded-xl border border-slate-200 bg-white p-5 transition-colors hover:border-brand-200 hover:bg-brand-50/50">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 transition-colors group-hover:bg-brand-100">
                            
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
                            <!-- People -->
                            <svg v-if="link.icon === 'conference'" fill="currentColor" viewBox="0 0 256 256" 
                                xmlns="http://www.w3.org/2000/svg" class="h-7 w-7">
                                <path
                                    d="M28.4 124.8a6 6 0 0 0 8.4-1.2 54 54 0 0 1 86.4 0 6 6 0 0 0 8.4 1.19 5.6 5.6 0 0 0 1.19-1.19h0a54 54 0 0 1 86.4 0 6 6 0 0 0 9.6-7.21 65.74 65.74 0 0 0-29.69-22.26 38 38 0 1 0-46.22 0A65.3 65.3 0 0 0 128 110.7a65.3 65.3 0 0 0-24.89-16.57 38 38 0 1 0-46.22 0A65.7 65.7 0 0 0 27.2 116.4a6 6 0 0 0 1.2 8.4ZM176 38a26 26 0 1 1-26 26 26 26 0 0 1 26-26Zm-96 0a26 26 0 1 1-26 26 26 26 0 0 1 26-26Zm119.11 160.13a38 38 0 1 0-46.22 0A65.3 65.3 0 0 0 128 214.7a65.3 65.3 0 0 0-24.89-16.57 38 38 0 1 0-46.22 0A65.7 65.7 0 0 0 27.2 220.4a6 6 0 1 0 9.6 7.2 54 54 0 0 1 86.4 0 6 6 0 0 0 8.4 1.19 5.6 5.6 0 0 0 1.19-1.19h0a54 54 0 0 1 86.4 0 6 6 0 0 0 9.6-7.21 65.74 65.74 0 0 0-29.68-22.26ZM80 142a26 26 0 1 1-26 26 26 26 0 0 1 26-26Zm96 0a26 26 0 1 1-26 26 26 26 0 0 1 26-26Z">
                                </path>
                            </svg>
                            <!-- List -->
                            <svg v-if="link.icon === 'directory'" fill="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg" class="h-8 w-8">
                                <g>
                                    <path
                                        d="M18.436 20.937H5.562a2.5 2.5 0 0 1-2.5-2.5V5.563a2.5 2.5 0 0 1 2.5-2.5h12.874a2.5 2.5 0 0 1 2.5 2.5v12.874a2.5 2.5 0 0 1-2.5 2.5ZM5.562 4.063a1.5 1.5 0 0 0-1.5 1.5v12.874a1.5 1.5 0 0 0 1.5 1.5h12.874a1.5 1.5 0 0 0 1.5-1.5V5.563a1.5 1.5 0 0 0-1.5-1.5Z">
                                    </path>
                                    <path
                                        d="M6.544 8.283za.52.52 0 0 1-.353-.147.5.5 0 0 1 0-.707.5.5 0 0 1 .353-.146H7.55a.52.52 0 0 1 .353.146.5.5 0 0 1 .147.354.5.5 0 0 1-.5.5ZM6.544 12.5za.52.52 0 0 1-.353-.146.5.5 0 0 1 0-.708.52.52 0 0 1 .353-.146H7.55a.52.52 0 0 1 .353.146.5.5 0 0 1 0 .708.52.52 0 0 1-.353.146ZM6.544 16.72za.52.52 0 0 1-.353-.147.5.5 0 0 1 0-.707.52.52 0 0 1 .353-.146H7.55a.52.52 0 0 1 .353.146.5.5 0 0 1 .147.354.5.5 0 0 1-.5.5ZM10.554 8.281za.5.5 0 0 1 0-1h6.9a.5.5 0 0 1 0 1ZM10.554 12.5za.5.5 0 0 1 0-1h6.9a.5.5 0 0 1 0 1ZM10.554 16.718za.5.5 0 0 1 0-1h6.9a.5.5 0 0 1 0 1Z">
                                    </path>
                                </g>
                            </svg>
                            <!-- User with data -->
                            <svg v-if="link.icon === 'member'" fill="currentColor" viewBox="0 0 24 24" 
                                xmlns="http://www.w3.org/2000/svg" class="h-7 w-7">
                                <path
                                    d="M15 11h7v2h-7zm1 4h6v2h-6zm-2-8h8v2h-8zM4 19h10v-1c0-2.757-2.243-5-5-5H7c-2.757 0-5 2.243-5 5v1zm4-7c1.995 0 3.5-1.505 3.5-3.5S9.995 5 8 5 4.5 6.505 4.5 8.5 6.005 12 8 12z">
                                </path>
                            </svg>
                            <!-- Card -->
                            <svg v-if="link.icon === 'card'" fill="currentColor" viewBox="0 0 16 16"
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-6 h-6">
                                <path
                                    d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1H0zm0 3v5a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7zm3 2h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1a1 1 0 0 1 1-1">
                                </path>
                            </svg>
                            <!-- Settings -->
                            <svg v-if="link.icon === 'config'" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6">
                                <g stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                                    <path
                                        d="m13.6 21.076 5.46-3.152c.584-.337.875-.505 1.087-.74a2 2 0 0 0 .416-.72c.097-.301.097-.637.097-1.307V8.843c0-.67 0-1.006-.098-1.307a2 2 0 0 0-.416-.72c-.21-.234-.5-.402-1.079-.736L13.6 2.924c-.583-.337-.874-.505-1.184-.57a2 2 0 0 0-.832 0c-.31.065-.601.233-1.184.57L4.938 6.077c-.582.336-.873.504-1.084.739a2 2 0 0 0-.416.72c-.098.302-.098.638-.098 1.311v6.305c0 .673 0 1.01.098 1.311a2 2 0 0 0 .416.72c.211.236.503.404 1.085.74l5.46 3.153c.584.337.875.505 1.185.57.274.059.558.059.832 0 .31-.065.602-.233 1.185-.57Z">
                                    </path>
                                    <path d="M9 12a3 3 0 1 0 6 0 3 3 0 0 0-6 0Z"></path>
                                </g>
                            </svg>
                            <!-- History -->
                            <svg v-if="link.icon === 'history'" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.266 16.06a8.92 8.92 0 0 0 3.915 3.978 8.7 8.7 0 0 0 5.471.832 8.8 8.8 0 0 0 4.887-2.64 9.07 9.07 0 0 0 2.388-5.079 9.14 9.14 0 0 0-1.044-5.53 8.9 8.9 0 0 0-4.069-3.815 8.7 8.7 0 0 0-5.5-.608c-1.85.401-3.366 1.313-4.62 2.755-.151.16-.735.806-1.22 1.781M7.5 8l-3.609.72L3 5m9 4v4l3 2">
                                </path>
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
