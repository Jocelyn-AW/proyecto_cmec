<script setup>
/**
 * Componente DataTable reutilizable para catalogos con paginacion de Laravel.
 *
 * Uso basico:
 *
 *   <DataTable
 *     :paginator="products"
 *     :columns="[
 *       { key: 'id', label: '#', width: '80px' },
 *       { key: 'name', label: 'Nombre' },
 *       { key: 'price', label: 'Precio', align: 'right' },
 *     ]"
 *     :only="['products']"
 *   />
 *
 * Con slots personalizados para celdas:
 *
 *   <DataTable :paginator="products" :columns="columns" :only="['products']">
 *     <template #cell-price="{ item }">
 *       ${{ item.price.toFixed(2) }}
 *     </template>
 *     <template #cell-actions="{ item }">
 *       <button @click="edit(item)">Editar</button>
 *     </template>
 *   </DataTable>
 *
 * Props:
 *   - paginator: Objeto completo de Laravel paginate() (contiene .data, .links, etc.)
 *   - columns: Array de { key, label, width?, align? }
 *   - only: Array de strings para partial reload de Inertia
 *   - searchable: Habilitar campo de busqueda (default: false)
 *   - searchParam: Nombre del query param para buscar (default: 'search')
 *   - searchPlaceholder: Placeholder del input de busqueda
 */
