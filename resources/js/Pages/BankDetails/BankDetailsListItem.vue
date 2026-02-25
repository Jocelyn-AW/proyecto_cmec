<script setup>
const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    isLast: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['edit', 'delete'])

const getBankColor = (bank) => {
    const map = {
        'bbva':               { bg: 'bg-blue-100 dark:bg-blue-500/20',    text: 'text-blue-700 dark:text-blue-300' },
        'invex':              { bg: 'bg-blue-100 dark:bg-blue-500/20',    text: 'text-blue-700 dark:text-blue-300' },
        'intercam':           { bg: 'bg-blue-100 dark:bg-blue-500/20',    text: 'text-blue-700 dark:text-blue-300' },
        'sabadell':           { bg: 'bg-sky-100 dark:bg-sky-500/20',      text: 'text-sky-700 dark:text-sky-300' },
        've por más (bx+)':   { bg: 'bg-sky-100 dark:bg-sky-500/20',      text: 'text-sky-700 dark:text-sky-300' },

        'banamex (citibanamex)': { bg: 'bg-red-100 dark:bg-red-500/20',   text: 'text-red-700 dark:text-red-300' },
        'banamex':            { bg: 'bg-red-100 dark:bg-red-500/20',      text: 'text-red-700 dark:text-red-300' },
        'citibanamex':        { bg: 'bg-red-100 dark:bg-red-500/20',      text: 'text-red-700 dark:text-red-300' },
        'santander':          { bg: 'bg-red-100 dark:bg-red-500/20',      text: 'text-red-700 dark:text-red-300' },
        'hsbc':               { bg: 'bg-rose-100 dark:bg-rose-500/20',    text: 'text-rose-700 dark:text-rose-300' },
        'scotiabank':         { bg: 'bg-red-100 dark:bg-red-500/20',      text: 'text-red-700 dark:text-red-300' },
        'famsa':              { bg: 'bg-rose-100 dark:bg-rose-500/20',    text: 'text-rose-700 dark:text-rose-300' },

        'banorte':            { bg: 'bg-orange-100 dark:bg-orange-500/20', text: 'text-orange-700 dark:text-orange-300' },
        'azteca':             { bg: 'bg-orange-100 dark:bg-orange-500/20', text: 'text-orange-700 dark:text-orange-300' },
        'consubanco':         { bg: 'bg-amber-100 dark:bg-amber-500/20',  text: 'text-amber-700 dark:text-amber-300' },

        'inbursa':            { bg: 'bg-yellow-100 dark:bg-yellow-500/20', text: 'text-yellow-700 dark:text-yellow-300' },
        'monexcb':            { bg: 'bg-yellow-100 dark:bg-yellow-500/20', text: 'text-yellow-700 dark:text-yellow-300' },

        'afirme':             { bg: 'bg-green-100 dark:bg-green-500/20',  text: 'text-green-700 dark:text-green-300' },
        'bajío':              { bg: 'bg-green-100 dark:bg-green-500/20',  text: 'text-green-700 dark:text-green-300' },
        'banbajío':           { bg: 'bg-green-100 dark:bg-green-500/20',  text: 'text-green-700 dark:text-green-300' },
        'compartamos':        { bg: 'bg-emerald-100 dark:bg-emerald-500/20', text: 'text-emerald-700 dark:text-emerald-300' },

        'banregio':           { bg: 'bg-purple-100 dark:bg-purple-500/20', text: 'text-purple-700 dark:text-purple-300' },
        'multiva':            { bg: 'bg-violet-100 dark:bg-violet-500/20', text: 'text-violet-700 dark:text-violet-300' },
        'mifel':              { bg: 'bg-indigo-100 dark:bg-indigo-500/20', text: 'text-indigo-700 dark:text-indigo-300' },
        'ixe':                { bg: 'bg-indigo-100 dark:bg-indigo-500/20', text: 'text-indigo-700 dark:text-indigo-300' },
    }
    return map[bank?.toLowerCase()] ?? { bg: 'bg-gray-100 dark:bg-gray-700', text: 'text-gray-600 dark:text-gray-300' }
}

