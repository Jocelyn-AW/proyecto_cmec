<script setup>
import { ref, watch, computed } from 'vue'
import axios from 'axios'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import Alerta from '@/Components/Alerta.vue'
import { useAlert } from '@/composables/useAlert'

defineOptions({
    layout: AuthenticatedLayout
})

const props = defineProps({
    users: {
        type: Array,
        default: () => []
    }
})

const { alertState, success, errorA, warning, info } = useAlert()
const showCreateModal = ref(false)
const showEditModal = ref(false)
const isSubmitting = ref(false)

// Busqueda y paginacion
const search = ref('')
const perPage = ref(10)
const currentPage = ref(1)

// Form crear
const createForm = ref({ name: '', email: '', password: '', password_confirmation: '' })

// Form editar
const editForm = ref({ id: null, name: '', email: '', password: '', password_confirmation: '', role: '' })

// Filtrado y paginacion computed
const filteredUsers = computed(() => {
    const q = search.value.toLowerCase()
    return props.users.filter(u =>
        u.name.toLowerCase().includes(q) ||
        u.email.toLowerCase().includes(q) ||
        u.role.toLowerCase().includes(q) ||
        (u.is_active ? 'activo' : 'inactivo').includes(q)
    )
})

const totalPages = computed(() => Math.ceil(filteredUsers.value.length / Number(perPage.value)))

const paginatedUsers = computed(() => {
    const start = (currentPage.value - 1) * perPage.value
    return filteredUsers.value.slice(start, start + perPage.value)
})

const showingFrom = computed(() => {
    if (filteredUsers.value.length === 0) return 0
    return (currentPage.value - 1) * perPage.value + 1
})

const showingTo = computed(() =>
    Math.min(currentPage.value * perPage.value, filteredUsers.value.length)
)

const visiblePages = computed(() => {
    const pages = []
    for (let i = 1; i <= totalPages.value; i++) {
        pages.push(i)
    }
    return pages
})

watch([search, perPage], () => { currentPage.value = 1 })

// Cambiar estado activo/inactivo
const statusChange = async (user) => {
    try {
        await axios.patch(`/users/${user.id}/status`)
        router.reload({ only: ['users'] })
        success(`Usuario ${user.is_active ? 'desactivado' : 'activado'} correctamente`)
    } catch (error) {
        errorA('Error al cambiar el estado del usuario')
    }
}

// Eliminar
const deleteUser = (id) => {
    warning('¿Estás seguro de eliminar este usuario?', {
        title: 'Eliminar usuario',
        buttonText: 'Eliminar',
        cancelText: 'Cancelar',
        onConfirm: async () => {
            try {
                const response = await axios.delete(`/users/${id}`)
                success('Usuario eliminado correctamente')
                router.reload({ only: ['users'] })
            } catch (error) {
                if (error.response) {
                    // Error controlado desde backend (403, 500, etc.)
                    errorA(error.response.data.message || 'No se pudo eliminar el usuario')
                } else {
                    // Error inesperado
                    errorA('Error inesperado al eliminar el usuario')
                }

            }
        }
    })
}


const handleConfirm = () => {
    alertState.value.onConfirm?.()
    alertState.value.show = false
}

const handleCancel = () => {
    alertState.value.onCancel?.()
    alertState.value.show = false
}

/* MODAL CREAR */
const openCreateModal = () => {
    createForm.value = { name: '', email: '', password: '', password_confirmation: '' }
    showCreateModal.value = true
}

const closeCreateModal = () => {
    showCreateModal.value = false
}

const createUser = async () => {
    if (!createForm.value.name || !createForm.value.email || !createForm.value.password) {
        info('Por favor completa todos los campos requeridos')
        return
    }

    isSubmitting.value = true
    try {
        const response = await axios.post('/users', createForm.value)
        if (response.data.message === 'success') {
            closeCreateModal()
            router.reload({ only: ['users'] })
            success('Usuario creado correctamente')
        }
    } catch (error) {
        errorA('Error al crear el usuario')
    } finally {
        isSubmitting.value = false
    }
}

const generatePassword = () => {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%&*'
    let password = ''
    for (let i = 0; i < 12; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length))
    }
    createForm.value.password = password
    createForm.value.password_confirmation = password
}

