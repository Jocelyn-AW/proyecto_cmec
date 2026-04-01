<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { useAlert } from '@/composables/useAlert'
import { computed, onMounted, watch, ref, reactive } from 'vue';
import flatPickr from "vue-flatpickr-component";
import { Spanish } from "flatpickr/dist/l10n/es.js";
import "flatpickr/dist/flatpickr.css";
import Drawer from '@/Components/Drawer.vue';
import Alerta from '@/Components/Alerta.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import states from '@/composables/useStatesAndCities';
import UseCFDI from '@/Components/UseCFDI.vue';
import axios from 'axios'

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
const isLoadingMember = ref(false)

watch(() => props.flash, (value) => {
    if (!value) return

    if (value.success) success(value.success)
    if (value.warning) warning(value.warning)
    if (value.error) errorA(value.error)
}, { immediate: true, deep: true })

const emit = defineEmits(['close', 'success', 'error'])

const page = usePage();
const selectedEvent = ref(null)
const isFree = ref(false)
const showInvoiceForm = ref(false)
const selectedState = ref('')
const selectedCity = ref('')
const paymentMethods = {
    'cash': 'Efectivo',
    'debit_card': 'Tarjeta de Débito',
    'credit_card': 'Tarjeta de Crédito',
    'transfer': 'Transferencia',
    'stripe': 'En línea (stripe)',
    'free': 'Sin costo'
}

const flatpickrConfig = {
    locale: Spanish,
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "F j, Y",
    wrap: false,
};

//Computed props
const cities = computed(() => {
    return selectedState.value ? states[selectedState.value] : []
})

const errors = computed(() => page.props.errors || props.errors || {})

const price = computed(() => {
    if (!selectedEvent.value || !createForm.person_type) return ''

    const priceMap = {
        'member': selectedEvent.value.member_price,
        'guest': selectedEvent.value.guest_price,
        'resident': selectedEvent.value.resident_price,
        'nurse': selectedEvent.value.nurse_price,
        'surgeon': selectedEvent.value.surgeon_price,
    }

    return priceMap[createForm.person_type] ?? createForm.price
})

const shouldHaveReference = computed(() => {
    const validMethods = ['debit_card', 'credit_card', 'transfer', 'stripe'];
    const validStatus = ['paid', 'cancelled'];

    const hasValidMethod = validMethods.includes(createForm.payment_method);
    const hasValidStatus = validStatus.includes(createForm.status);

    return hasValidMethod && hasValidStatus;
})

const isConference = computed(() => {
    const conferenceEvents = ['Congreso', 'Pre-congreso', 'Trans-congreso'];
    return conferenceEvents.includes(props.eventName);
})

const fetchMemberData = async () => {
    if (!createForm.cmec_member_id?.trim()) return

    isLoadingMember.value = true
    try {
        const { data } = await axios.get(`/attendees/by-cmec/${createForm.cmec_member_id.trim()}`)

        if (data.found) {
            const m = data.member

            createForm.name = m.name || ''
            createForm.email = m.email || ''
            createForm.phone = m.phone || ''

            selectedState.value = m.state || ''
            setTimeout(() => { selectedCity.value = m.city || '' }, 100)

            // Datos fiscales
            if (m.has_invoice_data) {
                createForm.rfc = m.rfc
                createForm.tax_name = m.tax_name
                createForm.postal_code = m.postal_code
                createForm.tax_person_type = m.tax_person_type
                createForm.cfdi_use = m.cfdi_use
                createForm.tax_regime = m.tax_regime
                createForm.address = m.address
                showInvoiceForm.value = true
            }

            success('Datos del miembro cargados')
        }
    } catch (e) {
        if (e.response?.status === 404) {
            errorA('No se encontró un miembro (activo) con ese folio')
        } else {
            errorA('Error al buscar el miembro')
        }
    } finally {
        isLoadingMember.value = false
    }
}


//String Formatters
const generateRandomString = (length = 5) => {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
};

//Form Actions
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
    cmec_member_id: null,
    reference: '',
    payment_method: '',
    specialty: '',
    birth_date: '',
    special_needs: '',

    //campos fiscales
    rfc: '',
    tax_name: '',
    postal_code: '',
    tax_person_type: '',
    tax_regime: '',
    cfdi_use: '',
    address: '',
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
    createForm.reference = '';
    createForm.payment_method = '';
    createForm.specialty = '';
    createForm.birth_date = '';
    createForm.special_needs = '';

    createForm.rfc = '';
    createForm.tax_name = '';
    createForm.postal_code = '';
    createForm.tax_person_type = '';
    createForm.tax_regime = '';
    createForm.cfdi_use = '';
    createForm.address = '';

    selectedEvent.value = '';
    selectedState.value = '';
    selectedCity.value = '';
}


