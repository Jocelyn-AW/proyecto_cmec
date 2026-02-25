<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3'
import { useAlert } from '@/composables/useAlert'
import { computed, onMounted, watch } from 'vue';
import Alerta from '@/Components/Alerta.vue';
import Drawer from '@/Components/Drawer.vue';
import { ref, reactive } from 'vue';

defineOptions({
    layout: AuthenticatedLayout
})

const { alertState, success, errorA, warning, hideAlert } = useAlert()

const props = defineProps({
    events: {
        type: Object,
        default: () => ({})
    },
    eventName: {
        type: String,
        default: ''
    },
    show: {
        type: Boolean,
        default: false
    },
    flash: {
        type: Object,
        default: () => ({})
    },
    auth: {
        type: Object,
        default: () => ({})
    }, 
    errors: {
        type: Object,
        default: () => ({})
    }
})

const page = usePage();
const errors = computed(() => page.props.errors || props.errors || {})
const emit = defineEmits(['close', 'success', 'error'])


onMounted(() => {
    console.log(props);
    
    
    if (page.props.success || props.flash.success) {
        success(page.props.success || props.flash.success)
    }
    if (page.props.error || props.flash.error) {
        errorA(page.props.error || props.flash.error)
    }

    if (page.props.warning || props.flash.warning) {
        warning(page.props.warning || props.flash.warning)
    }
})

const generateRandomString = (length = 5) => {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
};

const createForm = reactive({
    name: '',
    email: '',
    phone: '',
    event_id: '',
    event_type: props.eventName.toLowerCase(), // Asumiendo que eventName es algo como "Curso" o "Congreso"
    state: '',
    city: '',
    person_type: '',
    folio: generateRandomString(),
    status: '',
    price: '',
    cmec_member_id: null
})

const cleanForm = () => {
    createForm.name = '';
    createForm.email = '';
    createForm.phone = '';
    createForm.event_id = '';
    createForm.event_type = props.eventName.toLowerCase();
    createForm.state = '';
    createForm.city = '';
    createForm.person_type = '';
    createForm.folio = generateRandomString();
    createForm.status = '';
    createForm.cmec_member_id = null;
    createForm.price = '';
}

const submitCreate = () => {
    switch (createForm.event_type) {
        case 'curso':
            createForm.event_type = 'course';
            break;
        case 'congreso':
            createForm.event_type = 'conference';
            break;
        case 'webinar':
            createForm.event_type = 'webinar';
            break;
        default:
            createForm.event_type = 'course'; // Valor por defecto
    }
    router.post(route('attendees.store'), createForm, {
        onSuccess: () => {
            cleanForm();
            emit('success');
        },
        onError: () => {
            console.log('errores');
        }
    })
}

watch(() => createForm.person_type, (newVal) => {
    if (newVal) {
        if (newVal === 'member') {
            createForm.cmec_member_id = '';
        } else {
            createForm.cmec_member_id = null;
        }
    }
})


watch(() => props.show, (newVal) => {
    if (newVal) {
        cleanForm();
    }
})

</script>
<template>
    <Drawer
        :show="show"
        size="xl"
        @close="emit('close')"
        > 
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Nuevo participante</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ props.eventName }}</label>
                <select name="event_id" id="event_id" v-model="createForm.event_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Seleccionar curso</option>
                    <option v-for="key,event in events" :key="event" :value="event">{{ key }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre médico (se mostrará en el diploma)</label>
                <input
                    v-model="createForm.name"
                    type="text"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <span v-if="errors?.name" class="text-red-500 text-xs flex justify-end">{{ errors?.name }}</span>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                <input
                    v-model="createForm.email"
                    type="email"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <span v-if="errors?.email" class="text-red-500 text-xs flex justify-end">{{ errors?.email }}</span>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                <input
                    v-model="createForm.phone"
                    type="text"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <span v-if="errors?.phone" class="text-red-500 text-xs flex justify-end">{{ errors?.phone }}</span>
            </div>
            <div >
                <label class="block text-sm font-medium text-gray-700 mb-1">Origen</label>
                <div class="flex gap-2 w-full">
                    <input
                        v-model="createForm.city"
                        type="text"
                        class="grow rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Ciudad"
                    />
                    <input
                        v-model="createForm.state"
                        type="text"
                        class="grow rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Estado"
                    />
                </div>
                <span v-if="errors?.city || errors?.state" class="text-red-500 text-xs flex justify-end">{{ errors?.city || errors?.state }}</span>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de participante</label>
                <select name="person_type" id="person_type" v-model="createForm.person_type" 
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="" selected>Seleccionar tipo</option>
                    <option value="member">Miembro CMEC</option>
                    <option value="resident">Residente del colegio</option>
                    <option value="guest">No miembro (invitado)</option>
                </select>
                <span v-if="errors?.person_type" class="text-red-500 text-xs flex justify-end">{{ errors?.person_type }}</span>
            </div>
            <div v-if="createForm.person_type == 'member'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Folio de miembro CMEC</label>
                <input
                    v-model="createForm.cmec_member_id"
                    type="text"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <span v-if="errors?.cmec_member_id" class="text-red-500 text-xs flex justify-end">{{ errors?.cmec_member_id }}</span>
            </div>
            <hr class="my-2">
            <div >
                <label class="block text-sm font-medium text-gray-700 mb-1">Estatus de Pago</label>
                <div class="flex gap-2 w-full">
                    <input
                        v-model="createForm.price"
                        type="number" min="0" step="0.01"
                        class="grow rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Cantidad a pagar"
                    />
                    <select name="status" id="status" v-model="createForm.status" 
                    class="rounded-lg grow border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="" selected>Seleccionar estatus</option>
                        <option value="paid">Pagado</option>
                        <option value="pending">Pendiente</option>
                    </select>
                </div>
                <span v-if="errors?.status" class="text-red-500 text-xs flex justify-end">{{ errors?.status }}</span>
            </div>
            <hr class="my-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Folio de Acceso</label>
                <input
                    v-model="createForm.folio"
                    type="text" disabled
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-100 cursor-not-allowed"
                />
            </div>
            
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <button
                    type="button"
                    class="rounded-lg border px-4 py-2 text-sm text-gray-600 hover:bg-gray-50"
                    @click="emit('close')"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                    @click="submitCreate"
                >
                    Guardar
                </button>
            </div>
        </template>
    </Drawer>
</template>