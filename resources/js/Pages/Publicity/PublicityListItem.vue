<script setup>
defineProps({
    post: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['edit', 'delete'])
</script>

<template>
    <div
        class="bg-white dark:bg-white/[0.03] border border-gray-200 dark:border-gray-800 rounded-2xl p-6 flex items-center gap-4 hover:shadow-sm transition-shadow">
        <!-- miniatura -->
        <div class="w-28 h-20 flex-shrink-0 overflow-hidden rounded">
            <img v-if="post.image" :src="post.image" class="w-full h-full object-cover"
                :alt="post.name || 'Publicidad'">
        </div>

        <!-- info -->
        <div class="flex-1 min-w-0">
            <div class="text-base font-medium leading-6 text-gray-800 dark:text-white/90 truncate">
                {{ post.name }}
            </div>
            <div v-if="post.link" class="text-sm text-blue-600 truncate max-w-full">
                <a :href="post.link" target="_blank" rel="noopener noreferrer" class="hover:underline">
                    {{ post.link }}
                </a>
            </div>
            <div v-else class="text-sm text-gray-400">— sin link —</div>
        </div>

        <!-- acciones -->
        <div class="flex-shrink-0 flex items-center gap-2">
            <button @click="emit('edit', post.id)" title="Editar"
                class="p-2.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-colors border border-blue-100 hover:border-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>
            </button>

            <button @click="emit('delete', post.id)" title="Eliminar"
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