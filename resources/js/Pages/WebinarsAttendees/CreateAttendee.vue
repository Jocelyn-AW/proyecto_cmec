<script setup>
import { router, usePage } from '@inertiajs/vue3'
import { useAlert } from '@/composables/useAlert'
import { computed, watch } from 'vue';
import Drawer from '@/Components/Drawer.vue';
import { ref, reactive } from 'vue';
import states from '@/composables/useStatesAndCities';

const { warning } = useAlert()

const selectedState = ref('')
const selectedCity = ref('')
const selectedEvent = ref(null)

const cities = computed(() => {
    return selectedState.value ? states[selectedState.value] : []
})

const props = defineProps({
    events: {
        type: Array,
        default: () => []
    },
    eventName: {
        type: String,
        default: ''
    },
    show: {
        type: Boolean,
        default: false
    },
    errors: {
        type: Object,
        default: () => ({})
    }
})

const page = usePage();
const errors = computed(() => page.props.errors || props.errors || {})
const emit = defineEmits(['close', 'success', 'error'])

// precio segun evento y tipo de participante (solo lectura)
const price = computed(() => {
    if (!selectedEvent.value || !createForm.person_type) return ''

    const priceMap = {
        'member': selectedEvent.value.member_price,
        'guest': selectedEvent.value.guest_price,
        'resident': selectedEvent.value.resident_price,
    }

    return priceMap[createForm.person_type] ?? ''
})

const generateRandomString = (length = 5) => {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
};

const paymentMethods = {
    'cash': 'Efectivo',
    'debit_card': 'Tarjeta de Débito',
    'credit_card': 'Tarjeta de Crédito',
    'transfer': 'Transferencia',
    'stripe': 'En línea (stripe)'
}

const createForm = reactive({
    name: '',
    email: '',
    phone: '',
    event_id: '',
    event_type: 'webinar',
    state: '',
    city: '',
    person_type: '',
    folio: generateRandomString(),
    status: '',
    price: '',
    cmec_member_id: null,
    specialty: '',
    birth_date: '',
    special_needs: '',
    payment_method: '',
    reference: '',
})

const cleanForm = () => {
    createForm.name = '';
    createForm.email = '';
    createForm.phone = '';
    createForm.event_id = '';
    createForm.event_type = 'webinar';
    createForm.state = '';
    createForm.city = '';
    createForm.person_type = '';
    createForm.folio = generateRandomString();
    createForm.status = '';
    createForm.cmec_member_id = null;
    createForm.price = '';
    createForm.specialty = '';
    /* createForm.birth_date = '';
    createForm.special_needs = ''; */
    createForm.payment_method = '';
    createForm.reference = '';
    selectedEvent.value = null;
    selectedState.value = '';
    selectedCity.value = '';
}

const submitCreate = () => {
    createForm.event_id = selectedEvent?.value?.id;
    createForm.event_type = 'webinar';
    createForm.price = price.value;

    router.post(route('attendees.store'), createForm, {
        onSuccess: () => {
            cleanForm();
            emit('success');
        },
        onError: () => {
            //
        }
    })
}

// sync precio con el computed (solo lectura)
watch(price, (val) => {
    createForm.price = val
})

watch(() => createForm.person_type, (newVal) => {
    if (newVal === 'member') {
        createForm.cmec_member_id = '';
    } else {
        createForm.cmec_member_id = null;
    }
})

watch(() => props.show, (newVal) => {
    if (newVal) {
        cleanForm();
    }
})

watch(selectedState, (value) => {
    selectedCity.value = ''
    createForm.state = value
})

watch(selectedCity, (value) => {
    createForm.city = value
})
</script>

