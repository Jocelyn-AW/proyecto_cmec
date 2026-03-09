<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <form @submit.prevent="updatePassword" class="space-y-5">

            <!-- CONTRASEÑA ACTUAL -->
            <div>
                <InputLabel for="current_password" value="Contraseña actual"
                    class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500" />
                <TextInput id="current_password" ref="currentPasswordInput" v-model="form.current_password"
                    type="password"
                    class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 shadow-none transition focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100"
                    autocomplete="current-password" placeholder="••••••••" />
                <InputError :message="form.errors.current_password" class="mt-1.5" />
            </div>

            <!-- NUEVA CONTRASEÑA -->
            <div>
                <InputLabel for="password" value="Nueva contraseña"
                    class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500" />
                <TextInput id="password" ref="passwordInput" v-model="form.password" type="password"
                    class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 shadow-none transition focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100"
                    autocomplete="new-password" placeholder="••••••••" />
                <InputError :message="form.errors.password" class="mt-1.5" />
            </div>

            <!-- CONFIRMAR CONTRASEÑA -->
            <div>
                <InputLabel for="password_confirmation" value="Confirmar nueva contraseña"
                    class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500" />
                <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password"
                    class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 shadow-none transition focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100"
                    autocomplete="new-password" placeholder="••••••••" />
                <InputError :message="form.errors.password_confirmation" class="mt-1.5" />
            </div>

            <!-- ADVERTENCIA -->
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="flex items-start gap-2 text-xs text-slate-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand-400"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 16v-4" />
                        <path d="M12 8h.01" />
                    </svg>
                    Usa al menos 8 caracteres combinando letras, números y símbolos para mayor seguridad.
                </p>
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
                    Actualizar contraseña
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
                        Contraseña actualizada
                    </span>
                </Transition>
            </div>
        </form>
    </section>
</template>