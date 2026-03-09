<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-5">

        <!-- ADVERTENCIA -->
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-4">
            <div class="flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0 text-red-400" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                    <path d="M12 9v4" />
                    <path d="M12 17h.01" />
                </svg>
                <div>
                    <p class="text-sm font-semibold text-red-800">Acción irreversible</p>
                    <p class="mt-0.5 text-xs leading-relaxed text-red-600">
                        Al eliminar tu cuenta, todos tus datos, membresías e historial serán
                        borrados de forma permanente. Esta acción no se puede deshacer.
                    </p>
                </div>
            </div>
        </div>

        <!-- BTN ELIMINAR -->
        <button @click="confirmUserDeletion"
            class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-white px-5 py-2.5 text-sm font-semibold text-red-600 shadow-sm transition hover:border-red-300 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-300 focus:ring-offset-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                <path d="M3 6h18" />
                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
            </svg>
            Eliminar mi cuenta
        </button>

        <!-- CONFIRMAR -->
        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-7">

                <!-- ENCABEZADO -->
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-500">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                            <path d="M12 9v4" />
                            <path d="M12 17h.01" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">
                            ¿Eliminar cuenta permanentemente?
                        </h2>
                        <p class="mt-1 text-sm leading-relaxed text-slate-500">
                            Esta acción no se puede deshacer. Ingresa tu contraseña para confirmar
                            que deseas eliminar tu cuenta de forma permanente.
                        </p>
                    </div>
                </div>

                <!-- CONTRASEÑA CONFIRMAR -->
                <div class="mt-6">
                    <InputLabel for="password" value="Contraseña"
                        class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500" />
                    <TextInput id="password" ref="passwordInput" v-model="form.password" type="password"
                        class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 transition focus:border-red-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-100"
                        placeholder="Ingresa tu contraseña" @keyup.enter="deleteUser" />
                    <InputError :message="form.errors.password" class="mt-1.5" />
                </div>

                <!-- ACCIONES -->
                <div class="mt-7 flex items-center justify-end gap-3">
                    <button @click="closeModal"
                        class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2">
                        Cancelar
                    </button>

                    <button @click="deleteUser" :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2">
                        <svg v-if="form.processing" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        Sí, eliminar mi cuenta
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>