const submitCreate = () => {
    createForm.event_id = selectedEvent?.value?.id;
    createForm.has_invoice = showInvoiceForm?.value;

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
        case 'sesion academica':
            createForm.event_type = 'academic_session';
            break;
        case 'pre-congreso':
            createForm.event_type = 'pre_conference';
            break;
        case 'trans-congreso':
            createForm.event_type = 'trans_conference';
            break;
        default:
            createForm.event_type = 'course'; // Valor por defecto
    }

    router.post(route('attendees.store'), createForm, {
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
    if (newVal) {
        cleanForm();
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

    if (parseInt(val) <= 0) {
        createForm.payment_method = 'free';
        createForm.status = 'paid';
        isFree.value = true;
    } else {
        isFree.value = false;
        createForm.payment_method = '';
        createForm.status = '';
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
    <Drawer :show="show" title="Nuevo asistente" :subtitle="props.eventName" size="xl" @close="emit('close')">
        <div class="space-y-4">
            <!-- Evento -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ props.eventName }}</label>
                <select name="event_id" id="event_id" v-model="selectedEvent"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Seleccionar {{ props.eventName.toLowerCase() }}</option>
                    <option v-for="event in events" :key="event.id" :value="event">{{ event.name || event.topic }}
                    </option>
                </select>
                <span v-if="errors?.event_id" class="text-red-500 text-xs flex justify-end">{{ errors?.event_id
                }}</span>
            </div>

            <!-- Tipo de participante -->
            <div v-if="isConference">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de participante</label>
                <select name="person_type" id="person_type" v-model="createForm.person_type"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="" selected>Seleccionar tipo</option>
                    <option value="member">Miembro CMEC</option>
                    <option value="surgeon">Residente de Cirugía</option>
                    <option value="nurse">Estudiante o Enfermero</option>
                    <option value="resident">Residente o Medico General</option>
                    <option value="guest">Especialista (no miembro)</option>
                </select>
                <span v-if="errors?.person_type" class="text-red-500 text-xs flex justify-end">{{ errors?.person_type
                }}</span>
            </div>
            <div v-else>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de participante</label>
                <select name="person_type" id="person_type" v-model="createForm.person_type"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="" selected>Seleccionar tipo</option>
                    <option value="member">Miembro CMEC</option>
                    <option value="resident">Residente</option>
                    <option value="guest">Invitado (no socio)</option>
                </select>
                <span v-if="errors?.person_type" class="text-red-500 text-xs flex justify-end">{{ errors?.person_type
                }}</span>
            </div>

            <!-- Folio miembro CMEC -->
            <div v-if="createForm.person_type == 'member'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Folio de miembro CMEC</label>
                <div class="flex gap-2">
                    <input v-model="createForm.cmec_member_id" type="text" placeholder="Ej. CMEC-0001"
                        class="grow rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        @keyup.enter="fetchMemberData" />
                    <button type="button" @click="fetchMemberData"
                        :disabled="isLoadingMember || !createForm.cmec_member_id?.trim()"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shrink-0">
                        <svg v-if="isLoadingMember" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        {{ isLoadingMember ? 'Buscando...' : 'Buscar' }}
                    </button>
                </div>
                <span v-if="errors?.cmec_member_id" class="text-red-500 text-xs flex justify-end">{{
                    errors?.cmec_member_id }}</span>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre médico
                    <span
                        class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-gray-500/10 dark:text-gray-400">se
                        mostrará en el diploma</span>
                </label>
                <input v-model="createForm.name" type="text"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                <span v-if="errors?.name" class="text-red-500 text-xs flex justify-end">{{ errors?.name }}</span>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Especialidad</label>
                <input v-model="createForm.specialty" type="text"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                <span v-if="errors?.specialty" class="text-red-500 text-xs flex justify-end">{{ errors?.specialty
                    }}</span>
            </div>
            <div class="flex gap-2 w-full">
                <div class="flex flex-col grow">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                    <input v-model="createForm.email" type="email"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    <span v-if="errors?.email" class="text-red-500 text-xs flex justify-end">{{ errors?.email }}</span>
                </div>
                <div class="flex flex-col grow">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input v-model="createForm.phone" type="text" maxlength="15"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    <span v-if="errors?.phone" class="text-red-500 text-xs flex justify-end">{{ errors?.phone }}</span>
                </div>
            </div>
            <!-- Solo en Congreso -->
            <div v-if="isConference">
                <div class="flex gap-2 w-full">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de nacimiento</label>
                        <flat-pickr v-model="createForm.birth_date" :config="flatpickrConfig"
                            class=" grow dark:bg-dark-900 h-10 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pl-4 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                            placeholder="Selecciona una fecha" />
                    </div>

                    <div class="grow">
                        <label class="block text-sm font-medium text-gray-700 mb-1">¿Requiere atención especial?
                            <span
                                class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-gray-500/10 dark:text-gray-400">opcional</span>
                        </label>
                        <input v-model="createForm.special_needs" type="text"
                            placeholder="Ej: Silla de ruedas, discapacidad"
                            class="grow w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                </div>
                <div class="flex gap-9 w-full">
                    <span v-if="errors?.birth_date" class="grow text-red-500 text-xs flex justify-end">{{
                        errors?.birth_date }}</span>
                    <span v-if="errors?.special_needs" class="grow text-red-500 text-xs flex justify-end">{{
                        errors?.special_needs }}</span>
                </div>
            </div>
            <!--  -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Origen</label>
                <div class="flex gap-2 w-full">
                    <select name="state" id="state" v-model="selectedState"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
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
                    <span v-if="errors?.state" class="grow text-red-500 text-xs flex justify-end">{{ errors?.state
                        }}</span>
                    <span v-if="errors?.city" class="grow text-red-500 text-xs flex justify-end">{{ errors?.city
                        }}</span>
                </div>
            </div>

            <hr class="my-2">

            <!-- Detalles de Pago -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Detalles de Pago</label>
                <!-- Cantidad / Metodo de Pago -->
                <div class="flex gap-2 w-full">
                    <input :value="price" disabled type="number" min="0" step="0.01"
                        class="grow rounded-lg disabled border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Cantidad a pagar" />
                    <select name="method" id="method" v-model="createForm.payment_method" :disabled="isFree"
                        :class="isFree ? 'disabled' : ''"
                        class="rounded-lg grow border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="" selected>Seleccionar método de pago</option>
                        <option v-for="(method, key) in paymentMethods" :key="key" :value="key">{{ method }}</option>
                    </select>
                </div>
                <div class="flex gap-9 w-full">
                    <span v-if="errors?.price" class="grow text-red-500 text-xs flex justify-end">{{ errors?.price
                        }}</span>
                    <span v-if="errors?.payment_method" class="grow text-red-500 text-xs flex justify-end">{{
                        errors?.payment_method }}</span>
                </div>

                <!-- Estatus  -->
                <div class="flex gap-2 w-full mt-3">
                    <select name="status" id="status" v-model="createForm.status" :disabled="isFree"
                        :class="isFree ? 'disabled' : ''"
                        class="rounded-lg grow border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="" selected>Seleccionar estatus de pago</option>
                        <option value="paid">Pagado</option>
                        <option value="pending">Pendiente</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                </div>
                <span v-if="errors?.status" class="grow text-red-500 text-xs flex justify-end">{{ errors?.status
                    }}</span>

                <!-- Referencia -->
                <div class="flex mt-3" v-if="shouldHaveReference">
                    <input v-model="createForm.reference" type="text"
                        class="grow rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Referencia o  Numero de transaccion" />
                </div>
                <span v-if="errors?.reference" class="grow text-red-500 text-xs flex justify-end">{{ errors?.reference
                    }}</span>


                <!-- Switch mostrar facturacion -->
                <div class="flex mt-3 py-2 gap-4">
                    <button type="button" @click="showInvoiceForm = !showInvoiceForm"
                        class="relative inline-flex h-5 w-10 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                        :class="showInvoiceForm ? 'bg-indigo-500' : 'bg-gray-300 dark:bg-gray-600'">
                        <span
                            class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                            :class="showInvoiceForm ? 'translate-x-5' : 'translate-x-0'" />
                    </button>
                    <label class=" text-sm font-medium text-gray-700 mb-1">Requiere Factura</label>
                </div>

                <hr class="my-2">

                <!-- Datos Fiscales -->
                <UseCFDI v-if="showInvoiceForm" v-model="createForm" :errors="props.errors" />

                <!-- Folio Acceso -->
                <div v-if="!isConference">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Folio de Acceso</label>
                    <input v-model="createForm.folio" type="text" disabled
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-100 cursor-not-allowed" />
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <button type="button" class="rounded-lg border px-4 py-2 text-sm text-gray-600 hover:bg-gray-50"
                    @click="emit('close')">
                    Cancelar
                </button>
                <button type="button"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                    @click="submitCreate">
                    Guardar
                </button>
            </div>
        </template>
    </Drawer>
</template>