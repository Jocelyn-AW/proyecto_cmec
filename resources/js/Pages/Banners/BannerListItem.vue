<script setup>
defineProps({
    banner: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['edit', 'delete', 'status-change'])
</script>

<template>
    <div
        class="bg-white dark:bg-white/[0.03] border border-gray-200 dark:border-gray-800 rounded-2xl p-6 flex items-center gap-4 hover:shadow-sm transition-shadow">

        <!-- Thumbnail -->
        <div class="w-28 h-20 flex-shrink-0 overflow-hidden rounded">
            <img v-if="banner.image" :src="banner.image" class="w-full h-full object-cover"
                :alt="banner.name || 'Banner'" />
            <div v-else class="w-full h-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <!-- info -->
        <div class="flex-1 min-w-0 space-y-1">

            <!-- titulo -->
            <div class="text-base font-medium leading-6 text-gray-800 dark:text-white/90 truncate">
                {{ banner.title }}
            </div>

            <!-- link -->
            <div v-if="banner.link" class="text-sm text-blue-600 truncate max-w-full">
                <a :href="banner.link" target="_blank" rel="noopener noreferrer" class="hover:underline">
                    {{ banner.link }}
                </a>
            </div>
            <div v-else class="text-sm text-gray-400">-- sin link --</div>

            <!-- fechas del evento, si existen -->
            <div v-if="banner.dates && banner.dates.length > 0" class="flex flex-wrap gap-1.5 pt-0.5">
                <span v-for="(date, i) in banner.dates" :key="i"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-100 dark:border-blue-800">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ date }}
                </span>
            </div>

        </div>

        <!-- Acciones -->
        <div class="flex-shrink-0 flex items-center gap-2">

            <button type="button" @click="emit('status-change', banner.id)"
                :title="banner.is_active ? 'Desactivar' : 'Activar'"
                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                :class="banner.is_active ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'">
                <span
                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                    :class="banner.is_active ? 'translate-x-5' : 'translate-x-0'" />
            </button>

            <button @click="emit('edit', banner.id)" title="Editar"
                class="p-2.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-colors border border-blue-100 hover:border-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>
            </button>

            <button @click="emit('delete', banner.id)" title="Eliminar"
                class="p-2.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-colors border border-red-100 hover:border-red-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                </svg>
            </button>
        </div>
    </div>
</template>
