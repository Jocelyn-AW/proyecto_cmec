<script setup>
import { watch, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    user: {
        type: Object,
        default: null
    }
})

const emit = defineEmits(['close', 'updated', 'warning', 'error'])

// Uso de useForm para gestión automática de errores y estados
const form = useForm({
    id: null,
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: ''
})

// Error local de coincidencia de contraseñas
const passwordMatchError = computed(() => {
    if (!form.password && !form.password_confirmation) return null
    if (form.password !== form.password_confirmation) {
        return 'Las contraseñas no coinciden'
    }
    return null
})

watch(() => props.user, (user) => {
    if (user) {
        form.clearErrors()
        form.id = user.id
        form.name = user.name
        form.email = user.email
        form.password = ''
        form.password_confirmation = ''
        form.role = user.role
    }
}, { immediate: true })

const close = () => {
    form.reset()
    form.clearErrors()
    emit('close')
}

const submit = () => {
    if (!props.user) return

    if (passwordMatchError.value) {
        emit('warning', passwordMatchError.value)
        return
    }

    // Usamos put y enviamos el objeto form directamente
    form.put(route('users.edit', props.user.id), {
        preserveScroll: true,
        onSuccess: () => {
            close()
            emit('updated')
        },
        onError: (errors) => {
            // Los errores ya se cargan automáticamente en form.errors
            emit('error', 'Error al actualizar el usuario. Revisa los campos.')
        }
    })
}
</script>

<template>
    <Modal :show="show" @close="close" maxWidth="lg">
        <template #title>Editar usuario</template>

        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre del usuario <span class="text-red-500">*</span>
                </label>
                <input v-model="form.name" type="text" placeholder="Nombre completo"
                    class="block w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-1"
                    :class="form.errors.name ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'">
                <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Correo electrónico <span class="text-red-500">*</span>
                </label>
                <input v-model="form.email" type="email" placeholder="correo@ejemplo.com"
                    class="block w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-1"
                    :class="form.errors.email ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'">
                <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Rol <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select v-model="form.role"
                        class="block w-full rounded-lg border px-3 py-2 pr-8 text-sm appearance-none focus:outline-none focus:ring-1 dark:bg-gray-900"
                        :class="form.errors.role ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'">
                        <option value="administrador">Administrador</option>
                        <option value="miembro">Miembro</option>
                    </select>
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                        <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke-width="1.2"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </div>
                <p v-if="form.errors.role" class="mt-1 text-xs text-red-500">{{ form.errors.role }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nueva contraseña <span class="text-gray-400 text-xs">(opcional)</span>
                </label>
                <input v-model="form.password" type="password" placeholder="Dejar vacío para no cambiar"
                    class="block w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-1"
                    :class="(form.errors.password || passwordMatchError) ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'">
                <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Confirmar contraseña
                </label>
                <input v-model="form.password_confirmation" type="password" placeholder="••••••••"
                    class="block w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-1"
                    :class="passwordMatchError ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'">
                <p v-if="passwordMatchError" class="mt-1 text-xs text-red-500">
                    {{ passwordMatchError }}
                </p>
            </div>
        </div>

        <template #footer>
            <button @click="close" :disabled="form.processing"
                class="rounded-md border border-transparent py-2 px-4 text-sm text-slate-600 transition-all hover:bg-slate-100 disabled:opacity-50">
                Cancelar
            </button>
            <button @click="submit" :disabled="form.processing || !!passwordMatchError"
                class="rounded-md bg-blue-600 py-2 px-4 text-sm text-white transition-all shadow-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                {{ form.processing ? 'Actualizando...' : 'Actualizar usuario' }}
            </button>
        </template>
    </Modal>
</template>