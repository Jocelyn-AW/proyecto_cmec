import { ref, computed } from "vue";

/**
 * Composable reutilizable para manejar la carga de archivos.
 * Soporta tanto input[type="file"] como drag & drop.
 * Soporta modo simple (1 archivo) y modo multiple.
 * Funciona con imagenes, PDFs, o cualquier tipo de archivo.
 * Soporta imágenes existentes de BD mezcladas con nuevas subidas.
 *
 * @param {Object}   options
 * @param {number}   options.maxSizeMB     - Peso maximo permitido por archivo en MB (default: 1)
 * @param {number}   options.maxFiles      - Cantidad maxima de archivos en modo multiple (default: 10)
 * @param {boolean}  options.multiple      - Permitir multiples archivos (default: false)
 * @param {string[]} options.acceptedTypes - MIME types aceptados (default: [] = cualquier tipo)
 * @param {Object}   options.dimensions    - Restricciones de dimensiones para imagenes (opcional)
 * @param {number}   options.dimensions.minWidth  - Ancho minimo en px
 * @param {number}   options.dimensions.maxWidth  - Ancho maximo en px
 * @param {number}   options.dimensions.minHeight - Alto minimo en px
 * @param {number}   options.dimensions.maxHeight - Alto maximo en px
 * @param {Function} options.onError       - Callback cuando la validacion falla
 */
