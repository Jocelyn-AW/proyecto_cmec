<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['close', 'created', 'warning', 'error', 'info'])

const isSubmitting = ref(false)

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
})

// error local de contraseña
const passwordError = computed(() => {
    if (!form.value.password && !form.value.password_confirmation) return null
    if (form.value.password !== form.value.password_confirmation) {
        return 'Las contraseñas no coinciden'
    }
    return null
})

const resetForm = () => {
    form.value = { name: '', email: '', password: '', password_confirmation: '' }
}

const close = () => {
    resetForm()
    emit('close')
}

const generatePassword = () => {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%&*'
    let password = ''
    for (let i = 0; i < 12; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length))
    }
    form.value.password = password
    form.value.password_confirmation = password
}

const submit = () => {
    if (!form.value.name || !form.value.email || !form.value.password) {
        emit('info', 'Por favor completa todos los campos requeridos')
        return
    }

    if (passwordError.value) {
        emit('warning', passwordError.value)
        return
    }

    isSubmitting.value = true

    router.post('/users', form.value, {
        preserveScroll: true,
        onSuccess: () => {
            close()
            emit('created')
        },
        onError: () => {
            emit('error', 'Error al crear el usuario')
        },
        onFinish: () => {
            isSubmitting.value = false
        }
    })
}
</script>

<template>
    <Modal :show="show" @close="close" maxWidth="lg">
        <template #title>Crear usuario</template>

        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre del usuario <span class="text-red-500">*</span>
                </label>
                <input v-model="form.name" type="text" placeholder="Nombre completo"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Correo electrónico <span class="text-red-500">*</span>
                </label>
                <input v-model="form.email" type="email" placeholder="correo@ejemplo.com"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-sm font-medium text-gray-700">
                        Contraseña <span class="text-red-500">*</span>
                    </label>
                    <button type="button" @click="generatePassword"
                        class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 font-medium transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-3.5 h-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        Generar contraseña
                    </button>
                </div>
                <input v-model="form.password" type="text" placeholder="••••••••"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500 font-mono"
                    :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': passwordError }">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Confirmar contraseña <span class="text-red-500">*</span>
                </label>
                <input v-model="form.password_confirmation" type="text" placeholder="••••••••"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500 font-mono"
                    :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': passwordError }">
                <p v-if="passwordError" class="mt-1 text-xs text-red-500">
                    {{ passwordError }}
                </p>
            </div>
        </div>

        <template #footer>
            <button @click="close" :disabled="isSubmitting"
                class="rounded-md border border-transparent py-2 px-4 text-sm text-slate-600 transition-all hover:bg-slate-100 disabled:opacity-50">
                Cancelar
            </button>
            <button @click="submit" :disabled="isSubmitting || !!passwordError"
                class="rounded-md bg-green-600 py-2 px-4 text-sm text-white transition-all shadow-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                {{ isSubmitting ? 'Creando...' : 'Crear usuario' }}
            </button>
        </template>
    </Modal>
</template>