<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';

defineOptions({
    layout: AuthenticatedLayout
})

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
    avatarUrl: { type: String, default: '' },
});

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

const uploadAvatar = (event) => {
    const file = event.target.files?.[0]
    if (!file) return

    const formData = new FormData()
    formData.append('avatar', file)

    router.post(route('profile.avatar'), formData, {
        forceFormData: true,
        preserveScroll: true,
    })
}
</script>

<template>

    <Head title="Mi Perfil" />

    <div class="min-h-screen">

        <!-- TITLE OF PROFILE -->
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
            <div class="flex flex-col lg:flex-row">

                <!-- INFO -->
                <div class="flex flex-1 flex-col justify-center p-8 lg:p-10">
                    <p class="text-sm font-medium text-brand-600">Configuración de cuenta</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-800 sm:text-3xl">
                        Mi Perfil
                    </h1>
                    <p class="mt-3 max-w-md text-sm leading-relaxed text-slate-500">
                        Actualiza tu información personal, cambia tu contraseña o gestiona
                        la configuración de seguridad de tu cuenta.
                    </p>

                    <!-- AVATAR Y DATOS -->
                    <div class="mt-6 flex items-center gap-4">
                        <!-- Avatar clickeable -->
                        <label class="relative cursor-pointer group">
                            <div
                                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-brand-600 text-white text-lg font-bold shadow-sm overflow-hidden">
                                <img v-if="avatarUrl" :src="avatarUrl" class="h-full w-full object-cover"
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
                            <p class="text-base font-semibold text-slate-800">{{ user?.name ?? 'Doctor' }}</p>
                            <p class="text-sm text-slate-500">{{ user?.email ?? '' }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">Haz clic en la foto para cambiarla</p>
                        </div>
                    </div>
                </div>

                <!-- IMAGEN (PROX?) -->
                <div class="relative h-48 w-full lg:h-auto lg:w-72 xl:w-80 overflow-hidden">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-brand-600 via-brand-500 to-brand-400 opacity-10" />
                    <!-- foto -->
                    <img v-if="avatarUrl" :src="avatarUrl" alt="Avatar"
                        class="absolute inset-0 h-full w-full object-cover object-center" />
                    <!-- no foto -->
                    <div v-else class="absolute inset-0 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="0.75" stroke-linecap="round" stroke-linejoin="round"
                            class="h-32 w-32 text-brand-200">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </div>
                    <!-- suavizar borde izquierdo de la foto -->
                    <!-- <div v-if="avatarUrl"
                        class="absolute inset-0 bg-gradient-to-r from-white via-transparent to-transparent lg:block hidden" /> -->
                </div>
            </div>
        </div>

        <!-- CONFIG -->
        <div class="mt-8 space-y-6">

            <!-- CORREO + CONTRASEÑA -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="flex items-center gap-3 border-b border-slate-100 px-8 py-5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Información personal</h2>
                        <p class="text-xs text-slate-500">Nombre y correo electrónico de tu cuenta</p>
                    </div>
                </div>
                <div class="p-8">
                    <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status"
                        class="max-w-xl" />
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="flex items-center gap-3 border-b border-slate-100 px-8 py-5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Contraseña</h2>
                        <p class="text-xs text-slate-500">Actualiza tu contraseña de acceso</p>
                    </div>
                </div>
                <div class="p-8">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>
            </div>

            <!-- ELIMINAR CUENTA -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="flex items-center gap-3 border-b border-slate-100 px-8 py-5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 text-red-500">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                            <path d="M3 6h18" />
                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Zona de peligro</h2>
                        <p class="text-xs text-slate-500">Elimina permanentemente tu cuenta</p>
                    </div>
                </div>
                <div class="p-8">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>

        </div>
    </div>
</template>