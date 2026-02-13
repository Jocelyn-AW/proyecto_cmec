import { ref } from 'vue'

const alertState = ref({
    show: false,
    message: '',
    title: '',
    type: 'success',
    duration: 0,
    buttonText: 'Aceptar'
})

export function useAlert() {
    const showAlert = (message, type = 'success', options = {}) => {
        alertState.value = {
            show: true,
            message,
            type,
            title: options.title || '',
            duration: options.duration || 0,
            buttonText: options.buttonText || 'Aceptar'
        }
    }

    const hideAlert = () => {
        alertState.value.show = false
    }

    // Métodos de conveniencia
    const success = (message, options = {}) => {
        showAlert(message, 'success', {
            title: options.title || 'Operación exitosa',
            buttonText: options.buttonText || 'Aceptar',
            duration: options.duration || 0
        })
    }

    const error = (message, options = {}) => {
        showAlert(message, 'error', {
            title: options.title || 'Error',
            buttonText: options.buttonText || 'Aceptar',
            duration: options.duration || 0
        })
    }

    const warning = (message, options = {}) => {
        showAlert(message, 'warning', {
            title: options.title || 'Advertencia',
            buttonText: options.buttonText || 'Aceptar',
            duration: options.duration || 0
        })
    }

    const info = (message, options = {}) => {
        showAlert(message, 'info', {
            title: options.title || 'Información',
            buttonText: options.buttonText || 'Aceptar',
            duration: options.duration || 0
        })
    }

    return {
        alertState,
        showAlert,
        hideAlert,
        success,
        error,
        warning,
        info
    }
}