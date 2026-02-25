<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>

    <Head title="Registro" />

    <div class="flex min-h-screen">
        <div class="relative hidden w-1/2 lg:block">
            <img src="/images/placeholders/login_image.jpg" alt=""
                class="absolute inset-0 h-full w-full object-cover" />
            <div class="absolute inset-0 bg-indigo-900/50" />
            <div class="absolute inset-0 flex flex-col justify-end p-12">
                <blockquote class="max-w-md">
                    <p class="text-lg leading-relaxed text-white/90">
                        &ldquo;Promoviendo la educación y actualización
                        médica en coloproctología a través de cursos, congresos
                        y sesiones en todo México&rdquo;
                    </p>
                    <footer class="mt-4 text-sm text-white/60">
                        - CMEC
                    </footer>
                </blockquote>
            </div>
        </div>

        <!-- Right panel - Form -->
        <div class="flex w-full flex-col lg:w-1/2">
            <!-- Top bar -->
            <div class="flex items-center justify-between p-6 lg:p-8">
                <Link href="/" class="flex items-center gap-2 text-slate-800">
                    <!-- Medical cross icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-indigo-600">
                        <path
                            d="M11 2a2 2 0 0 0-2 2v5H4a2 2 0 0 0-2 2v2c0 1.1.9 2 2 2h5v5c0 1.1.9 2 2 2h2a2 2 0 0 0 2-2v-5h5a2 2 0 0 0 2-2v-2a2 2 0 0 0-2-2h-5V4a2 2 0 0 0-2-2h-2z" />
                    </svg>
                    <span class="text-lg font-semibold">CMEC</span>
                </Link>
                <Link :href="route('login')"
                    class="text-sm font-medium text-slate-500 transition-colors hover:text-slate-800">
                    Iniciar Sesión
                </Link>
            </div>

            <!-- Form centered -->
            <div class="flex flex-1 items-center justify-center px-6 pb-12 lg:px-12">
                <div class="w-full max-w-sm">
                    <div class="mb-8">
                        <h1 class="text-2xl font-bold tracking-tight text-slate-800">
                            Crea una cuenta
                        </h1>
                    </div>

                    <div v-if="status" class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm font-medium text-emerald-600">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="flex flex-col gap-5">
                        <!-- username -->
                        <div class="flex flex-col gap-2">
                            <label for="email" class="text-sm font-medium text-slate-700">
                                Nombre de usuario
                            </label>
                            <input id="email" type="text" v-model="form.name"
                                required autofocus autocomplete="username"
                                class="h-11 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" />
                            <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
                        </div>


                        <!-- Email -->
                        <div class="flex flex-col gap-2">
                            <label for="email" class="text-sm font-medium text-slate-700">
                                Correo electronico
                            </label>
                            <input id="email" type="email" v-model="form.email" placeholder="doctor@ejemplo.com"
                                required autofocus autocomplete="username"
                                class="h-11 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" />
                            <p v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</p>
                        </div>

                        <!-- Password -->
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <label for="password" class="text-sm font-medium text-slate-700">
                                    Contraseña
                                </label>
                            </div>
                            <input id="password" type="password" v-model="form.password" placeholder="********" required
                                autocomplete="new-password"
                                class="h-11 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" />
                            <p v-if="form.errors.password" class="text-xs text-red-500">{{ form.errors.password }}</p>
                        </div>

                        <!-- Password -->
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <label for="password" class="text-sm font-medium text-slate-700">
                                    Confirmar Contraseña
                                </label>
                            </div>
                            <input id="password" type="password" v-model="form.password_confirmation" placeholder="********" required
                                autocomplete="new-password"
                                class="h-11 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" />
                            <p v-if="form.errors.password" class="text-xs text-red-500">{{ form.errors.password_confirmation }}</p>
                        </div>

                        <!-- Submit -->
                        <button type="submit" :disabled="form.processing"
                            class="h-11 w-full rounded-lg bg-indigo-600 text-sm font-medium text-white transition-colors hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ form.processing ? 'Registrando...' : 'Registrarse' }}
                        </button>

                        <p class="text-center text-sm text-slate-500">
                            ¿Ya tienes una cuenta?
                            <Link :href="route('login')"
                                class="font-medium text-indigo-600 transition-colors hover:text-indigo-700">
                                Inicia sesión aqui
                            </Link>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