const getBankInitials = (bank) => {
    if (!bank) return '?'
    const words = bank.trim().split(' ')
    if (words.length >= 2) return (words[0][0] + words[1][0]).toUpperCase()
    return bank.slice(0, 2).toUpperCase()
}

const maskAccount = (value) => {
    if (!value) return null
    const str = String(value)
    if (str.length <= 4) return str
    return '•••• ' + str.slice(-4)
}

const maskClabe = (value) => {
    if (!value) return null
    const str = String(value)
    if (str.length < 8) return str
    return str.slice(0, 3) + ' ' + '•'.repeat(str.length - 7) + ' ' + str.slice(-4)
}
</script>

<template>
    <!-- fila de datos con flex (no puede tener hijos porque si no no se sobreponen) -->
    <div class="flex items-start gap-4 px-6 py-5 transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]"
        :class="!isLast ? 'border-b border-gray-100 dark:border-gray-800' : ''">
        <!-- icono banco -->
        <div class="mt-0.5 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-xs font-bold"
            :class="[getBankColor(item.bank).bg, getBankColor(item.bank).text]">
            {{ getBankInitials(item.bank) }}
        </div>

        <!-- datos -->
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5 mb-1.5">
                <span class="text-sm font-semibold text-gray-800 dark:text-white/90">
                    {{ item.bank }}
                </span>
                <span v-if="item.beneficiary" class="text-xs text-gray-500 dark:text-gray-400">
                    · {{ item.beneficiary }}
                </span>
            </div>

            <div class="flex flex-wrap gap-x-5 gap-y-1 mb-1.5">
                <div v-if="item.account_number" class="flex items-center gap-1.5">
                    <span class="text-xs text-gray-400 dark:text-gray-500">Cuenta</span>
                    <span class="text-xs font-mono font-medium text-gray-700 dark:text-gray-300 tracking-widest">
                        {{ maskAccount(item.account_number) }}
                    </span>
                </div>
                <div v-if="item.clabe_number" class="flex items-center gap-1.5">
                    <span class="text-xs text-gray-400 dark:text-gray-500">CLABE</span>
                    <span class="text-xs font-mono font-medium text-gray-700 dark:text-gray-300 tracking-widest">
                        {{ maskClabe(item.clabe_number) }}
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap gap-x-4 gap-y-1">
                <div v-if="item.reference" class="flex items-center gap-1">
                    <span class="text-xs text-gray-400 dark:text-gray-500">Ref.</span>
                    <span class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        {{ item.reference }}
                    </span>
                </div>
                <div v-if="item.subsidiary" class="flex items-center gap-1">
                    <span class="text-xs text-gray-400 dark:text-gray-500">Sucursal</span>
                    <span class="text-xs text-gray-600 dark:text-gray-400">
                        {{ item.subsidiary }}
                    </span>
                </div>
            </div>
        </div>

        <!-- acciones -->
        <div class="flex flex-col items-end gap-2 shrink-0 ml-2">
            <div class="flex items-center gap-2">
                <button @click="emit('edit', item)" title="Editar"
                    class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline transition-colors">
                    Editar
                </button>
                <span class="text-gray-300 dark:text-gray-700 select-none">|</span>
                <button @click="emit('delete', item.id)" title="Eliminar"
                    class="text-xs font-medium text-red-500 dark:text-red-400 hover:underline transition-colors">
                    Eliminar
                </button>
            </div>

            <div v-if="item.updated_by" class="flex items-center gap-1 mt-auto">
                <p
                    class="inline-flex shrink-0 items-center justify-center rounded-full text-gray-500 text-xs leading-none">
                    Último editor:
                </p>
                <div
                    class="inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs font-semibold leading-none">
                    {{ item.updated_by.name?.charAt(0).toUpperCase() }}
                </div>
                <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                    {{ item.updated_by.name }}
                </span>
            </div>
        </div>
    </div>
</template>
