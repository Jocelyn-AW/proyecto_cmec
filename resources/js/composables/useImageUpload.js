import { ref } from "vue";

/**
 * Composable reutilizable para manejar la carga y preview de imagenes.
 *
 * @param {Object} options
 * @param {number}   options.maxSizeMB  - Peso maximo permitido en MB (default: 1)
 * @param {Function} options.onError    - Callback cuando la imagen excede el limite
 */
export function useImageUpload({ maxSizeMB = 1, onError } = {}) {
    const file = ref(null);
    const preview = ref(null);

    /**
     * Maneja el evento change de un input[type="file"].
     * Valida el peso y genera un preview en base64.
     */
    const handleChange = (event) => {
        const selected = event.target.files[0];
        if (!selected) return;

        const maxBytes = maxSizeMB * 1024 * 1024;

        if (selected.size > maxBytes) {
            onError?.(`La imagen no debe superar ${maxSizeMB}MB`);
            event.target.value = "";
            return;
        }

        file.value = selected;

        const reader = new FileReader();
        reader.onload = (e) => {
            preview.value = e.target.result;
        };
        reader.readAsDataURL(selected);
    };

    /**
     * Resetea el archivo y el preview.
     */
    const reset = () => {
        file.value = null;
        preview.value = null;
    };

    return { file, preview, handleChange, reset };
}
