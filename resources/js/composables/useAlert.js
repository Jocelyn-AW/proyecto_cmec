import { ref } from 'vue'

const alertState = ref({
    show: false,
    message: '',
    title: '',
    type: 'success',
    duration: 0,
    buttonText: 'Aceptar',
    cancelText: null,
    onConfirm: null,
    onCancel: null
})

export function useAlert() {

    const showAlert = (message, type = 'success', options = {}) => {
        alertState.value = {
            show: true,
            message,
            type,
            title: options.title || '',
            duration: options.duration || 0,
            buttonText: options.buttonText || 'Aceptar',
            cancelText: options.cancelText || null,
            onConfirm: options.onConfirm || null,
            onCancel: options.onCancel || null
        }
    }

    const hideAlert = () => {
        alertState.value.show = false
        alertState.value.onConfirm = null
        alertState.value.onCancel = null
    }

    const success = (message, options = {}) => {
        showAlert(message, 'success', {
            title: options.title || 'Operación exitosa',
            ...options
        })
    }

    const errorA = (message, options = {}) => {
        showAlert(message, 'error', {
            title: options.title || 'Error',
            ...options
        })
    }

    const warning = (message, options = {}) => {
        showAlert(message, 'warning', {
            title: options.title || 'Advertencia',
            ...options
        })
    }

    const info = (message, options = {}) => {
        showAlert(message, 'info', {
            title: options.title || 'Información',
            ...options
        })
    }

    return {
        alertState,
        showAlert,
        hideAlert,
        success,
        errorA,
        warning,
        info
    }
}