export function useFileUpload({
    maxSizeMB = 1,
    maxFiles = 10,
    multiple = false,
    acceptedTypes = [],
    dimensions = null,
    onError,
} = {}) {
    // --- Estado modo simple ---
    const file = ref(null);
    const preview = ref(null);

    // --- Estado modo multiple ---
    const files = ref([]);
    const previews = ref([]);

    // --- Estado para imágenes existentes de BD ---
    // Cada item: { id, url, markedForDeletion: boolean }
    const existingFiles = ref([]);
    
    // IDs de imágenes existentes marcadas para eliminar
    const deletedExistingIds = ref([]);

    // --- Computed para obtener todas las previews combinadas ---
    const allPreviews = computed(() => {
        const existing = existingFiles.value
            .filter(item => !item.markedForDeletion)
            .map(item => ({
                type: 'existing',
                id: item.id,
                url: item.url,
                preview: item.url,
            }));
        
        const newOnes = previews.value.map((preview, index) => ({
            type: 'new',
            index,
            file: files.value[index],
            preview,
        }));
        

        return [...existing, ...newOnes];
    });

    // Cantidad total de archivos (existentes activos + nuevos)
    const totalCount = computed(() => {
        const existingCount = existingFiles.value.filter(f => !f.markedForDeletion).length;
        return existingCount + files.value.length;
    });

    const isDragging = ref(false);

    /**
     * Determina si un archivo es de tipo imagen.
     */
    const isImage = (fileItem) => {
        return fileItem.type.startsWith("image/");
    };

    /**
     * Valida las dimensiones de una imagen contra los rangos definidos.
     * Retorna una Promise<boolean>.
     */
    const validateDimensions = (fileItem) => {
        if (!dimensions || !isImage(fileItem)) {
            return Promise.resolve(true);
        }

        const { minWidth, maxWidth, minHeight, maxHeight } = dimensions;

        return new Promise((resolve) => {
            const url = URL.createObjectURL(fileItem);
            const img = new Image();

            img.onload = () => {
                URL.revokeObjectURL(url);

                const errors = [];

                if (minWidth && img.width < minWidth) {
                    errors.push(`ancho minimo ${minWidth}px`);
                }
                if (maxWidth && img.width > maxWidth) {
                    errors.push(`ancho maximo ${maxWidth}px`);
                }
                if (minHeight && img.height < minHeight) {
                    errors.push(`alto minimo ${minHeight}px`);
                }
                if (maxHeight && img.height > maxHeight) {
                    errors.push(`alto maximo ${maxHeight}px`);
                }

                if (errors.length > 0) {
                    onError?.(
                        `Dimensiones incorrectas (${img.width}x${img.height}px). \n` +
                        `Requerido: ${errors.join(', ')}.`
                    );
                    resolve(false);
                    return;
                }

                resolve(true);
            };

            img.onerror = () => {
                URL.revokeObjectURL(url);
                resolve(false);
            };

            img.src = url;
        });
    };

    /**
     * Valida un archivo individual.
     * Retorna Promise<boolean>.
     */
    const validateFile = async (selected) => {
        if (!selected) return false;

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

        if (!(await validateDimensions(selected))) return false;

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
        // if (!(await validateFile(selected))) return false;

        file.value = selected;
        preview.value = await generatePreview(selected);
        return true;
    };

/**
     * Procesa multiples archivos (modo multiple).
     * Ahora considera también los archivos existentes de BD.
     */
    const processFiles = async (fileList) => {
        const incoming = Array.from(fileList);
        const slotsAvailable = maxFiles - totalCount.value;

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
            if (!await validateFile(item)) continue;
            const previewData = await generatePreview(item);
            files.value.push(item);
            previews.value.push(previewData);
        }
    };

    /**
     * Maneja el evento change de un input[type="file"].
     */
    const handleChange = async (event) => {
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
     * Elimina un archivo nuevo por indice (modo multiple).
     */
    const removeAt = (index) => {
        files.value.splice(index, 1);
        previews.value.splice(index, 1);
    };

    /**
     * Inicializa con archivos existentes de BD.
     * @param {Array} items - Array de objetos con { id, url } o solo URLs como strings
     */
    const initExisting = (items) => {
        if (!items || !Array.isArray(items)) return;
                
        existingFiles.value = items.map((item, index) => { 
            // Soporta tanto objetos { id, url } como strings directos
            if (typeof item === 'string') {
                return { id: `existing-${index}`, url: item, markedForDeletion: false };
            }
            return { 
                id: item.id ?? `existing-${index}`, 
                url: item.url ?? item.image ?? item.src,
                markedForDeletion: false 
            };
        });
        
        deletedExistingIds.value = [];
    };

    /**
     * Marca un archivo existente de BD para eliminación.
     * @param {string|number} id - ID del archivo existente
     */
    const removeExisting = (id) => {
        const item = existingFiles.value.find(f => f.id === id);
        if (item) {
            item.markedForDeletion = true;
            deletedExistingIds.value.push(id);
        }
    };

    /**
     * Restaura un archivo existente marcado para eliminación.
     * @param {string|number} id - ID del archivo existente
     */
    const restoreExisting = (id) => {
        const item = existingFiles.value.find(f => f.id === id);
        if (item) {
            item.markedForDeletion = false;
            deletedExistingIds.value = deletedExistingIds.value.filter(i => i !== id);
        }
    };

    /**
     * Elimina un item de allPreviews (maneja tanto existentes como nuevos).
     * @param {Object} item - Item de allPreviews { type, id?, index? }
     */
    const removeItem = (item) => {
        if (item.type === 'existing') {
            removeExisting(item.id);
        } else {
            removeAt(item.index);
        }
    };

    /**
     * Obtiene los datos listos para enviar al backend.
     * @returns {Object} { newFiles: File[], deletedIds: Array, existingIds: Array }
     */
    const getSubmitData = () => {
        return {
            newFiles: files.value,
            deletedIds: deletedExistingIds.value,
            existingIds: existingFiles.value
                .filter(f => !f.markedForDeletion)
                .map(f => f.id),
        };
    };

    /**
     * Resetea todo el estado.
     */
    const reset = () => {
        file.value = null;
        preview.value = null;
        files.value = [];
        previews.value = [];
        existingFiles.value = [];
        deletedExistingIds.value = [];
        isDragging.value = false;
    };

    /**
     * Resetea solo los archivos nuevos (mantiene los existentes).
     */
    const resetNew = () => {
        files.value = [];
        previews.value = [];
    };

return {
        // Estado modo simple
        file,
        preview,
        
        // Estado modo multiple
        files,
        previews,
        
        // Estado para existentes de BD
        existingFiles,
        deletedExistingIds,
        
        // Computed útiles
        allPreviews,
        totalCount,
        
        // Métodos existentes
        removeAt,
        isDragging,
        handleChange,
        handleDrop,
        handleDragEnter,
        handleDragLeave,
        reset,
        
        // Nuevos métodos para manejo de existentes
        initExisting,
        removeExisting,
        restoreExisting,
        removeItem,
        resetNew,
        getSubmitData,
    };
}

/**
 * Alias retrocompatible para los componentes que ya usan useImageUpload.
 */
export function useImageUpload(options = {}) {
    return useFileUpload({
        acceptedTypes: ["image/jpeg", "image/png", "image/jpg", "image/webp"],
        ...options,
    });
}
