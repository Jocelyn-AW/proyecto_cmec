<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3' // Cambiamos router por useForm
import Modal from '@/Components/Modal.vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['close', 'created', 'warning', 'error', 'info'])

// Inicializamos el formulario con useForm
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
})

// Error local de coincidencia (frontend)
const passwordMismatch = computed(() => {
    if (!form.password && !form.password_confirmation) return null
    return form.password !== form.password_confirmation ? 'Las contraseñas no coinciden' : null
})

const close = () => {
    form.reset()
    form.clearErrors()
    emit('close')
}

const generatePassword = () => {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%&*'
    let password = ''
    for (let i = 0; i < 12; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length))
    }
    form.password = password
    form.password_confirmation = password
}

const submit = () => {
    if (passwordMismatch.value) {
        emit('warning', passwordMismatch.value)
        return
    }

    form.post('/users', {
        preserveScroll: true,
        onSuccess: () => {
            close()
            emit('created')
        },
        onError: (errors) => {
            // Si hay un error general que no sea de campos (como el catch del controller)
            if (errors.message) {
                emit('error', errors.message)
            } else {
                emit('error', 'Revisa los errores en el formulario')
            }
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
                    class="block w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-1"
                    :class="form.errors.name ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-green-500 focus:ring-green-500'">
                <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Correo electrónico <span class="text-red-500">*</span>
                </label>
                <input v-model="form.email" type="email" placeholder="correo@ejemplo.com"
                    class="block w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-1"
                    :class="form.errors.email ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-green-500 focus:ring-green-500'">
                <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-sm font-medium text-gray-700">
                        Contraseña <span class="text-red-500">*</span>
                    </label>
                    <button type="button" @click="generatePassword"
                        class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                        Generar contraseña
                    </button>
                </div>
                <input v-model="form.password" type="text" placeholder="••••••••"
                    class="block w-full rounded-lg border px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1"
                    :class="(form.errors.password || passwordMismatch) ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-green-500 focus:ring-green-500'">
                <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Confirmar contraseña <span class="text-red-500">*</span>
                </label>
                <input v-model="form.password_confirmation" type="text" placeholder="••••••••"
                    class="block w-full rounded-lg border px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1"
                    :class="passwordMismatch ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-green-500 focus:ring-green-500'">
                <p v-if="passwordMismatch" class="mt-1 text-xs text-red-500">{{ passwordMismatch }}</p>
            </div>
        </div>

        <template #footer>
            <button @click="close" :disabled="form.processing"
                class="rounded-md border border-transparent py-2 px-4 text-sm text-slate-600 hover:bg-slate-100 disabled:opacity-50">
                Cancelar
            </button>
            <button @click="submit" :disabled="form.processing || !!passwordMismatch"
                class="rounded-md bg-green-600 py-2 px-4 text-sm text-white shadow-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                {{ form.processing ? 'Creando...' : 'Crear usuario' }}
            </button>
        </template>
    </Modal>
</template>