<template>
    <Drawer :show="show" size="xl" @close="emit('close')">
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Nuevo participante</h3>

            <!-- EVENTO -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ props.eventName }}</label>
                <select v-model="selectedEvent"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option :value="null">Seleccionar webinar</option>
                    <option v-for="event in events" :key="event.id" :value="event">{{ event.topic }}</option>
                </select>
                <span v-if="errors?.event_id" class="text-red-500 text-xs flex justify-end">{{ errors?.event_id
                    }}</span>
            </div>

            <!-- NOMBRE -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre médico (se mostrará en el
                    diploma)</label>
                <input v-model="createForm.name" type="text"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                <span v-if="errors?.name" class="text-red-500 text-xs flex justify-end">{{ errors?.name }}</span>
            </div>

            <!-- ESPECIALIDAD -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Especialidad</label>
                <input v-model="createForm.specialty" type="text"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                <span v-if="errors?.specialty" class="text-red-500 text-xs flex justify-end">{{ errors?.specialty
                    }}</span>
            </div>

            <!-- EMAIL + TELÉFONO -->
            <div class="flex gap-2 w-full">
                <div class="flex flex-col grow">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                    <input v-model="createForm.email" type="email"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <span v-if="errors?.email" class="text-red-500 text-xs flex justify-end">{{ errors?.email }}</span>
                </div>
                <div class="flex flex-col grow">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input v-model="createForm.phone" type="text"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <span v-if="errors?.phone" class="text-red-500 text-xs flex justify-end">{{ errors?.phone }}</span>
                </div>
            </div>

            <!-- FECHA DE NACIMIENTO + NECESIDADES ESPECIALES -->
            <!-- <div class="flex gap-2 w-full">
                <div class="flex flex-col grow">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha de nacimiento
                        <span class="text-gray-400 font-normal">(opcional)</span>
                    </label>
                    <input v-model="createForm.birth_date" type="date"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <span v-if="errors?.birth_date" class="text-red-500 text-xs flex justify-end">{{ errors?.birth_date
                        }}</span>
                </div>
                <div class="flex flex-col grow">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Necesidades especiales
                        <span class="text-gray-400 font-normal">(opcional)</span>
                    </label>
                    <input v-model="createForm.special_needs" type="text" placeholder="Ej. silla de ruedas"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <span v-if="errors?.special_needs" class="text-red-500 text-xs flex justify-end">{{
                        errors?.special_needs }}</span>
                </div>
            </div> -->

            <!-- ORIGEN -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Origen</label>
                <div class="flex gap-2 w-full">
                    <select v-model="selectedState"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccionar estado</option>
                        <option v-for="state in Object.keys(states)" :key="state" :value="state">{{ state }}</option>
                    </select>
                    <select v-model="selectedCity" :disabled="!selectedState"
                        :class="!selectedState ? 'cursor-not-allowed opacity-60' : ''"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccionar ciudad</option>
                        <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
                    </select>
                </div>
                <span v-if="errors?.city || errors?.state" class="text-red-500 text-xs flex justify-end">{{
                    errors?.city || errors?.state }}</span>
            </div>

            <!-- TIPO DE PARTICIPANTE -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de participante</label>
                <select v-model="createForm.person_type"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Seleccionar tipo</option>
                    <option value="member">Miembro CMEC</option>
                    <option value="resident">Residente</option>
                    <option value="guest">No miembro (invitado)</option>
                </select>
                <span v-if="errors?.person_type" class="text-red-500 text-xs flex justify-end">{{ errors?.person_type
                    }}</span>
            </div>

            <!-- FOLIO CMEC -->
            <div v-if="createForm.person_type === 'member'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Folio de miembro CMEC</label>
                <input v-model="createForm.cmec_member_id" type="text"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                <span v-if="errors?.cmec_member_id" class="text-red-500 text-xs flex justify-end">{{
                    errors?.cmec_member_id }}</span>
            </div>

            <hr class="my-2">

            <!-- DETALLES DE PAGO -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Detalles de Pago</label>

                <!-- PRECIO + MÉTODO -->
                <div class="flex gap-2 w-full">
                    <!-- lectura precio calculado por webinar + tipo de participante -->
                    <div class="relative grow">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                        <input :value="price" type="number" readonly disabled
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 pl-7 pr-3 py-2 text-sm text-gray-500 cursor-not-allowed"
                            placeholder="Selecciona webinar y tipo" />
                    </div>
                    <select v-model="createForm.payment_method"
                        class="rounded-lg grow border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccionar método de pago</option>
                        <option v-for="(method, key) in paymentMethods" :key="key" :value="key">{{ method }}</option>
                    </select>
                </div>
                <p v-if="!selectedEvent || !createForm.person_type" class="text-xs text-gray-400 mt-1">
                    El precio se calculará al seleccionar el webinar y tipo de participante.
                </p>
                <div class="flex gap-9 w-full">
                    <span v-if="errors?.price" class="grow text-red-500 text-xs flex justify-end">{{ errors?.price
                        }}</span>
                    <span v-if="errors?.payment_method" class="grow text-red-500 text-xs flex justify-end">{{
                        errors?.payment_method }}</span>
                </div>

                <!-- ESTATUS -->
                <div class="flex gap-2 w-full mt-3">
                    <select v-model="createForm.status"
                        class="rounded-lg grow border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccionar estatus de pago</option>
                        <option value="paid">Pagado</option>
                        <option value="pending">Pendiente</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                </div>
                <span v-if="errors?.status" class="text-red-500 text-xs flex justify-end">{{ errors?.status }}</span>

                <!-- REFERENCIA-->
                <div class="flex mt-3"
                    v-if="createForm.payment_method !== 'cash' && createForm.payment_method !== '' && createForm.status !== 'pending' && createForm.status !== ''">
                    <input v-model="createForm.reference" type="text"
                        class="grow rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Referencia o número de transacción" />
                </div>
                <span v-if="errors?.reference" class="text-red-500 text-xs flex justify-end">{{ errors?.reference
                    }}</span>
            </div>

            <hr class="my-2">

            <!-- FOLIO DE ACCESO -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Folio de Acceso</label>
                <input v-model="createForm.folio" type="text" disabled
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-gray-100 cursor-not-allowed" />
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <button type="button" class="rounded-lg border px-4 py-2 text-sm text-gray-600 hover:bg-gray-50"
                    @click="emit('close')">
                    Cancelar
                </button>
                <button type="button"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                    @click="submitCreate">
                    Guardar
                </button>
            </div>
        </template>
    </Drawer>
</template>