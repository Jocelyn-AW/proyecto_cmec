<script setup>
import { ref, watch, computed } from 'vue'
import { router } from '@inertiajs/vue3'
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

const isSubmitting = ref(false)

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: ''
})

// error local de contraseña
const passwordError = computed(() => {
    if (!form.value.password && !form.value.password_confirmation) return null
    if (form.value.password !== form.value.password_confirmation) {
        return 'Las contraseñas no coinciden'
    }
    return null
})

watch(() => props.user, (user) => {
    if (user) {
        form.value = {
            name: user.name,
            email: user.email,
            password: '',
            password_confirmation: '',
            role: user.role
        }
    }
}, { immediate: true })

const resetForm = () => {
    form.value = { name: '', email: '', password: '', password_confirmation: '', role: '' }
}

const close = () => {
    resetForm()
    emit('close')
}

const submit = () => {
    if (!props.user) return

    // contra coincidencias
    if (passwordError.value) {
        emit('warning', passwordError.value)
        return
    }

    isSubmitting.value = true

    router.put(`/users/${props.user.id}`, form.value, {
        preserveScroll: true,
        onSuccess: () => {
            close()
            emit('updated')
        },
        onError: () => {
            emit('error', 'Error al actualizar el usuario')
        },
        onFinish: () => {
            isSubmitting.value = false
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
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Correo electrónico <span class="text-red-500">*</span>
                </label>
                <input v-model="form.email" type="email" placeholder="correo@ejemplo.com"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Rol <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select v-model="form.role"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 pr-8 text-sm appearance-none focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white/90">
                        <option value="administrador">Administrador</option>
                        <option value="miembro">Miembro</option>
                    </select>
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                        <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="" stroke-width="1.2"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nueva contraseña <span class="text-gray-400 text-xs">(opcional)</span>
                </label>
                <input v-model="form.password" type="password" placeholder="Dejar vacío para no cambiar"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': passwordError }">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Confirmar contraseña
                </label>
                <input v-model="form.password_confirmation" type="password" placeholder="••••••••"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
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
                class="rounded-md bg-blue-600 py-2 px-4 text-sm text-white transition-all shadow-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                {{ isSubmitting ? 'Actualizando...' : 'Actualizar usuario' }}
            </button>
        </template>
    </Modal>
</template>