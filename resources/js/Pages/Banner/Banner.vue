<script setup>
import { ref, watch, computed } from 'vue'
import draggable from 'vuedraggable'
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
    banners: {
        type: Array,
        default: () => []
    }
})


const { alertState, success, error, warning, info } = useAlert()
const showCreateModal = ref(false)
const showEditModal = ref(false)
const enabled = ref(true)
const list = ref([])
const original = ref([])
const dragging = ref(false)

// Form para crear banner
const createForm = ref({
    image: null,
    link: '',
    order: 0
})

// Form para editar banner
const editForm = ref({
    id: null,
    image: null,
    link: '',
    order: 0,
    currentImage: null // mostrar la imagen actual
})

const createImagePreview = ref(null)
const editImagePreview = ref(null)
const isSubmitting = ref(false)

// order para nuevo banner
const nextOrder = computed(() => {
    if (list.value.length === 0) return 0
    const maxOrder = Math.max(...list.value.map(b => b.order ?? 0))
    return maxOrder + 1
})

const init = () => {
    const sorted = [...props.banners].sort(
        (a, b) => (a.order ?? 0) - (b.order ?? 0)
    )

    list.value = sorted.map(b => ({ ...b }))
    original.value = sorted.map(b => ({ ...b }))
}

watch(() => props.banners, init, { immediate: true })

const checkMove = (e) => {
    return true
}

const startDrag = () => { dragging.value = true }
const endDrag = () => { dragging.value = false }

const saveOrder = async () => {
    try {
        const payload = {
            banners: list.value.map((b, idx) => ({
                id: b.id,
                order: idx
            }))
        }

        await axios.post('/banners/reorder', payload)

        original.value = list.value.map(b => ({ ...b }))

        console.log('Orden actualizado exitosamente')
        success('Orden actualizado correctamente')

    } catch (error) {
        console.error(error)
        alert('Error actualizando el orden')
    }
}

const cancelChanges = () => {
    list.value = original.value.map(b => ({ ...b }))
}

const deleteBanner = (id) => {
    if (!confirm('¿Eliminar banner?')) return

    router.delete(`/banners/${id}`, {
        preserveScroll: true,
        onSuccess: () => {
            list.value = list.value.filter(b => b.id !== id)
            original.value = original.value.filter(b => b.id !== id)
        }
    })
}

/* MODAL CREAR */
const openCreateModal = () => {
    resetCreateForm()
    createForm.value.order = nextOrder.value
    showCreateModal.value = true
}

const closeCreateModal = () => {
    showCreateModal.value = false
    resetCreateForm()
}

const resetCreateForm = () => {
    createForm.value = {
        image: null,
        link: '',
        order: 0
    }
    createImagePreview.value = null
}

const handleCreateImageChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        createForm.value.image = file

        // preview
        const reader = new FileReader()
        reader.onload = (e) => {
            createImagePreview.value = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const createBanner = async () => {
    if (!createForm.value.image) {
        alert('Por favor selecciona una imagen')
        return
    }

    isSubmitting.value = true

    try {
        const formData = new FormData()
        formData.append('image', createForm.value.image)
        formData.append('order', createForm.value.order)

        if (createForm.value.link && createForm.value.link.trim() !== '') {
            formData.append('link', createForm.value.link)
        }

        const response = await axios.post('/banners', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })

        if (response.data.message === 'success') {
            //alert('Banner creado exitosamente')
            closeCreateModal()
            router.reload({ only: ['banners'] })
            success('El banner ha sido creado correctamente')
        }

    } catch (error) {
        console.error('Error creando banner:', error)
        //alert(error.response?.data?.message || 'Error al crear el banner')
        alert('Error al crear el banner')
    } finally {
        isSubmitting.value = false
    }
}

/* MODAL EDITAR */
const openEditModal = (id) => {
    const banner = list.value.find(b => b.id === id)

    if (!banner) {
        alert('Banner no encontrado')
        return
    }

    editForm.value = {
        id: banner.id,
        image: null,
        link: banner.link || '',
        order: banner.order,
        currentImage: banner.image // guardas la imagen actual
    }

    editImagePreview.value = null
    showEditModal.value = true
}

const closeEditModal = () => {
    showEditModal.value = false
    resetEditForm()
}

const resetEditForm = () => {
    editForm.value = {
        id: null,
        image: null,
        link: '',
        order: 0,
        currentImage: null
    }
    editImagePreview.value = null
}

const handleEditImageChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        editForm.value.image = file

        const reader = new FileReader()
        reader.onload = (e) => {
            editImagePreview.value = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const updateBanner = async () => {
    isSubmitting.value = true

    try {
        const formData = new FormData()
        formData.append('order', editForm.value.order)

        if (editForm.value.link && editForm.value.link.trim() !== '') {
            formData.append('link', editForm.value.link)
        }

        /**solo actualizar si hay nueva */
        if (editForm.value.image) {
            formData.append('image', editForm.value.image)
        }

        const response = await axios.post(`/banners/${editForm.value.id}`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })

        if (response.data.message === 'success') {
            closeEditModal()
            router.reload({ only: ['banners'] })
        }

    } catch (error) {
        console.error('Error actualizando banner:', error)
        alert('Error al actualizar el banner')
    } finally {
        isSubmitting.value = false
    }
}
</script>

