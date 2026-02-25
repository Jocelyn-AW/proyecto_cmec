<script setup>
import { ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Alerta from '@/Components/Alerta.vue'
import { useAlert } from '@/composables/useAlert'
import NewsListItem from './NewsListItem.vue'
import NewsEdit from './NewsEdit.vue'

defineOptions({
    layout: AuthenticatedLayout
})

const props = defineProps({
    news: { type: Array, default: () => [] },
    pagination: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) }
})

const { alertState, success, errorA, warning } = useAlert()

// --- Drawer ---
const showEditDrawer = ref(false)
const newsToEdit = ref(null)

// --- Filtros ---
const search = ref(props.filters.search || '')
const filterType = ref(props.filters.type || '')
const filterStatus = ref(props.filters.status || '')
const filterHasImage = ref(props.filters.has_image || '')
const filterHasPdf = ref(props.filters.has_pdf || '')
const filterHasLink = ref(props.filters.has_link || '')

let searchTimer = null
const applyFilters = () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => {
        router.get('/news', {
            search: search.value || undefined,
            type: filterType.value || undefined,
            status: filterStatus.value || undefined,
            has_image: filterHasImage.value || undefined,
            has_pdf: filterHasPdf.value || undefined,
            has_link: filterHasLink.value || undefined,
            page: 1,
        }, { preserveScroll: true, replace: true })
    }, 450)
}

watch([filterType, filterStatus, filterHasImage, filterHasPdf, filterHasLink], applyFilters)
watch(search, applyFilters)

const clearFilters = () => {
    search.value = ''
    filterType.value = ''
    filterStatus.value = ''
    filterHasImage.value = ''
    filterHasPdf.value = ''
    filterHasLink.value = ''
}

const hasActiveFilters = () =>
    search.value || filterType.value || filterStatus.value ||
    filterHasImage.value || filterHasPdf.value || filterHasLink.value

const goToPage = (page) => {
    router.get('/news', {
        search: search.value || undefined,
        type: filterType.value || undefined,
        status: filterStatus.value || undefined,
        has_image: filterHasImage.value || undefined,
        has_pdf: filterHasPdf.value || undefined,
        has_link: filterHasLink.value || undefined,
        page,
    }, { preserveScroll: true })
}

// --- Acciones ---
const openCreate = () => router.get('/news/create')

const openEditDrawer = (item) => {
    newsToEdit.value = item
    showEditDrawer.value = true
}

const closeEditDrawer = () => {
    showEditDrawer.value = false
    newsToEdit.value = null
}

const statusChange = (item) => {
    router.patch(`/news/${item.id}/status`, {}, {
        preserveScroll: true,
        onSuccess: () => success(`Noticia ${item.is_active ? 'desactivada' : 'activada'} correctamente`),
        onError: () => errorA('Error al cambiar el estado')
    })
}

