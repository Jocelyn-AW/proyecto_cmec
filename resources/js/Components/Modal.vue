<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    maxWidth: {
        type: String,
        default: '2xl'
    },
    closeable: {
        type: Boolean,
        default: true
    }
})

const emit = defineEmits(['close'])

const visible = ref(false)

watch(
    () => props.show,
    (value) => {
        if (value) {
            visible.value = true
            document.body.style.overflow = 'hidden'
        } else {
            document.body.style.overflow = ''
            setTimeout(() => {
                visible.value = false
            }, 300)
        }
    },
    { immediate: true }
)

const close = () => {
    if (!props.closeable) return
    emit('close')
}

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.show) {
        close()
    }
}

onMounted(() => {
    window.addEventListener('keydown', closeOnEscape)
})

onUnmounted(() => {
    window.removeEventListener('keydown', closeOnEscape)
    document.body.style.overflow = ''
})

const maxWidthClass = computed(() => {
    return {
        sm: 'max-w-sm',
        md: 'max-w-md',
        lg: 'max-w-lg',
        xl: 'max-w-xl',
        '2xl': 'max-w-2xl',
    }[props.maxWidth]
})
</script>

<template>
    <Teleport to="body">
        <Transition enter-active-class="transition-opacity duration-300" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="visible"
                class="fixed inset-0 z-[9999] grid h-screen w-screen place-items-center bg-black/60 backdrop-blur-sm"
                @click.self="close">

                <Transition enter-active-class="transition-all duration-300"
                    enter-from-class="opacity-0 -translate-y-28 scale-90 pointer-events-none"
                    enter-to-class="opacity-100 translate-y-0 scale-100"
                    leave-active-class="transition-all duration-300"
                    leave-from-class="opacity-100 translate-y-0 scale-100"
                    leave-to-class="opacity-0 -translate-y-28 scale-90 pointer-events-none">

                    <div v-show="props.show" class="relative m-4 p-4 w-full rounded-lg bg-white shadow-sm"
                        :class="maxWidthClass">

                        <div v-if="$slots.title" class="flex shrink-0 items-center justify-between pb-4">
                            <h3 class="text-xl font-medium text-slate-800">
                                <slot name="title" />
                            </h3>

                            <button v-if="closeable" @click="close"
                                class="text-slate-400 hover:text-slate-600 text-2xl leading-none px-2">
                                &times;
                            </button>
                        </div>

                        <div class="relative py-4 leading-normal text-slate-600 font-light">
                            <slot />
                        </div>

                        <div v-if="$slots.footer" class="flex shrink-0 flex-wrap items-center pt-4 justify-end gap-2">
                            <slot name="footer" />
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>