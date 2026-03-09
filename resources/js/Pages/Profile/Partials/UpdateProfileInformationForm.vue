<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <form @submit.prevent="form.patch(route('profile.update'))" class="space-y-5">

            <!-- NOMBRE -->
            <div>
                <InputLabel for="name" value="Nombre completo"
                    class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500" />
                <TextInput id="name" type="text"
                    class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 shadow-none transition focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100"
                    v-model="form.name" required autofocus autocomplete="name" placeholder="Dr. Juan Pérez" />
                <InputError class="mt-1.5" :message="form.errors.name" />
            </div>

            <!-- EMAIL -->
            <div>
                <InputLabel for="email" value="Correo electrónico"
                    class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500" />
                <TextInput id="email" type="email"
                    class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 shadow-none transition focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100"
                    v-model="form.email" required autocomplete="username" placeholder="correo@ejemplo.com" />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <!-- VERIFICAR EMAIL -->
            <div v-if="mustVerifyEmail && user.email_verified_at === null"
                class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                <p class="text-sm text-amber-800">
                    Tu correo electrónico no ha sido verificado.
                    <Link :href="route('verification.send')" method="post" as="button"
                        class="ml-1 font-medium text-brand-600 underline hover:text-brand-700 focus:outline-none">
                        Reenviar verificación
                    </Link>
                </p>
                <div v-show="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-700">
                    Se ha enviado un nuevo enlace de verificación a tu correo.
                </div>
            </div>

            <!-- ACCIONES -->
            <div class="flex items-center gap-4 pt-2">
                <button type="submit" :disabled="form.processing"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 disabled:opacity-60">
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    Guardar cambios
                </button>

                <Transition enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0 translate-y-1" leave-active-class="transition ease-in-out duration-200"
                    leave-to-class="opacity-0">
                    <span v-if="form.recentlySuccessful"
                        class="flex items-center gap-1.5 text-sm font-medium text-green-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6 9 17l-5-5" />
                        </svg>
                        Guardado correctamente
                    </span>
                </Transition>
            </div>
        </form>
    </section>
</template>