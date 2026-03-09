<template>
    <div class="relative" ref="dropdownRef">

        <!-- BOTON -->
        <button
            class="flex items-center gap-2.5 rounded-xl px-2 py-1.5 transition-colors hover:bg-gray-100 dark:hover:bg-white/5 min-w-0"
            @click.prevent="toggleDropdown">
            <span class="overflow-hidden rounded-full h-8 w-8 ring-2 ring-gray-200 dark:ring-gray-700 shrink-0">
                <img :src="$page.props.auth.user.avatar_url || '/images/placeholders/user-01.jpg'" alt="User"
                    class="h-full w-full object-cover" />
            </span>
            <span class="hidden sm:block text-sm font-medium text-gray-700 dark:text-gray-300 max-w-[120px] truncate">
                {{ $page.props.auth.user.name }}
            </span>
            <ChevronDownIcon class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0"
                :class="{ 'rotate-180': dropdownOpen }" />
        </button>

        <!-- DROPDOWN -->
        <Transition enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 translate-y-1 scale-95" enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-1 scale-95">

            <div v-if="dropdownOpen"
                class="fixed right-4 mt-1 w-64 origin-top-right rounded-2xl border border-gray-100 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-dark z-50"
                :style="{ top: dropdownTop + 'px' }">

                <!-- USUARIO INFO -->
                <div class="flex items-center gap-3 px-4 py-4 border-b border-gray-100 dark:border-gray-800">
                    <span
                        class="overflow-hidden rounded-full h-10 w-10 ring-2 ring-gray-100 dark:ring-gray-700 shrink-0">
                        <img :src="$page.props.auth.user.avatar_url || '/images/placeholders/user-01.jpg'" alt="User"
                            class="h-full w-full object-cover" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">
                            {{ $page.props.auth.user.name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            {{ $page.props.auth.user.email }}
                        </p>
                    </div>
                </div>

                <!-- OPCIONES -->
                <ul class="p-2">
                    <li v-for="item in menuItems" :key="item.href">
                        <Link :href="item.href" @click="closeDropdown"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-200">
                            <component :is="item.icon" class="w-4 h-4 text-gray-400 shrink-0" />
                            {{ item.text }}
                        </Link>
                    </li>
                </ul>

                <!-- CERRAR SESION -->
                <div class="p-2 border-t border-gray-100 dark:border-gray-800">
                    <Link :href="route('logout')" method="post" @click="signOut"
                        class="flex items-center gap-3 w-full px-3 py-2 rounded-lg text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">
                        <LogoutIcon class="w-4 h-4 shrink-0" />
                        Cerrar sesión
                    </Link>
                </div>

            </div>
        </Transition>
    </div>
</template>

<script setup>
import { UserCircleIcon, ChevronDownIcon, LogoutIcon, SettingsIcon } from '@/icons'
import { ref, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'

const dropdownOpen = ref(false)
const dropdownTop = ref(0)
const dropdownRef = ref(null)

const toggleDropdown = () => {
    if (!dropdownOpen.value) {
        const rect = dropdownRef.value.getBoundingClientRect()
        dropdownTop.value = rect.bottom + 8
    }
    dropdownOpen.value = !dropdownOpen.value
}

const menuItems = [
    { href: '/profile', icon: UserCircleIcon, text: 'Editar perfil' },
    { href: '/settings', icon: SettingsIcon, text: 'Configuración' },
]

const closeDropdown = () => dropdownOpen.value = false
const signOut = () => closeDropdown()

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        closeDropdown()
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))
</script>