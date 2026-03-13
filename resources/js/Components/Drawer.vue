<script setup>
import { watch, onUnmounted } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: ''
    },
    subtitle: {
        type: String,
        default: ''
    },
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['sm', 'md', 'lg', 'xl'].includes(v)
    }
})

const emit = defineEmits(['close'])

const sizeClasses = {
    sm: 'w-80',
    md: 'w-96',
    lg: 'w-[32rem]',
    xl: 'w-[40rem]',
}

watch(() => props.show, (value) => {
    document.body.style.overflow = value ? 'hidden' : ''
})

onUnmounted(() => {
    document.body.style.overflow = ''
})
</script>

<template>
    <Teleport to="body">
        <!-- Backdrop -->
        <Transition
            enter-active-class="transition-opacity duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 bg-black/40 backdrop-blur-[2px] z-[9998]"
                @click="emit('close')"
            />
        </Transition>

        <!-- Panel -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="translate-x-full opacity-0"
            enter-to-class="translate-x-0 opacity-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="translate-x-0 opacity-100"
            leave-to-class="translate-x-full opacity-0"
        >
            <div
                v-if="show"
                :class="[
                    sizeClasses[size],
                    'fixed inset-y-0 right-0 z-[9999] flex flex-col bg-white',
                    'border-l border-gray-200/80 shadow-[-8px_0_32px_rgba(0,0,0,0.10)]'
                ]"
            >
                <!-- Header -->
                <div class="flex items-center justify-between mt-[76px] px-6 py-5 border-b border-gray-100">
                    <div class="min-w-0">
                        <p
                            v-if="subtitle"
                            class="text-[10px] font-medium tracking-widest uppercase text-gray-400 mb-0.5"
                        >
                            {{ subtitle }}
                        </p>
                        <h3 class="text-[18px] font-semibold text-gray-700 truncate">
                            {{ title }}
                        </h3>
                    </div>
                    <button
                        type="button"
                        class="ml-4 shrink-0 flex items-center justify-center w-8 h-8 rounded-lg
                            border border-gray-200/80 text-gray-400
                            hover:bg-gray-50 hover:text-gray-600 hover:border-gray-300
                            transition-all duration-150"
                        @click="emit('close')"
                        aria-label="Cerrar panel"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Content (scrolleable) -->
                <div class="flex-1 overflow-y-auto px-6 py-6 scroll-smooth">
                    <slot />
                </div>

                <!-- Footer -->
                <div
                    v-if="$slots.footer"
                    class="border-t border-gray-100 bg-gray-50/70 px-6 py-4"
                >
                    <slot name="footer" />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>