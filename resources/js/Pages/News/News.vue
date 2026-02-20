<script setup>
import { ref, computed, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Alerta from '@/Components/Alerta.vue'
import { useAlert } from '@/composables/useAlert'

defineOptions({
    layout: AuthenticatedLayout
})

const props = defineProps({
    news: {
        type: Array,
        default: () => []
    },
    // paginacion del backend
    pagination: {
        type: Object,
        default: () => ({})
    }
})

const { alertState, success, errorA, warning } = useAlert()

const openCreate = () => {
    router.get('/news/create')
}

const stripHtml = (html) => {
    if (!html) return ''
    return html.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim()
}

// filtros
const search = ref('')
const filterType = ref('')      // 'sesion' | 'noticia' | ''
const filterStatus = ref('')    // 'active' | 'inactive' | ''

// filtro local (lo que llega del backend)
const filteredNews = computed(() => {
    return props.news.filter(n => {
        const matchSearch = !search.value ||
            n.title.toLowerCase().includes(search.value.toLowerCase()) ||
            n.content.toLowerCase().includes(search.value.toLowerCase())

        const matchType = !filterType.value || n.type === filterType.value

        const matchStatus = !filterStatus.value ||
            (filterStatus.value === 'active' ? n.is_active : !n.is_active)

        return matchSearch && matchType && matchStatus
    })
})

// --- Acciones ---
const statusChange = (news) => {
    router.patch(`/news/${news.id}/status`, {}, {
        preserveScroll: true,
        onSuccess: () => success(`Noticia ${news.is_active ? 'desactivada' : 'activada'} correctamente`),
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

const goToPage = (page) => {
    router.get('/news', { page }, { preserveScroll: true })
}

const handleConfirm = () => {
    alertState.value.onConfirm?.()
    alertState.value.show = false
}

const handleCancel = () => {
    alertState.value.onCancel?.()
    alertState.value.show = false
}
</script>

<template>
    <div>

        <Head title="Noticias" />

        <div class="p-6 border-t border-gray-100 dark:border-gray-800 sm:p-6 lg:ml-[290px]">
            <div class="space-y-5">

                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Noticias</h3>
                    <p class="text-sm text-gray-500">Administra las noticias y sesiones publicadas</p>
                </div>

                <div
                    class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">

                        <!-- izquierda -->
                        <div class="flex flex-wrap items-center gap-3">

                            <!-- buscador -->
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

                            <!-- filtro tipo -->
                            <div class="relative">
                                <select v-model="filterType"
                                    class="h-10 py-2 pl-3 pr-8 text-sm text-gray-800 bg-transparent border border-gray-300 rounded-lg appearance-none dark:bg-gray-900 shadow-sm focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:text-white/90">
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

                            <!-- filtro estado -->
                            <div class="relative">
                                <select v-model="filterStatus"
                                    class="h-10 py-2 pl-3 pr-8 text-sm text-gray-800 bg-transparent border border-gray-300 rounded-lg appearance-none dark:bg-gray-900 shadow-sm focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:text-white/90">
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
                        </div>

                        <!-- boton crear -->
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

                <!-- grid de cards -->
                <div v-if="filteredNews.length > 0" class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    <article v-for="item in filteredNews" :key="item.id"
                        class="flex flex-col rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
                        <!-- imagen -->
                        <div class="w-full h-48 bg-gray-100 dark:bg-gray-800 flex-shrink-0">
                            <img v-if="item.image" :src="item.image" :alt="item.title"
                                class="w-full h-full object-cover">
                            <div v-else class="w-full h-full flex flex-col items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor"
                                    class="w-10 h-10 text-gray-300 dark:text-gray-600">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                                <span class="text-xs text-gray-400 dark:text-gray-500">Sin imagen</span>
                            </div>
                        </div>

                        <!-- ccontenido -->
                        <div class="flex flex-col flex-1 p-5 gap-3">

                            <!-- tipo -->
                            <div class="flex items-center justify-between">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                    :class="item.type === 'noticia'
                                        ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400'
                                        : 'bg-purple-100 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400'">
                                    {{ item.type }}
                                </span>
                                <span class="text-xs text-gray-400 dark:text-gray-500">{{ item.updated_at }}</span>
                            </div>

                            <!-- titulo -->
                            <h3
                                class="text-sm font-semibold text-gray-800 dark:text-white/90 line-clamp-2 leading-snug">
                                {{ item.title }}
                            </h3>

                            <!-- extracto / contenido -->
                            <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-3 leading-relaxed flex-1">
                                {{ item.extract ? stripHtml(item.extract) : stripHtml(item.content) }}
                            </p>

                            <!-- link -->
                            <div class="h-5">
                                <a v-if="item.link" :href="item.link" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-xs text-blue-600 hover:underline truncate
                                max-w-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-3 h-3 shrink-0">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                    </svg>
                                    <span class="truncate">{{ item.link }}</span>
                                </a>
                                <span v-else class="text-xs text-gray-300 dark:text-gray-600">— sin link —</span>
                            </div>

                            <!-- PDF -->
                            <div class="h-6">
                                <a v-if="item.pdf" :href="item.pdf" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-red-50 text-red-600
                                hover:bg-red-100 transition-colors text-xs font-medium dark:bg-red-500/10
                                dark:text-red-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    Ver PDF
                                </a>



                                <span v-else
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-gray-100 text-gray-400 text-xs font-medium dark:bg-gray-800 dark:text-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    Sin PDF
                                </span>
                            </div>
                        </div>

                        <!-- footer de la card -->
                        <div
                            class="flex items-center justify-between border-t border-gray-100 dark:border-gray-800 px-5 py-3">

                            <!-- toggle activo/inactivo -->
                            <button @click="statusChange(item)" :title="item.is_active ? 'Desactivar' : 'Activar'"
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium transition-colors"
                                :class="item.is_active
                                    ? 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-500/10 dark:text-green-400'
                                    : 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-500/10 dark:text-red-400'">
                                <span class="w-1.5 h-1.5 rounded-full"
                                    :class="item.is_active ? 'bg-green-500' : 'bg-red-500'" />
                                {{ item.is_active ? 'Activo' : 'Inactivo' }}
                            </button>

                            <!-- editar / eliminar -->
                            <div class="flex items-center gap-2">
                                <button title="Editar"
                                    class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-colors border border-blue-100 hover:border-blue-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                </button>
                                <button @click="deleteNews(item.id)" title="Eliminar"
                                    class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-colors border border-red-100 hover:border-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- no noticias -->
                <div v-else
                    class="rounded-2xl border border-dashed border-gray-200 dark:border-gray-700 bg-white dark:bg-white/[0.03] py-16 flex flex-col items-center justify-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-12 h-12 text-gray-300 dark:text-gray-600">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z" />
                    </svg>
                    <p class="text-sm text-gray-400 dark:text-gray-500">No se encontraron noticias</p>
                </div>

                <!-- paginacion -->
                <div v-if="pagination && pagination.last_page > 1"
                    class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3 px-1">
                    <p class="text-sm text-center text-gray-500 dark:text-gray-400 xl:text-left">
                        Mostrando {{ pagination.from }} a {{ pagination.to }} de {{ pagination.total }} noticias
                    </p>

                    <div class="flex items-center justify-center gap-1">
                        <!-- anterior -->
                        <button @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            <svg class="fill-current" width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M2.58301 9.99868C2.58272 10.1909 2.65588 10.3833 2.80249 10.53L7.79915 15.5301C8.09194 15.8231 8.56682 15.8233 8.85981 15.5305C9.15281 15.2377 9.15297 14.7629 8.86018 14.4699L5.14009 10.7472L16.6675 10.7472C17.0817 10.7472 17.4175 10.4114 17.4175 9.99715C17.4175 9.58294 17.0817 9.24715 16.6675 9.24715L5.14554 9.24715L8.86017 5.53016C9.15297 5.23717 9.15282 4.7623 8.85983 4.4695C8.56684 4.1767 8.09197 4.17685 7.79917 4.46984L2.84167 9.43049C2.68321 9.568 2.58301 9.77087 2.58301 9.99715C2.58301 9.99766 2.58301 9.99817 2.58301 9.99868Z" />
                            </svg>
                        </button>

                        <!--paginas -->
                        <button v-for="page in pagination.last_page" :key="page" @click="goToPage(page)"
                            class="flex h-9 w-9 items-center justify-center rounded-lg text-sm font-medium transition-colors"
                            :class="pagination.current_page === page
                                ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400'
                                : 'text-gray-700 dark:text-gray-400 hover:bg-blue-500/10 hover:text-blue-600 dark:hover:text-blue-400'">
                            {{ page }}
                        </button>

                        <!-- siguiente -->
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

        <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
            :buttonText="alertState.buttonText" :cancelText="alertState.cancelText" @confirm="handleConfirm"
            @cancel="handleCancel" @close="alertState.show = false" />
    </div>
</template>