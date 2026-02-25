<script setup>
/**
 * Componente de paginacion compatible con la estructura de Laravel's LengthAwarePaginator.
 *
 * Laravel envia un objeto con esta estructura cuando usas ->paginate():
 * {
 *   current_page: 1,
 *   last_page: 5,
 *   per_page: 15,
 *   total: 73,
 *   from: 1,
 *   to: 15,
 *   links: [
 *     { url: null, label: "&laquo; Previous", active: false },
 *     { url: "?page=1", label: "1", active: true },
 *     ...
 *     { url: "?page=2", label: "Next &raquo;", active: false }
 *   ]
 * }
 *
 * Uso:
 *   <Pagination :paginator="products" />
 *
 * donde `products` es el objeto completo que Inertia pasa como prop desde Laravel.
 */
import { router } from '@inertiajs/vue3'

const props = defineProps({
    paginator: {
        type: Object,
        required: true
    },
    /** Mantener la posicion del scroll al cambiar de pagina */
    preserveScroll: {
        type: Boolean,
        default: true
    },
    /** Solo recargar estas props al paginar (partial reload) */
    only: {
        type: Array,
        default: () => []
    }
})

/**
 * Navega a la URL de la pagina seleccionada usando Inertia.
 * No recarga toda la pagina, solo las props que necesitas.
 */
const goToPage = (url) => {
    if (!url) return

    const options = {
        preserveScroll: props.preserveScroll,
        preserveState: true,
    }

    // Partial reload: solo recarga las props indicadas
    if (props.only.length > 0) {
        options.only = props.only
    }

    router.get(url, {}, options)
}

/**
 * Limpia las etiquetas HTML que Laravel envia en los labels
 * (ej: "&laquo; Previous" -> "Anterior")
 */
const cleanLabel = (label) => {
    if (label.includes('Previous')) return 'Anterior'
    if (label.includes('Next')) return 'Siguiente'
    return label
}
</script>

<template>
    <div
        v-if="paginator.last_page > 1"
        class="flex flex-col sm:flex-row items-center justify-between gap-4 px-2 py-3"
    >
        <!-- Info: "Mostrando 1 a 15 de 73 resultados" -->
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Mostrando
            <span class="font-medium text-gray-700 dark:text-gray-200">{{ paginator.from }}</span>
            a
            <span class="font-medium text-gray-700 dark:text-gray-200">{{ paginator.to }}</span>
            de
            <span class="font-medium text-gray-700 dark:text-gray-200">{{ paginator.total }}</span>
            resultados
        </p>

        <!-- Botones de pagina -->
        <nav class="flex items-center gap-1">
            <button
                v-for="(link, index) in paginator.links"
                :key="index"
                @click="goToPage(link.url)"
                :disabled="!link.url"
                class="inline-flex items-center justify-center min-w-[36px] h-9 px-3 text-sm font-medium rounded-lg transition-colors"
                :class="[
                    link.active
                        ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900'
                        : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800',
                    !link.url
                        ? 'opacity-40 cursor-not-allowed'
                        : 'cursor-pointer'
                ]"
                v-html="cleanLabel(link.label)"
            />
        </nav>
    </div>
</template>
