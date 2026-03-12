<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { useAlert } from '@/composables/useAlert'
import { onMounted, ref } from 'vue'
import Alerta from '@/Components/Alerta.vue'
import AlbumCreateModal from './AlbumCreateModal.vue'
import AlbumEditModal from './AlbumEditModal.vue'

defineOptions({ layout: AuthenticatedLayout })

const { alertState, success, errorA, warning, hideAlert } = useAlert()
const page = usePage()

const props = defineProps({
    albums: { type: Array, default: () => [] },
    event_type: { type: String, default: '' },
    event_id: { type: Number, default: null },
    event_label: { type: String, default: 'Evento' },
    back_route: { type: String, default: null },
    flash: { type: Object, default: () => ({}) },
})

onMounted(() => {
    if (page.props.flash?.success || props.flash?.success)
        success(page.props.flash?.success || props.flash?.success)
    if (page.props.flash?.error || props.flash?.error)
        errorA(page.props.flash?.error || props.flash?.error)
})

// crear albums
const showCreate = ref(false)
const createForm = ref({ title: '', description: '' })
const createError = ref('')

// editar album 
const showEdit = ref(false)
const editForm = ref({ id: null, title: '', description: '' })
const editError = ref('')

const openEdit = (album) => {
    editForm.value = { id: album.id, title: album.title, description: album.description ?? '' }
    editError.value = ''
    showEdit.value = true
}

// eliminar album
const handleDelete = (album) => {
    warning(`¿Eliminar el álbum "${album.title}" y todas sus fotos? Esta acción no se puede deshacer.`, {
        title: 'Eliminar álbum',
        buttonText: 'Sí, eliminar',
        cancelText: 'Cancelar',
        onConfirm: () => {
            hideAlert()
            router.delete(route('albums.delete', album.id))
        },
    })
}

// ir a fotos
const openAlbum = (album) => {
    router.get(route('albums.photos', album.id))
}
</script>

<template>

    <Head :title="`Álbumes · ${event_label}`" />

    <div class="p-6 border-t border-gray-100 sm:p-6">
        <div class="space-y-6">

            <!-- encabezado -->
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                        <button v-if="back_route" @click="router.get(route(back_route))"
                            class="hover:text-gray-700 transition-colors">
                            ← {{ event_label }}
                        </button>
                        <span v-if="back_route">/</span>
                        <span>Álbumes</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Galería de álbumes</h3>
                    <p class="text-sm text-gray-500">Organiza las fotos en álbumes</p>
                </div>
                <button @click="showCreate = true; createForm = { title: '', description: '' }"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo álbum
                </button>
            </div>

            <!-- grid de los albumes -->
            <div v-if="albums.length" class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                <div v-for="album in albums" :key="album.id"
                    class="group relative rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden hover:shadow-md transition-shadow">

                    <!-- portada del album -->
                    <div class="aspect-video bg-gray-100 overflow-hidden cursor-pointer" @click="openAlbum(album)">
                        <img v-if="album.cover_url" :src="album.cover_url" :alt="album.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200" />
                        <div v-else class="w-full h-full flex flex-col items-center justify-center gap-2 text-gray-300">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-xs">Sin fotos</span>
                        </div>
                    </div>

                    <!-- info + acciones -->
                    <div class="p-3">
                        <button @click="openAlbum(album)" class="text-left w-full">
                            <p class="font-medium text-gray-800 truncate hover:text-blue-600 transition-colors">
                                {{ album.title }}
                            </p>
                            <p v-if="album.description" class="text-xs text-gray-500 truncate mt-0.5">
                                {{ album.description }}
                            </p>
                        </button>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-xs text-gray-400">{{ album.photos_count }} foto{{ album.photos_count !== 1
                                ? 's' : '' }}</span>
                            <div class="flex gap-1">
                                <button @click="openEdit(album)" title="Editar"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button @click="handleDelete(album)" title="Eliminar"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- sin datos -->
            <div v-else
                class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 py-20 text-center">
                <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <p class="mt-3 text-sm text-gray-500">Aún no hay álbumes</p>
                <button @click="showCreate = true" class="mt-3 text-sm font-medium text-blue-600 hover:text-blue-700">
                    Crear el primer álbum
                </button>
            </div>
        </div>
    </div>

    <AlbumCreateModal :show="showCreate" :event-type="event_type" :event-id="event_id" @close="showCreate = false"
        @created="success('Álbum creado correctamente')" @error="(msg) => errorA(msg)" />

    <AlbumEditModal :show="showEdit" :album="editForm" @close="showEdit = false"
        @updated="success('Álbum actualizado correctamente')" @error="(msg) => errorA(msg)" />

    <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
        :buttonText="alertState.buttonText" :cancelText="alertState.cancelText"
        @confirm="alertState.onConfirm ? alertState.onConfirm() : hideAlert()"
        @cancel="alertState.onCancel ? alertState.onCancel() : hideAlert()" @close="hideAlert()" />
</template>