/* MODAL EDITAR */
const openEditModal = (user) => {
    editForm.value = {
        id: user.id,
        name: user.name,
        email: user.email,
        password: '',
        password_confirmation: '',
        role: user.role
    }
    showEditModal.value = true
}

const closeEditModal = () => {
    showEditModal.value = false
}

const updateUser = async () => {
    isSubmitting.value = true
    try {
        const response = await axios.put(`/users/${editForm.value.id}`, editForm.value)
        if (response.data.message === 'success') {
            closeEditModal()
            router.reload({ only: ['users'] })
            success('Usuario actualizado correctamente')
        }
    } catch (error) {
        errorA('Error al actualizar el usuario')
    } finally {
        isSubmitting.value = false
    }
}
</script>

<template>
    <div>

        <Head title="Usuarios" />

        <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6 lg:ml-[290px]">
            <div class="space-y-5">

                <div class="">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Usuarios</h3>
                    <p class="text-sm text-gray-500">Administra los usuarios de tu aplicación, asigna roles y controla
                        su acceso
                    </p>
                </div>
                <div
                    class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

                    <!-- num. datos, barra de busqueda, crear -->
                    <div
                        class="flex flex-col gap-3 px-4 py-4 border-t border-gray-100 dark:border-white/[0.05] sm:flex-row sm:items-center sm:justify-between">

                        <!-- datos -->
                        <div class="flex items-center gap-3">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">Mostrar</span>
                            <div class="relative z-20">
                                <select v-model.number="perPage"
                                    class="w-full py-2 pl-3 pr-8 text-sm text-gray-800 bg-transparent border border-gray-300 rounded-lg appearance-none dark:bg-gray-900 h-9 shadow-sm placeholder:text-gray-400 focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:text-white/90">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                                <span
                                    class="absolute z-30 text-gray-500 -translate-y-1/2 pointer-events-none right-2 top-1/2 dark:text-gray-400">
                                    <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke=""
                                            stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                            <span class="text-gray-500 dark:text-gray-400 text-sm">entradas</span>
                        </div>

                        <!-- busqueda + boton -->
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <div class="relative flex-1 sm:flex-none">
                                <input v-model="search" type="text" placeholder="Buscar..."
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2 pl-10 pr-4 text-sm text-gray-800 shadow-sm placeholder:text-gray-400 focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 sm:w-[260px]">
                                <span class="absolute text-gray-500 -translate-y-1/2 left-3 top-1/2 dark:text-gray-400">
                                    <svg class="fill-current" width="18" height="18" viewBox="0 0 20 20" fill="none">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z" />
                                    </svg>
                                </span>
                            </div>

                            <button @click="openCreateModal"
                                class="inline-flex h-10 shrink-0 justify-center items-center gap-2 rounded-lg bg-green-600 text-white px-4 text-sm font-medium hover:bg-green-700 transition-colors shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                    class="w-4 h-4">
                                    <path
                                        d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                                </svg>
                                Nuevo usuario
                            </button>
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="max-w-full overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-t border-gray-100 dark:border-white/[0.05]">
                                    <th class="px-4 py-3 border border-gray-100 dark:border-white/[0.05] text-left">
                                        <p class="font-medium text-gray-700 text-xs dark:text-gray-400">Nombre</p>
                                    </th>
                                    <th class="px-4 py-3 border border-gray-100 dark:border-white/[0.05] text-left">
                                        <p class="font-medium text-gray-700 text-xs dark:text-gray-400">Correo</p>
                                    </th>
                                    <th class="px-4 py-3 border border-gray-100 dark:border-white/[0.05] text-center">
                                        <p class="font-medium text-gray-700 text-xs dark:text-gray-400">Rol</p>
                                    </th>
                                    <th class="px-4 py-3 border border-gray-100 dark:border-white/[0.05] text-center">
                                        <p class="font-medium text-gray-700 text-xs dark:text-gray-400">Estado</p>
                                    </th>
                                    <th class="px-4 py-3 border border-gray-100 dark:border-white/[0.05] text-center">
                                        <p class="font-medium text-gray-700 text-xs dark:text-gray-400">Acciones</p>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="paginatedUsers.length === 0">
                                    <td colspan="5"
                                        class="px-4 py-8 text-center text-sm text-gray-400 border border-gray-100 dark:border-white/[0.05]">
                                        No se encontraron usuarios
                                    </td>
                                </tr>
                                <tr v-for="user in paginatedUsers" :key="user.id">
                                    <!-- nombre -->
                                    <td class="px-4 py-3 border border-gray-100 dark:border-white/[0.05]">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-sm font-semibold text-gray-600 dark:text-gray-300 shrink-0">
                                                {{ user.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <span
                                                class="font-medium text-gray-800 text-sm dark:text-white/90 whitespace-nowrap">
                                                {{ user.name }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- correo -->
                                    <td class="px-4 py-3 border border-gray-100 dark:border-white/[0.05]">
                                        <p class="text-gray-700 text-sm dark:text-gray-400">{{ user.email }}</p>
                                    </td>

                                    <!-- rol -->
                                    <td class="px-4 py-3 border border-gray-100 dark:border-white/[0.05] text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                            :class="user.role === 'administrador'
                                                ? 'bg-purple-100 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400'
                                                : 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400'">
                                            {{ user.role }}
                                        </span>
                                    </td>

                                    <!-- estado -->
                                    <td class="px-4 py-3 border border-gray-100 dark:border-white/[0.05] text-center">
                                        <button @click="statusChange(user)"
                                            :title="user.is_active ? 'Desactivar usuario' : 'Activar usuario'"
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium transition-colors"
                                            :class="user.is_active
                                                ? 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-500/10 dark:text-green-400 dark:hover:bg-green-500/20'
                                                : 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20'">
                                            <span class="w-1.5 h-1.5 rounded-full"
                                                :class="user.is_active ? 'bg-green-500' : 'bg-red-500'">
                                            </span>
                                            {{ user.is_active ? 'Activo' : 'Inactivo' }}
                                        </button>
                                    </td>

                                    <!-- acciones -->
                                    <td class="px-4 py-3 border border-gray-100 dark:border-white/[0.05]">
                                        <div class="flex items-center justify-center gap-2">
                                            <button @click="openEditModal(user)" title="Editar"
                                                class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-colors border border-blue-100 hover:border-blue-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                            </button>
                                            <button @click="deleteUser(user.id)" title="Eliminar"
                                                class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-colors border border-red-100 hover:border-red-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="border border-t-0 rounded-b-xl border-gray-100 py-4 px-4 dark:border-white/[0.05]">
                        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3">
                            <p class="text-sm text-center text-gray-500 dark:text-gray-400 xl:text-left">
                                Mostrando {{ showingFrom }} a {{ showingTo }} de {{ filteredUsers.length }} entradas
                            </p>
                            <div class="flex items-center justify-center gap-1">
                                <!-- anterior pagina -->
                                <button @click="currentPage--" :disabled="currentPage === 1"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                    <svg class="fill-current" width="18" height="18" viewBox="0 0 20 20" fill="none">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M2.58301 9.99868C2.58272 10.1909 2.65588 10.3833 2.80249 10.53L7.79915 15.5301C8.09194 15.8231 8.56682 15.8233 8.85981 15.5305C9.15281 15.2377 9.15297 14.7629 8.86018 14.4699L5.14009 10.7472L16.6675 10.7472C17.0817 10.7472 17.4175 10.4114 17.4175 9.99715C17.4175 9.58294 17.0817 9.24715 16.6675 9.24715L5.14554 9.24715L8.86017 5.53016C9.15297 5.23717 9.15282 4.7623 8.85983 4.4695C8.56684 4.1767 8.09197 4.17685 7.79917 4.46984L2.84167 9.43049C2.68321 9.568 2.58301 9.77087 2.58301 9.99715C2.58301 9.99766 2.58301 9.99817 2.58301 9.99868Z" />
                                    </svg>
                                </button>

                                <!-- paginas -->
                                <button v-for="page in visiblePages" :key="page" @click="currentPage = page"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg text-sm font-medium transition-colors"
                                    :class="currentPage === page
                                        ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400'
                                        : 'text-gray-700 dark:text-gray-400 hover:bg-blue-500/10 hover:text-blue-600 dark:hover:text-blue-400'">
                                    {{ page }}
                                </button>

                                <!-- siguiente pag -->
                                <button @click="currentPage++"
                                    :disabled="currentPage === totalPages || totalPages === 0"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                    <svg class="fill-current" width="18" height="18" viewBox="0 0 20 20" fill="none">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M17.4175 9.9986C17.4178 10.1909 17.3446 10.3832 17.198 10.53L12.2013 15.5301C11.9085 15.8231 11.4337 15.8233 11.1407 15.5305C10.8477 15.2377 10.8475 14.7629 11.1403 14.4699L14.8604 10.7472L3.33301 10.7472C2.91879 10.7472 2.58301 10.4114 2.58301 9.99715C2.58301 9.58294 2.91879 9.24715 3.33301 9.24715L14.8549 9.24715L11.1403 5.53016C10.8475 5.23717 10.8477 4.7623 11.1407 4.4695C11.4336 4.1767 11.9085 4.17685 12.2013 4.46984L17.1588 9.43049C17.3173 9.568 17.4175 9.77087 17.4175 9.99715C17.4175 9.99763 17.4175 9.99812 17.4175 9.9986Z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL CREAR -->
        <Modal :show="showCreateModal" @close="closeCreateModal" maxWidth="lg">
            <template #title>Crear usuario</template>

            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre del usuario <span class="text-red-500">*</span>
                    </label>
                    <input v-model="createForm.name" type="text" placeholder="Nombre completo"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Correo electrónico <span class="text-red-500">*</span>
                    </label>
                    <input v-model="createForm.email" type="email" placeholder="correo@ejemplo.com"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                </div>

                <!-- contraseña con boton generar -->
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
                    <input v-model="createForm.password" type="text" placeholder="••••••••"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500 font-mono">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Confirmar contraseña <span class="text-red-500">*</span>
                    </label>
                    <input v-model="createForm.password_confirmation" type="text" placeholder="••••••••"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500 font-mono">
                </div>
            </div>

            <template #footer>
                <button @click="closeCreateModal" :disabled="isSubmitting"
                    class="rounded-md border border-transparent py-2 px-4 text-sm text-slate-600 transition-all hover:bg-slate-100 disabled:opacity-50">
                    Cancelar
                </button>
                <button @click="createUser" :disabled="isSubmitting"
                    class="rounded-md bg-green-600 py-2 px-4 text-sm text-white transition-all shadow-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    {{ isSubmitting ? 'Creando...' : 'Crear usuario' }}
                </button>
            </template>
        </Modal>

        <!-- MODAL EDITAR -->
        <Modal :show="showEditModal" @close="closeEditModal" maxWidth="lg">
            <template #title>Editar usuario</template>

            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre del usuario <span class="text-red-500">*</span>
                    </label>
                    <input v-model="editForm.name" type="text" placeholder="Nombre completo"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Correo electrónico <span class="text-red-500">*</span>
                    </label>
                    <input v-model="editForm.email" type="email" placeholder="correo@ejemplo.com"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>

                <!-- ROL -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Rol <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select v-model="editForm.role"
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
                    <input v-model="editForm.password" type="password" placeholder="Dejar vacío para no cambiar"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Confirmar contraseña
                    </label>
                    <input v-model="editForm.password_confirmation" type="password" placeholder="••••••••"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            <template #footer>
                <button @click="closeEditModal" :disabled="isSubmitting"
                    class="rounded-md border border-transparent py-2 px-4 text-sm text-slate-600 transition-all hover:bg-slate-100 disabled:opacity-50">
                    Cancelar
                </button>
                <button @click="updateUser" :disabled="isSubmitting"
                    class="rounded-md bg-blue-600 py-2 px-4 text-sm text-white transition-all shadow-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    {{ isSubmitting ? 'Actualizando...' : 'Actualizar usuario' }}
                </button>
            </template>
        </Modal>

        <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
            :buttonText="alertState.buttonText" :cancelText="alertState.cancelText" @confirm="handleConfirm"
            @cancel="handleCancel" @close="alertState.show = false" />
    </div>
</template>