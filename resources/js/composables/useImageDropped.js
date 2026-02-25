import { ref } from "vue";

/**
 * Composable reutilizable para manejar la carga de archivos.
 * Soporta tanto input[type="file"] como drag & drop.
 * Soporta modo simple (1 archivo) y modo multiple.
 * Funciona con imagenes, PDFs, o cualquier tipo de archivo.
 *
 * @param {Object}   options
 * @param {number}   options.maxSizeMB     - Peso maximo permitido por archivo en MB (default: 1)
 * @param {number}   options.maxFiles      - Cantidad maxima de archivos en modo multiple (default: 10)
 * @param {boolean}  options.multiple      - Permitir multiples archivos (default: false)
 * @param {string[]} options.acceptedTypes - MIME types aceptados (default: [] = cualquier tipo)
 * @param {Function} options.onError       - Callback cuando la validacion falla
 */
export function useFileUpload({
    maxSizeMB = 1,
    maxFiles = 10,
    multiple = false,
    acceptedTypes = [],
    onError,
} = {}) {
    // --- Estado modo simple ---
    const file = ref(null);
    const preview = ref(null);

    // --- Estado modo multiple ---
    const files = ref([]);
    const previews = ref([]);

    const isDragging = ref(false);

    /**
     * Determina si un archivo es de tipo imagen.
     */
    const isImage = (fileItem) => {
        return fileItem.type.startsWith("image/");
    };

    /**
     * Valida un archivo individual.
     * Retorna true si es valido, false si no.
     */
    const validateFile = (selected) => {
        if (!selected) return false;

        console.log('validando tipo:', selected.type, 'aceptados:', acceptedTypes)

        if (
            acceptedTypes.length > 0 &&
            !acceptedTypes.includes(selected.type)
        ) {
            const labels = acceptedTypes.map((t) =>
                t.split("/")[1].toUpperCase(),
            );
            onError?.(`Formato no permitido. Usa: ${labels.join(", ")}`);
            return false;
        }

        const maxBytes = maxSizeMB * 1024 * 1024;
        if (selected.size > maxBytes) {
            onError?.(`El archivo no debe superar ${maxSizeMB}MB`);
            return false;
        }

        return true;
    };

    /**
     * Genera un preview para un archivo.
     * Para imagenes retorna base64, para otros archivos retorna un objeto con metadata.
     */
    const generatePreview = (fileItem) => {
        if (isImage(fileItem)) {
            return new Promise((resolve) => {
                const reader = new FileReader();
                reader.onload = (e) => resolve(e.target.result);
                reader.readAsDataURL(fileItem);
            });
        }

        // Para archivos no-imagen, retornamos metadata en vez de base64
        return Promise.resolve({
            isFile: true,
            name: fileItem.name,
            size: fileItem.size,
            type: fileItem.type,
        });
    };

    /**
     * Procesa un solo archivo (modo simple).
     */
    const processFile = async (selected) => {
        if (!validateFile(selected)) return false;

        file.value = selected;
        preview.value = await generatePreview(selected);
        return true;
    };

    /**
     * Procesa multiples archivos (modo multiple).
     * Los agrega a la lista existente hasta alcanzar maxFiles.
     */
    const processFiles = async (fileList) => {
        const incoming = Array.from(fileList);
        const slotsAvailable = maxFiles - files.value.length;

        if (slotsAvailable <= 0) {
            onError?.(`Maximo ${maxFiles} archivos permitidos`);
            return;
        }

        const toProcess = incoming.slice(0, slotsAvailable);

        if (incoming.length > slotsAvailable) {
            onError?.(
                `Solo se agregaron ${slotsAvailable} de ${incoming.length} archivos (maximo ${maxFiles})`,
            );
        }

        for (const item of toProcess) {
            if (!validateFile(item)) continue;
            const previewData = await generatePreview(item);
            files.value.push(item);
            previews.value.push(previewData);
        }
    };

    /**
     * Maneja el evento change de un input[type="file"].
     */
    const handleChange = async (event) => {
        console.log('handleChange llamado', event.target.files)
        if (multiple) {
            await processFiles(event.target.files);
            event.target.value = "";
        } else {
            const selected = event.target.files[0];
            if (!(await processFile(selected))) {
                event.target.value = "";
            }
        }
    };

    /**
     * Maneja el evento drop de un area de drag & drop.
     */
    const handleDrop = async (event) => {
        isDragging.value = false;
        if (multiple) {
            await processFiles(event.dataTransfer?.files || []);
        } else {
            const selected = event.dataTransfer?.files[0];
            await processFile(selected);
        }
    };

    const handleDragEnter = () => {
        isDragging.value = true;
    };
    const handleDragLeave = () => {
        isDragging.value = false;
    };

    /**
     * Elimina un archivo por indice (modo multiple).
     */
    const removeAt = (index) => {
        files.value.splice(index, 1);
        previews.value.splice(index, 1);
    };

    /**
     * Resetea todo el estado.
     */
    const reset = () => {
        file.value = null;
        preview.value = null;
        files.value = [];
        previews.value = [];
        isDragging.value = false;
    };

    return {
        // Modo simple
        file,
        preview,
        // Modo multiple
        files,
        previews,
        removeAt,
        // Compartidos
        isDragging,
        handleChange,
        handleDrop,
        handleDragEnter,
        handleDragLeave,
        reset,
    };
}

/**
 * Alias retrocompatible para los componentes que ya usan useImageUpload.
 * Aplica acceptedTypes de imagenes por defecto.
 */
export function useImageUpload(options = {}) {
    return useFileUpload({
        acceptedTypes: ["image/jpeg", "image/png", "image/jpg", "image/webp"],
        ...options,
    });
}
