<script setup>
import { computed } from 'vue'

const props = defineProps({
    item: { type: Object, required: true }
})

// 🔥 Mapa centralizado de tipos de evento
const eventTypeMap = {
    conference: {
        label: 'Congreso',
        class: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-400'
    },
    course: {
        label: 'Curso',
        class: 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400'
    },
    webinar: {
        label: 'Webinar',
        class: 'bg-pink-100 text-pink-700 dark:bg-pink-500/15 dark:text-pink-400'
    },
    pre_conference: {
        label: 'Pre-Congreso',
        class: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-400'
    },
    trans_conference: {
        label: 'Trans-Congreso',
        class: 'bg-orange-100 text-orange-700 dark:bg-orange-500/15 dark:text-orange-400'
    },
    academic_session: {
        label: 'Sesión Académica',
        class: 'bg-purple-100 text-purple-700 dark:bg-purple-500/15 dark:text-purple-400'
    }
}

const eventTypeLabel = (type) => {
    return eventTypeMap[type]?.label || 'Evento'
}

const eventTypeStyle = (type) => {
    return eventTypeMap[type]?.class || 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'
}

const formatDate = (dateStr) => {
    if (!dateStr) return '—'
    const parts = dateStr.split(/[T ]/)[0].split('-')
    return new Date(parts[0], parts[1] - 1, parts[2]).toLocaleDateString('es-MX', {
        day: '2-digit', month: 'short', year: 'numeric'
    })
}

const firstSessionDate = computed(() => {
    const sessions = props.item.event?.sessions
    if (!sessions?.length) return null
    return [...sessions].sort((a, b) => new Date(a.date) - new Date(b.date))[0]?.date
})
</script>

<template>
    <tr class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
        <td class="px-4 py-4 border border-gray-100 dark:border-white/[0.05]">
            <div class="flex items-center gap-4">
                <div
                    class="h-12 w-16 shrink-0 overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                    <img v-if="item.event?.cover_url" :src="item.event.cover_url" class="h-full w-full object-cover" />
                    <div v-else class="flex h-full w-full items-center justify-center text-gray-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="max-w-[300px]">
                    <span :class="eventTypeStyle(item.event_type)"
                        class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider mb-1">
                        {{ eventTypeLabel(item.event_type) }}
                    </span>
                    <p class="text-sm font-semibold text-gray-800 dark:text-white/90 truncate"
                        :title="item.event?.topic">
                        {{ item.event?.topic || item.event?.name || '—' }}
                    </p>
                </div>
            </div>
        </td>

        <td class="px-4 py-4 border border-gray-100 dark:border-white/[0.05]">
            <div class="flex flex-col">
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ formatDate(firstSessionDate)
                    }}</span>
                <span class="text-[10px] text-gray-400 uppercase">Primera Fecha</span>
            </div>
        </td>

        <td class="px-4 py-4 border border-gray-100 dark:border-white/[0.05]">
            <div class="flex flex-col gap-1">
                <span class="text-sm font-mono font-bold text-gray-800 dark:text-white/80">
                    ${{ item.price || '0.00' }}
                </span>
                {{ console.log(item) }}
                <span class="inline-flex items-center w-fit gap-1 rounded-full px-2 py-0.5 text-[10px] font-medium"
                    :class="{
                        'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400': item.status === 'paid',
                        'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400': item.status === 'pending',
                        'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400': item.status === 'free',
                        'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400': item.status === 'cancelled'
                    }">
                    {{
                        item.status === 'paid' ? 'Pagado' :
                            item.status === 'pending' ? 'Pendiente' :
                                item.status === 'free' ? 'Gratis' :
                                    item.status === 'cancelled' ? 'Cancelado' :
                                        'Desconocido'
                    }}
                </span>
            </div>
        </td>

        <td class="px-4 py-4 border border-gray-100 dark:border-white/[0.05]">
            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
                :class="item.did_attend ? 'bg-brand-100 text-brand-700 dark:bg-brand-500/10 dark:text-brand-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'">
                <span class="h-1.5 w-1.5 rounded-full" :class="item.did_attend ? 'bg-brand-500' : 'bg-gray-400'" />
                {{ item.did_attend ? 'Asistió' : 'No asistió' }}
            </span>
        </td>

        <td class="px-4 py-4 border border-gray-100 dark:border-white/[0.05] text-right">
            <a v-if="item.diploma_url" :href="item.diploma_url" target="_blank"
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-brand-50 hover:text-brand-600 hover:border-brand-200 transition-all shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4.26 10.147L12 15l7.74-4.853M12 3v12m0 0l-3-3m3 3l3-3" />
                </svg>
                Diploma
            </a>
            <span v-else class="text-xs text-gray-400 italic">No disponible</span>
        </td>
    </tr>
</template>