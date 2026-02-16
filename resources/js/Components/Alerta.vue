<script setup>
import { ref, watch, computed } from 'vue'

const props = defineProps({
    message: {
        type: String,
        default: ''
    },
    title: {
        type: String,
        default: ''
    },
    type: {
        type: String,
        default: 'success',
        validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
    },
    show: {
        type: Boolean,
        default: false
    },
    duration: {
        type: Number,
        default: 0 // 0 = no auto-close, requiere click en botón
    },
    buttonText: {
        type: String,
        default: 'Aceptar'
    },
    cancelText: {
        type: String,
        default: null
    },
})

const emit = defineEmits(['close', 'confirm', 'cancel'])

const visible = ref(false)
let timer = null

watch(
    () => props.show,
    (value) => {
        if (value) {
            visible.value = true
            document.body.style.overflow = 'hidden'

            // Auto-close solo si duration > 0
            if (props.duration > 0) {
                clearTimeout(timer)
                timer = setTimeout(() => {
                    close()
                }, props.duration)
            }
        } else {
            document.body.style.overflow = ''
            visible.value = false
        }
    },
    { immediate: true }
)

const close = (isConfirm = false) => {
    visible.value = false
    clearTimeout(timer)
    document.body.style.overflow = ''

    setTimeout(() => {

        if (isConfirm) {
            emit('confirm')
        } else {
            if (props.cancelText) {
                emit('cancel')
            } else {
                emit('close')
            }
        }

    }, 200)
}


const alertConfig = computed(() => {
    const configs = {
        success: {
            iconBg: 'bg-green-100',
            iconColor: 'text-green-600',
            buttonBg: 'bg-green-600 hover:bg-green-700',
            title: props.title || 'Operación exitosa',
            icon: `<svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>`
        },
        error: {
            iconBg: 'bg-red-100',
            iconColor: 'text-red-600',
            buttonBg: 'bg-red-600 hover:bg-red-700',
            title: props.title || 'Error',
            icon: `<svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>`
        },
        warning: {
            iconBg: 'bg-yellow-100',
            iconColor: 'text-yellow-600',
            buttonBg: 'bg-yellow-600 hover:bg-yellow-700',
            title: props.title || 'Advertencia',
            icon: `<svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>`
        },
        info: {
            iconBg: 'bg-blue-100',
            iconColor: 'text-blue-600',
            buttonBg: 'bg-blue-600 hover:bg-blue-700',
            title: props.title || 'Información',
            icon: `<svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>`
        }
    }

    return configs[props.type] || configs.success
})
</script>

<template>
    <Teleport to="body">
        <!-- Backdrop -->
        <Transition enter-active-class="transition-opacity duration-300 ease-out" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition-opacity duration-200 ease-in"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="visible" class="fixed inset-0 bg-gray-500/75 transition-opacity z-[9998]" @click="close">
            </div>
        </Transition>

        <!-- Dialog Container -->
        <div v-if="visible" class="fixed inset-0 z-[9999] w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <!-- Dialog Panel -->
                <Transition enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <div v-show="props.show"
                        class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">

                        <!-- Content -->
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <!-- Icon -->
                                <div :class="[
                                    alertConfig.iconBg,
                                    'mx-auto flex size-12 shrink-0 items-center justify-center rounded-full sm:mx-0 sm:size-10'
                                ]">
                                    <div :class="alertConfig.iconColor" v-html="alertConfig.icon"></div>
                                </div>

                                <!-- Text Content -->
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-base font-semibold text-gray-900">
                                        {{ alertConfig.title }}
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            {{ message }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">

                            <!-- Confirm -->
                            <button type="button" @click="$emit('confirm')"
                                :class="[alertConfig.buttonBg,
                                    'inline-flex w-full justify-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm sm:ml-3 sm:w-auto']">
                                {{ buttonText }}
                            </button>

                            <!-- Cancel -->
                            <button v-if="cancelText" type="button" @click="$emit('cancel')"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                                {{ cancelText }}
                            </button>

                        </div>


                    </div>
                </Transition>
            </div>
        </div>
    </Teleport>
</template>