<template>
    <div>

        <Head title="Banners" />

        <Alerta
            :show="alertState.show"
            :message="alertState.message"
            :title="alertState.title"
            :type="alertState.type"
            :duration="alertState.duration"
            :buttonText="alertState.buttonText"
            @close="alertState.show = false"
        />

        <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6 lg:ml-[290px]">
            <div class="space-y-5">
                <div
                    class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex flex-col gap-5 px-6 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Banners</h3>
                            <p class="text-sm text-gray-500">Arrastra las filas para reordenar y pulsa "Guardar orden"
                            </p>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <div>
                                <button @click="cancelChanges"
                                    class="inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Cancelar
                                </button>
                                <button @click="saveOrder"
                                    class="inline-flex h-10 items-center gap-2 rounded-lg bg-zinc-900 text-white px-4 py-2.5 text-sm font-medium hover:bg-zinc-800 ml-2">
                                    Guardar orden
                                </button>
                                <button @click="openCreateModal"
                                    class="inline-flex h-10 items-center gap-2 rounded-lg bg-green-600 text-white px-4 py-2.5 text-sm font-medium hover:bg-green-700 ml-2">
                                    Nuevo
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="block px-5 pb-4">
                        <div class="space-y-4">
                            <draggable :list="list" :disabled="!enabled" ghost-class="ghost" :move="checkMove"
                                item-key="id" @start="startDrag" @end="endDrag">
                                <template #item="{ element, index }">
                                    <div :key="element.id"
                                        class="bg-white dark:bg-white/[0.03] border border-gray-200 dark:border-gray-800 rounded-2xl p-6 flex items-center gap-4">
                                        <div class="w-28 h-20 flex-shrink-0 overflow-hidden rounded">
                                            <img v-if="element.image" :src="element.image"
                                                class="w-full h-full object-cover" />
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div
                                                class="text-base font-medium leading-6 text-gray-800 dark:text-white/90 truncate">
                                                {{ element.name }}</div>
                                            <div v-if="element.link" class="text-sm text-blue-600 truncate max-w-full">
                                                <a :href="element.link" target="_blank" class="hover:underline">{{
                                                    element.link }}</a>
                                            </div>
                                            <div v-else class="text-sm text-gray-400">— sin link —</div>
                                        </div>

                                        <div class="flex-shrink-0 flex flex-col gap-2">
                                            <button @click="openEditModal(element.id)"
                                                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-blue-600 hover:bg-blue-700 px-3 py-2 text-sm font-medium text-white">
                                                Editar
                                            </button>
                                            <button @click="deleteBanner(element.id)"
                                                class="inline-flex items-center gap-2 rounded-lg bg-red-600 text-white px-3 py-2 text-sm font-medium hover:bg-red-700">
                                                Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </draggable>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL CREAR -->
        <Modal :show="showCreateModal" @close="closeCreateModal" maxWidth="2xl">
            <template #title>
                Crear banner
            </template>

            <div class="p-6 space-y-4">
                <div v-if="createImagePreview" class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Vista previa</label>
                    <div class="w-full h-48 overflow-hidden rounded-lg border border-gray-300">
                        <img :src="createImagePreview" class="w-full h-full object-cover" alt="Preview">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Imagen <span class="text-red-500">*</span>
                    </label>
                    <input type="file" accept="image/jpeg,image/png,image/jpg,image/webp"
                        @change="handleCreateImageChange"
                        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                    <p class="mt-1 text-xs text-gray-500">
                        JPG, PNG, WEBP (máx. 5MB)
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Link (opcional)
                    </label>
                    <input v-model="createForm.link" type="text" placeholder="https://ejemplo.com"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:ring-green-500">
                </div>

                <!-- <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">Orden:</span> {{ form.order }}
                    </p>
                </div> -->
            </div>

            <template #footer>
                <button @click="closeCreateModal" :disabled="isSubmitting"
                    class="rounded-md border border-transparent py-2 px-4 text-sm text-slate-600 transition-all hover:bg-slate-100 disabled:opacity-50">
                    Cancelar
                </button>

                <button @click="createBanner" :disabled="isSubmitting || !createForm.image"
                    class="rounded-md bg-green-600 py-2 px-4 text-sm text-white transition-all shadow-md hover:bg-green-700 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                    {{ isSubmitting ? 'Creando...' : 'Crear banner' }}
                </button>
            </template>
        </Modal>

        <!-- MODAL EDITAR -->
        <Modal :show="showEditModal" @close="closeEditModal" maxWidth="2xl">
            <template #title>
                Editar banner
            </template>

            <div class="p-6 space-y-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ editImagePreview ? 'Nueva imagen' : 'Imagen actual' }}
                    </label>
                    <div class="w-full h-48 overflow-hidden rounded-lg border border-gray-300">
                        <img :src="editImagePreview || editForm.currentImage" class="w-full h-full object-cover"
                            alt="Banner">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Cambiar imagen (opcional)
                    </label>
                    <input type="file" accept="image/jpeg,image/png,image/jpg,image/webp"
                        @change="handleEditImageChange"
                        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                    <p class="mt-1 text-xs text-gray-500">
                        JPG, PNG, WEBP (máx. 5MB). Deja vacío para mantener la imagen actual.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Link (opcional)
                    </label>
                    <input v-model="editForm.link" type="text" placeholder="https://ejemplo.com"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">Orden actual:</span> {{ editForm.order }}
                    </p>
                </div> -->
            </div>

            <template #footer>
                <button @click="closeEditModal" :disabled="isSubmitting"
                    class="rounded-md border border-transparent py-2 px-4 text-sm text-slate-600 transition-all hover:bg-slate-100 disabled:opacity-50">
                    Cancelar
                </button>

                <button @click="updateBanner" :disabled="isSubmitting"
                    class="rounded-md bg-blue-600 py-2 px-4 text-sm text-white transition-all shadow-md hover:bg-blue-700 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                    {{ isSubmitting ? 'Actualizando...' : 'Actualizar banner' }}
                </button>
            </template>
        </Modal>
    </div>
</template>

<style scoped>
.ghost {
    opacity: 0.5;
    background: #c8ebfb;
}

.list-group {
    padding: 0;
}

.list-group-item {
    list-style: none;
}
</style>