import { ref, watch, isRef, toValue, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import Pagination from './Pagination.vue'

const props = defineProps({
    paginator: {
        type: Object,
        required: true
    },
    columns: {
        type: Array,
        required: true
    },
    /** Props a recargar con partial reload de Inertia */
    only: {
        type: Array,
        default: () => []
    },
    /** Habilitar busqueda */
    searchable: {
        type: Boolean,
        default: false
    },
    /** Nombre del query param para la busqueda en el backend */
    searchParam: {
        type: String,
        default: 'search'
    },
    searchPlaceholder: {
        type: String,
        default: 'Buscar...'
    },
    /** Habilitar selector de registros por pagina */
    perPageOptions: {
        type: Array,
        default: () => []
    },
    /** Nombre del query param para per_page en el backend */
    perPageParam: {
        type: String,
        default: 'per_page'
    },
    /**
     * Objeto plano con los filtros personalizados que vienen del slot #filters.
     * Formato: { queryParamName: refOValue, ... }
     * Ejemplo: { status: statusRef, category_id: categoryRef }
     *
     * Los valores vacíos ('', null, undefined) se omiten del query string.
     */
    filterValues: {
        type: Object,
        default: () => ({})
    },
    allowActions: {
        type: Boolean,
        default: false
    },
    allowCreate: {
        type: Boolean,
        default: false
    },
    allowEdit: {
        type: Boolean,
        default: false
    },
    allowDelete: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['create', 'edit', 'delete'])

// --- Registros por pagina ---
const currentUrlParams = new URLSearchParams(window.location.search)

const perPage = ref(
    props.perPageOptions.length > 0
        ? Number(currentUrlParams.get(props.perPageParam)) || props.paginator.per_page
        : props.paginator.per_page
)

// ── Búsqueda ──────────────────────────────────────────────────────────────────
const searchQuery = ref(
    currentUrlParams.get(props.searchParam) || ''
)

// ── buildParams ───────────────────────────────────────────────────────────────
/**
 * Construye el objeto de query params combinando:
 *   - búsqueda
 *   - per_page
 *   - cualquier filtro pasado en filterValues
 *
 * Los valores vacíos ('', null, undefined) se omiten para mantener
 * la URL limpia.
 */
const buildParams = () => {
    const params = {}
    if (searchQuery.value) {
        params[props.searchParam] = searchQuery.value
    }
    if (props.perPageOptions.length > 0) {
        params[props.perPageParam] = perPage.value
    }

    // Agrega los filtros personalizados del slot
    for (const [key, val] of Object.entries(props.filterValues)) {
        // Soporta tanto refs como valores primitivos
        const resolved = toValue(val)
        if (resolved !== '' && resolved !== null && resolved !== undefined) {
            params[key] = resolved
        }
    }

    return params
}

/**
 * Navega con Inertia preservando estado.
 */
const navigate = (params, options = {}) => {
    router.get(
        window.location.pathname,
        params,
        {
            preserveState: true,
            preserveScroll: true,
            only: props.only.length > 0 ? props.only : undefined,
            replace: true,
            ...options
        }
    )
}

const onPerPageChange = () => {
    navigate({ ...buildParams(), page: 1 })
}

let navTimeout = null

const debouncedNavigate = (params) => {
    clearTimeout(navTimeout)
    navTimeout = setTimeout(() => navigate(params), 300)
}

watch(
    [
        searchQuery,
        () => Object.values(props.filterValues).map(v => toValue(v)),
        perPage,
    ],
    () => {
        debouncedNavigate({ ...buildParams(), page: 1 })
    }
)
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <!-- Header: filtros + búsqueda + per page + acciones -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4">
            <!-- slot para agregar filtros personalizados -->
            <slot name="filters" />

            <div class="flex grow justify-between items-center gap-3 w-full sm:w-auto">
                <!-- Selector de registros por pagina -->
                <div v-if="perPageOptions.length > 0" class="flex items-center gap-2">
                    <label
                        for="per-page-select"
                        class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                    >
                        Mostrar
                    </label>
                    <select
                        id="per-page-select"
                        v-model.number="perPage"
                        class="rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-700 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                    >
                        <option v-for="option in perPageOptions" :key="option" :value="option">
                            {{ option }}
                        </option>
                    </select>
                </div>
                <div class="flex gap-4">
                <!-- Busqueda -->
                    <div v-if="searchable" class="w-full sm:w-80">
                        <div class="relative">
                            <svg
                                class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                            </svg>
                            <input
                                v-model="searchQuery"
                                type="text"
                                :placeholder="searchPlaceholder"
                                class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-10 pr-4 text-sm text-gray-700 placeholder-gray-400 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:placeholder-gray-500"
                            />
                        </div>
                    </div>
                    

                    <div v-if="allowCreate">
                        <button
                            @click="$emit('create')"
                            class="ml-auto inline-flex items-center gap-1 rounded-lg bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                        >
                            Nuevo
                        </button>
                    </div>
                </div>
            </div>

            <!-- Slot para botones extra (ej: "Nuevo producto") -->
            <div class="flex items-center gap-2">
                <slot name="actions" />
            </div>
        </div>

        <!-- Tabla -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-y border-gray-200 dark:border-gray-800">
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"
                            :class="{
                                'text-right': col.align === 'right',
                                'text-center': col.align === 'center'
                            }"
                            :style="col.width ? { width: col.width } : {}"
                        >
                            {{ col.label }}
                        </th>
                        <th v-if="allowActions" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr
                        v-for="item in paginator.data"
                        :key="item.id"
                        class="transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]"
                    >
                        <td
                            v-for="col in columns"
                            :key="col.key"
                            class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300"
                            :class="{
                                'text-right': col.align === 'right',
                                'text-center': col.align === 'center'
                            }"
                        >
                            <!--
                                Si existe un slot #cell-[key], usalo.
                                Si no, muestra el valor plano item[col.key].
                            -->
                            <slot :name="`cell-${col.key}`" :item="item">
                                {{ item[col.key] }}
                            </slot>
                        </td>
                        <td v-if="allowActions" class="sticky whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                            <slot name="actionButtons" :item="item" />
                            <button
                                v-if="allowEdit"
                                @click="emit('edit', item)"
                                title="Editar"
                                class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-colors border border-blue-100 hover:border-blue-600"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                </svg>
                            </button>
                            <button
                                v-if="allowDelete"
                                @click="emit('delete', item.id)"
                                title="Eliminar"
                                class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-colors border border-red-100 hover:border-red-600"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </td>
                    </tr>

                    <!-- Estado vacio -->
                    <tr v-if="paginator.data.length === 0">
                        <td
                            :colspan="columns.length + (allowActions ? 1 : 0)"
                            class="px-6 py-12 text-center text-gray-400 dark:text-gray-500"
                        >
                            <slot name="empty">
                                No se encontraron resultados.
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Paginacion -->
        <div class="border-t border-gray-200 px-4 dark:border-gray-800">
            <Pagination :paginator="paginator" :only="only" />
        </div>
    </div>
</template>