const deleteNews = (id) => {
    warning('¿Estás seguro de eliminar esta noticia?', {
        title: 'Eliminar noticia',
        buttonText: 'Eliminar',
        cancelText: 'Cancelar',
        onConfirm: () => {
            router.delete(`/news/${id}`, {
                preserveScroll: true,
                onSuccess: () => success('Noticia eliminada correctamente'),
                onError: () => errorA('Error al eliminar la noticia')
            })
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

const selectClass = 'h-10 py-2 pl-3 pr-8 text-sm text-gray-800 bg-transparent border border-gray-300 rounded-lg appearance-none dark:bg-gray-900 shadow-sm focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:text-white/90'
const chevronSvg = `<svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>`
</script>

<template>
    <div>

        <Head title="Noticias" />

        <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6 lg:ml-[290px]">
            <div class="space-y-5">

                <!-- Encabezado -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Noticias</h3>
                    <p class="text-sm text-gray-500">Administra las noticias y sesiones publicadas</p>
                </div>

                <!-- Toolbar -->
                <div
                    class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">

                        <div class="flex flex-wrap items-center gap-3">

                            <!-- Buscador -->
                            <div class="relative">
                                <input v-model="search" type="text" placeholder="Buscar noticia..."
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2 pl-10 pr-4 text-sm text-gray-800 shadow-sm placeholder:text-gray-400 focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 sm:w-[220px]">
                                <span class="absolute text-gray-500 -translate-y-1/2 left-3 top-1/2 dark:text-gray-400">
                                    <svg class="fill-current" width="18" height="18" viewBox="0 0 20 20" fill="none">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z" />
                                    </svg>
                                </span>
                            </div>

                            <!-- Tipo -->
                            <div class="relative">
                                <select v-model="filterType" :class="selectClass">
                                    <option value="">Todos los tipos</option>
                                    <option value="noticia">Noticia</option>
                                    <option value="sesion">Sesión</option>
                                </select>
                                <span
                                    class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                                    <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke=""
                                            stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>

                            <!-- Estado -->
                            <div class="relative">
                                <select v-model="filterStatus" :class="selectClass">
                                    <option value="">Todos los estados</option>
                                    <option value="active">Activos</option>
                                    <option value="inactive">Inactivos</option>
                                </select>
                                <span
                                    class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                                    <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke=""
                                            stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>

                            <!-- Imagen -->
                            <div class="relative">
                                <select v-model="filterHasImage" :class="selectClass">
                                    <option value="">Imágenes</option>
                                    <option value="yes">Con imagen</option>
                                    <option value="no">Sin imagen</option>
                                </select>
                                <span
                                    class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                                    <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke=""
                                            stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>

                            <!-- PDF -->
                            <div class="relative">
                                <select v-model="filterHasPdf" :class="selectClass">
                                    <option value="">PDF</option>
                                    <option value="yes">Con PDF</option>
                                    <option value="no">Sin PDF</option>
                                </select>
                                <span
                                    class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                                    <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke=""
                                            stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>

                            <!-- URL -->
                            <div class="relative">
                                <select v-model="filterHasLink" :class="selectClass">
                                    <option value="">URLs</option>
                                    <option value="yes">Con URL</option>
                                    <option value="no">Sin URL</option>
                                </select>
                                <span
                                    class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                                    <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke=""
                                            stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>

                            <!-- Limpiar filtros -->
                            <button v-if="hasActiveFilters()" @click="clearFilters"
                                class="inline-flex items-center gap-1.5 h-10 px-3 rounded-lg border border-gray-300 dark:border-gray-700 text-sm text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-red-500 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Limpiar
                            </button>
                        </div>

                        <!-- Botón crear -->
                        <button @click="openCreate"
                            class="inline-flex h-10 shrink-0 justify-center items-center gap-2 rounded-lg bg-green-600 text-white px-4 text-sm font-medium hover:bg-green-700 transition-colors shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                class="w-4 h-4">
                                <path
                                    d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                            </svg>
                            Nueva noticia
                        </button>
                    </div>
                </div>

                <!-- Grid de cards -->
                <div v-if="props.news.length > 0" class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    <NewsListItem v-for="item in props.news" :key="item.id" :item="item" @edit="openEditDrawer"
                        @delete="deleteNews" @status-change="statusChange" />
                </div>

                <!-- Sin resultados -->
                <div v-else
                    class="rounded-2xl border border-dashed border-gray-200 dark:border-gray-700 bg-white dark:bg-white/[0.03] py-16 flex flex-col items-center justify-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-12 h-12 text-gray-300 dark:text-gray-600">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z" />
                    </svg>
                    <p class="text-sm text-gray-400 dark:text-gray-500">No se encontraron noticias</p>
                </div>

                <!-- Paginación -->
                <div v-if="pagination && pagination.last_page > 1"
                    class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3 px-1">
                    <p class="text-sm text-center text-gray-500 dark:text-gray-400 xl:text-left">
                        Mostrando {{ pagination.from }} a {{ pagination.to }} de {{ pagination.total }} noticias
                    </p>
                    <div class="flex items-center justify-center gap-1">
                        <button @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            <svg class="fill-current" width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M2.58301 9.99868C2.58272 10.1909 2.65588 10.3833 2.80249 10.53L7.79915 15.5301C8.09194 15.8231 8.56682 15.8233 8.85981 15.5305C9.15281 15.2377 9.15297 14.7629 8.86018 14.4699L5.14009 10.7472L16.6675 10.7472C17.0817 10.7472 17.4175 10.4114 17.4175 9.99715C17.4175 9.58294 17.0817 9.24715 16.6675 9.24715L5.14554 9.24715L8.86017 5.53016C9.15297 5.23717 9.15282 4.7623 8.85983 4.4695C8.56684 4.1767 8.09197 4.17685 7.79917 4.46984L2.84167 9.43049C2.68321 9.568 2.58301 9.77087 2.58301 9.99715C2.58301 9.99766 2.58301 9.99817 2.58301 9.99868Z" />
                            </svg>
                        </button>
                        <button v-for="page in pagination.last_page" :key="page" @click="goToPage(page)"
                            class="flex h-9 w-9 items-center justify-center rounded-lg text-sm font-medium transition-colors"
                            :class="pagination.current_page === page
                                ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400'
                                : 'text-gray-700 dark:text-gray-400 hover:bg-blue-500/10 hover:text-blue-600 dark:hover:text-blue-400'">
                            {{ page }}
                        </button>
                        <button @click="goToPage(pagination.current_page + 1)"
                            :disabled="pagination.current_page === pagination.last_page"
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

        <!-- Drawer de edición -->
        <NewsEdit :show="showEditDrawer" :news-item="newsToEdit" @close="closeEditDrawer"
            @success="(msg) => success(msg)" @error="(msg) => errorA(msg)" @warning="(msg) => warning(msg)" />

        <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
            :buttonText="alertState.buttonText" :cancelText="alertState.cancelText" @confirm="handleConfirm"
            @cancel="handleCancel" @close="alertState.show = false" />
    </div>
</template>
