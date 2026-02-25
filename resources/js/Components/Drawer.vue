<script setup>
import { watch } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    title: {
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
                class="fixed inset-0 bg-gray-500/75 z-[9998]"
                @click="emit('close')"
            />
        </Transition>

        <!-- Panel -->
        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
        >
            <div
                v-if="show"
                :class="[sizeClasses[size], 'fixed inset-y-0 right-0 z-[9999] flex flex-col bg-white shadow-xl']"
            >
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                    <h2 class="text-base font-semibold text-gray-900">
                        {{ title }}
                    </h2>
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors"
                        @click="emit('close')"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Content (scrolleable) -->
                <div class="flex-1 overflow-y-auto px-6 py-5">
                    <slot />
                </div>

                <!-- Footer -->
                <div v-if="$slots.footer" class="border-t border-gray-200 px-6 py-4">
                    <slot name="footer" />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>