<script setup>
import Modal from '@/Components/Modal.vue';
import Dropzone from '@/Components/Dropzone.vue';
import { useFileUpload } from '@/composables/useImageDropped';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    maxWidth: {
        type: String,
        default: 'xl'
    },
    paymentDetails: {
        type: Object,
        default: () => ({})
    }
})

const emit = defineEmits(['close'])

const formatDate =  (originalDate) => {
    let date = new Date(originalDate);
    let options = { day: '2-digit', month: '2-digit', year: 'numeric' };
    return new Intl.DateTimeFormat('es-MX', options).format(date);
}

const readableMethod =  (paymentMethod) => {
    let readable = '';
    switch (paymentMethod) {
        case 'cash':
            readable = 'Efectivo';
            break;
        case 'transfer':
            readable = 'Transferencia';
            break;
        case 'debit_card':
            readable = 'Tarjeta de Débito';
            break;
        case 'credit_card':
            readable = 'Tarjeta de Crédito';
            break;
        case 'stripe':
            readable = 'En línea (stripe)';
            break;
        default:
            break;
    }
    return readable;
}

const close = () => {
    emit('close')
}

</script>
<template>
    <Modal :show="show" @close="close" :max-width="maxWidth">
        <template #title>
            <p class="text-lg">Detalles del pago</p>
        </template>
        <div class="grid grid-cols-2 " v-if="paymentDetails">
            <p>Método de pago</p>
            <p> {{ readableMethod(paymentDetails.payment_method) }} </p>

            <p>Referencia</p>
            <p>
                {{ paymentDetails.reference == '' ? 'No aplica' : '' }}
                {{ paymentDetails.reference }}
            </p>

            <p>Cantidad pagada</p>
            <p> {{ paymentDetails.amount }} </p>

            <p>Fecha de Pago</p>
            <p> {{ formatDate(paymentDetails.created_at) }} </p>
        </div>
        <div v-else>
            No hay pagos relacionados
        </div>

        <template #footer>
            <button @click="close"
                class="rounded-md bg-green-600 py-2 px-4 text-sm text-white transition-all shadow-md hover:bg-green-700 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                >
                Aceptar
            </button>
        </template>
    </Modal>

</template>