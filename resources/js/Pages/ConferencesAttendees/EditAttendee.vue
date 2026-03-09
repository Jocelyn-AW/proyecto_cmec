<script setup>
import { computed, onMounted, watch, ref, reactive } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3'
import { useAlert } from '@/composables/useAlert'
import { Spanish } from "flatpickr/dist/l10n/es.js";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Drawer from '@/Components/Drawer.vue';
import states from '@/composables/useStatesAndCities';

defineOptions({
    layout: AuthenticatedLayout
})

const props = defineProps({
    events: {
        type: Object,
        default: () => ({})
    },
    eventName: {
        type: String,
        default: ''
    },
    data: {
        type: Object,
        default: () => ({})
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

const { alertState, success, errorA, warning, hideAlert } = useAlert()

watch(() => props.flash, (value) => {
    if (!value) return

    if (value.success) success(value.success)
    if (value.warning) warning(value.warning)
    if (value.error) errorA(value.error)
}, {immediate: true, deep: true})

const emit = defineEmits(['close', 'success', 'error'])

const page = usePage();
const isPayed = ref(false)
const selectedEvent = ref(null)
const selectedState = ref('')
const selectedCity = ref('')
const paymentMethods =  {
    'cash': 'Efectivo',
    'debit_card': 'Tarjeta de Débito', 
    'credit_card': 'Tarjeta de Crédito', 
    'transfer': 'Transferencia', 
    'stripe': 'En línea (stripe)'
}

const flatpickrConfig = {
    locale: Spanish,
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "F j, Y",
    wrap: false,
};


const cities = computed(() => {
    return selectedState.value ? states[selectedState.value] : []
})

const errors = computed(() => page.props.errors || props.errors || {})


const price = computed(() => {    
    if (!selectedEvent.value || !createForm.person_type) return ''
    
    const priceMap = {
        'member':   selectedEvent.value.member_price,
        'guest':    selectedEvent.value.guest_price,
        'resident': selectedEvent.value.resident_price,
        'nurse': selectedEvent.value.nurse_price,
        'surgeon': selectedEvent.value.surgeon_price,
    }
    
    return priceMap[createForm.person_type] ?? createForm.price
})

// const isPayed = computed(() => {
//     console.log(props.data?.payments?.[0]?.status == 'paid');
    
//     return props.data?.payments?.[0]?.status == 'paid'
// })

const generateRandomString = (length = 5) => {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
};

const formattedDate = (originalDate) => {
    return originalDate.slice(0,10) ?? '';
}

const createForm = reactive({
    _method: 'put',
    id: null,
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
    cmec_member_id: null,
    reference: '',
    payment_method: '',
    specialty: '',
    did_attend: false,
    birth_date: '',
    special_needs: '',
})

const cleanForm = () => {
    createForm.id = null;
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
    createForm.did_attend = false;
    createForm.reference = '';
    createForm.payment_method = '';
    createForm.specialty = '';
    createForm.birth_date = '';
    createForm.special_needs = '';

    selectedEvent.value = '';
    selectedCity.value = '';
    selectedState.value = '';
}

const setDataToForm = () => {
    createForm.id = props.data.id || null;
    createForm.name = props.data.name || '';
    createForm.email = props.data.email || '';
    createForm.phone = props.data.phone || '';
    createForm.event_id = props.data.event_id || '';
    createForm.event_type = props.data.event_type || props.eventName.toLowerCase();
    createForm.state = props.data.state || '';
    createForm.city = props.data.city || '';
    createForm.person_type = props.data.person_type || '';
    createForm.folio = props.data.folio || generateRandomString();
    createForm.status = props.data.status || '';
    createForm.cmec_member_id = props.data.cmec_member_id || null;
    createForm.price = props.data.price || '';
    selectedEvent.value = props.events.find(e => e.id === createForm.event_id)
    selectedState.value = props.data.state || '';
    selectedCity.value = props.data.city || '';
    createForm.reference = props.data.payments?.[0]?.reference || '';
    createForm.payment_method = props.data.payments?.[0]?.payment_method || '';
    createForm.specialty = props.data.specialty || '';
    createForm.did_attend = props.data.did_attend === true;
    createForm.birth_date = formattedDate(props.data?.birth_date) || '';
    createForm.special_needs = props.data.special_needs || '';

    isPayed.value = props.data.payments?.[0]?.status == 'paid';
}

const submitCreate = () => {
    

    createForm.event_id = selectedEvent?.value?.id;

    switch (props.eventName.toLowerCase()) {
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

    router.post(route('attendees.update', createForm.id), createForm, {
        onSuccess: () => {
            cleanForm();
            emit('close');
        },
        onError: () => {
            //
        }
    })
}

//Watchers
watch(() => props.show, (newVal) => {
    cleanForm();
    
    if (newVal) {     
        setDataToForm();
    }
})

watch(() => createForm.person_type, (newVal) => {
    if (newVal) {
        if (newVal === 'member') {
            createForm.cmec_member_id = '';
        } else {
            createForm.cmec_member_id = null;
        }
    }
})

watch(price, (val) => {
    createForm.price = val
})

watch(selectedCity, (value) => {
    createForm.city = value
})

watch(selectedState, (value, old) => {
    if (old !== '' && old !== createForm.state) {
        selectedCity.value = ''
    }
    createForm.state = value
})
</script>
<template>
    <Drawer
        :show="show"
        size="xl"
        @close="emit('close')"
        > 
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Editar asistente</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ props.eventName }}</label>
                <select name="event_id" id="event_id" v-model="selectedEvent" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Seleccionar congreso</option>
                    <option v-for="event in events" :key="event.id" :value="event">{{ event.name || event.topic }}</option>
                </select>
                <span v-if="errors?.event_id" class="text-red-500 text-xs flex justify-end">{{ errors?.event_id }}</span>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre médico
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-gray-500/10 dark:text-gray-400">se mostrará en el diploma</span>
                </label>
                <input
                    v-model="createForm.name"
                    type="text"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
                <span v-if="errors?.name" class="text-red-500 text-xs flex justify-end">{{ errors?.name }}</span>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Especialidad</label>
                <input
                    v-model="createForm.specialty"
                    type="text"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
                <span v-if="errors?.specialty" class="text-red-500 text-xs flex justify-end">{{ errors?.specialty }}</span>
            </div>
            <div class="flex gap-2 w-full">
                <div class="flex flex-col grow">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                    <input
                        v-model="createForm.email"
                        type="email"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    <span v-if="errors?.email" class="text-red-500 text-xs flex justify-end">{{ errors?.email }}</span>
                </div>
                <div class="flex flex-col grow">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input
                        v-model="createForm.phone"
                        type="text"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    <span v-if="errors?.phone" class="text-red-500 text-xs flex justify-end">{{ errors?.phone }}</span>
                </div>
            </div>
            <div>
                <div class="flex gap-2 w-full">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de nacimiento</label>
                        <flat-pickr
                            v-model="createForm.birth_date"
                            :config="flatpickrConfig"
                            class=" grow dark:bg-dark-900 h-10 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pl-4 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                            placeholder="Selecciona una fecha"
                        />
                    </div>

                    <div class="grow">
                        <label class="block text-sm font-medium text-gray-700 mb-1">¿Requiere atención especial?
                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-gray-500/10 dark:text-gray-400">opcional</span>
                        </label>
                        <input
                            v-model="createForm.special_needs" type="text"
                            placeholder="Ej: Silla de ruedas, discapacidad"
                            class="grow w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                    </div>  
                </div>
                <div class="flex gap-9 w-full">
                    <span v-if="errors?.birth_date" class="grow text-red-500 text-xs flex justify-end">{{ errors?.birth_date }}</span>
                    <span v-if="errors?.special_needs" class="grow text-red-500 text-xs flex justify-end">{{ errors?.special_needs }}</span>
                </div>
            </div>
            <!--  -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Origen</label>
                <div class="flex gap-2 w-full">
                    <select name="state" id="state" v-model="selectedState" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Seleccionar estado</option>
                        <option v-for="state in Object.keys(states)" :key="state" :value="state">{{ state }}</option>
                    </select>

                    <select name="event_id" id="event_id" v-model="selectedCity" :disabled="!selectedState"
                        :class="!selectedState ? 'cursor-not-allowed' : ''"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Seleccionar ciudad</option>
                        <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
                    </select>
                    
                </div>
                <div class="flex gap-9 w-full">
                    <span v-if="errors?.state" class="grow text-red-500 text-xs flex justify-end">{{ errors?.state }}</span>
                    <span v-if="errors?.city" class="grow text-red-500 text-xs flex justify-end">{{ errors?.city }}</span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de participante</label>
                <select name="person_type" id="person_type" v-model="createForm.person_type" 
                :disabled="isPayed" :class="isPayed ? 'disabled' :''"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="" selected>Seleccionar tipo</option>
                    <option value="member">Miembro CMEC</option>
                    <option value="surgeon">Residente de Cirugía</option>
                    <option value="nurse">Estudiante o Enferemero</option>
                    <option value="resident">Residente o Medico General</option>
                    <option value="guest">Especialista (no miembro)</option>
                </select>
                <span v-if="errors?.person_type" class="text-red-500 text-xs flex justify-end">{{ errors?.person_type }}</span>
            </div>
            <div v-if="createForm.person_type == 'member'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Folio de miembro CMEC</label>
                <input
                    v-model="createForm.cmec_member_id"
                    type="text"
                    class="grow w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                <span v-if="errors?.cmec_member_id" class="text-red-500 text-xs flex justify-end">{{ errors?.cmec_member_id }}</span>
            </div>
            <hr class="my-2">
            <div >
                <label class="block text-sm font-medium text-gray-700 mb-1">Detalles de Pago</label>
                <div class="flex gap-2 w-full">
                    <input
                        :value="price"
                        :disabled="isPayed" :class="isPayed ? 'disabled' :''"
                        type="number" min="0" step="0.01"
                        class="grow rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Cantidad a pagar"
                    />
                    <select name="method" id="method" v-model="createForm.payment_method"
                    :disabled="isPayed" :class="isPayed ? 'disabled' :''"
                    class="rounded-lg grow border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="" selected>Seleccionar método de pago</option>
                        <option v-for="(method, key) in paymentMethods" :key="key" :value="key">{{ method }}</option>
                    </select>
                </div>
                <div class="flex gap-9 w-full">
                    <span v-if="errors?.price" class="grow text-red-500 text-xs flex justify-end">{{ errors?.price }}</span>
                    <span v-if="errors?.payment_method" class="grow text-red-500 text-xs flex justify-end">{{ errors?.payment_method }}</span>
                </div>
                <div class="flex gap-2 w-full mt-3">
                    <select name="status" id="status" v-model="createForm.status" 
                    :disabled="isPayed" :class="isPayed ? 'disabled' :''"
                    class="rounded-lg grow border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="" selected>Seleccionar estatus de pago</option>
                        <option value="paid">Pagado</option>
                        <option value="pending">Pendiente</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                </div>
                <span v-if="errors?.status" class="grow text-red-500 text-xs flex justify-end">{{ errors?.status }}</span>
                <div class="flex mt-3" 
                    v-if="createForm.payment_method != 'cash' && createForm.payment_method != '' && createForm.status != 'pending' && createForm.status != ''">
                    <input
                        v-model="createForm.reference"
                        type="text"
                        :disabled="isPayed" :class="isPayed ? 'disabled' :''"
                        class="grow rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Referencia o  Numero de transaccion"
                    />
                </div>
                <span v-if="errors?.reference" class="grow text-red-500 text-xs flex justify-end">{{ errors?.reference }}</span>
            </div>

            <!-- <hr class="my-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Folio de Acceso</label>
                <input
                    v-model="createForm.folio"
                    type="text" disabled
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-100 cursor-not-allowed"
                />
            </div> -->
            
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
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                    @click="submitCreate"
                >
                    Guardar
                </button>
            </div>
        </template>
    </Drawer>
</template>