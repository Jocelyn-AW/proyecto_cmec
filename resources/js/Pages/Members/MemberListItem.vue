<script setup>
defineProps({
    member: { type: Object, required: true }
})

const emit = defineEmits(['edit', 'delete'])

const formatDate = (dateStr) => {
    if (!dateStr) return '—'
    const parts = dateStr.split(/[T ]/)[0].split('-')
    return new Date(parts[0], parts[1] - 1, parts[2]).toLocaleDateString('es-MX', {
        day: '2-digit', month: 'short', year: 'numeric'
    })
}

const isExpired = (dateStr) => {
    if (!dateStr) return false
    return new Date(dateStr.split(/[T ]/)[0]) < new Date()
}
</script>

<template>
    <tr>
        <!-- ID CMEC -->
        <td class="px-4 py-3 border border-gray-100 dark:border-white/[0.05]">
            <span class="font-mono text-xs text-gray-400">{{ member.cmec_member_id || '—' }}</span>
        </td>

        <!-- Nombre -->
        <td class="px-4 py-3 border border-gray-100 dark:border-white/[0.05]">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 text-xs font-bold">
                    {{ member.name.charAt(0).toUpperCase() }}{{ member.last_name.charAt(0).toUpperCase() }}
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-800 dark:text-white/90 whitespace-nowrap">
                        {{ member.name }} {{ member.last_name }}
                    </p>
                    <p v-if="member.hospital" class="text-xs text-gray-400 truncate max-w-[180px]">{{ member.hospital }}
                    </p>
                </div>
            </div>
        </td>

        <!-- Correo -->
        <td class="px-4 py-3 border border-gray-100 dark:border-white/[0.05]">
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ member.email }}</p>
        </td>

        <!-- Teléfono -->
        <td class="px-4 py-3 border border-gray-100 dark:border-white/[0.05]">
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ member.phone }}</p>
        </td>

        <!-- Ciudad -->
        <td class="px-4 py-3 border border-gray-100 dark:border-white/[0.05]">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ member.city }}{{ member.state ? ', ' + member.state : '' }}
            </p>
        </td>

        <!-- Vencimiento -->
        <td class="px-4 py-3 border border-gray-100 dark:border-white/[0.05]">
            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium" :class="isExpired(member.expiration_date)
                ? 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400'
                : 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400'">
                <span class="h-1.5 w-1.5 rounded-full"
                    :class="isExpired(member.expiration_date) ? 'bg-red-500' : 'bg-green-500'" />
                {{ formatDate(member.expiration_date) }}
            </span>
        </td>

        <!-- Acciones -->
        <td class="px-4 py-3 border border-gray-100 dark:border-white/[0.05]">
            <div class="flex items-center justify-center gap-2">
                <button @click="emit('edit', member)" title="Editar"
                    class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-colors border border-blue-100 hover:border-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                    </svg>
                </button>
                <button @click="emit('delete', member.id)" title="Eliminar"
                    class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-colors border border-red-100 hover:border-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                </button>
            </div>
        </td>
    </tr>
</template>