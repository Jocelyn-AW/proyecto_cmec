<script setup>
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { useAlert } from '@/composables/useAlert'
import { computed, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Alerta from '@/Components/Alerta.vue';

const props = defineProps({
    flash:  { type: Object, default: () => ({}) },
    errors: { type: Object, default: () => ({}) },
    auth:   { type: Object, default: () => ({}) },

    event:          { type: Object, default: () => ({}) },
    event_type:     { type: String, default: '' },
    event_name:     { type: String, default: '' },
    person_types:   { type: Array,  default: () => [] },
    amounts:        { type: Object, default: () => ({}) },
    has_online_payment: { type: Boolean, default: false },
})

const { alertState, warning, hideAlert, success, errorA } = useAlert();
watch(() => props.flash, (value) => {
    if (!value) return

    if (value.success) success(value.success)
    if (value.warning) warning(value.warning)
    if (value.error) errorA(value.error)
}, { immediate: true, deep: true })

const isSubmitting = ref(false);
const memberStatus = ref('');
const isMemberVerified = ref(false);

const form = useForm({
    person_type: 'guest',
    cmec_member_id: '',
    name: '',
    email: '',
    phone: '',
    state: '',
    city: '',
    specialty: '',
    birth_date: '',
    special_needs: '',
})

const showMemberField = computed(() => form.person_type === 'member')

const toggleMemberField = (value) => {
    if (value !== 'member') {
        isMemberVerified.value = false;
        memberStatus.value = '';
    }
}

const validateMember = async () => {
    if (!form.cmec_member_id) return;

    memberStatus.value = 'Verificando...';

    try {
        const { data } = await axios.post(route('public.event.validate-member'), {
            cmec_member_id: form.cmec_member_id
        });

        form.name  = data.name;
        form.email = data.email;
        form.phone = data.phone;
        form.state = data.state;
        form.city  = data.city;

        isMemberVerified.value = true;
        memberStatus.value = '✓ Miembro verificado';
    } catch (e) {
        memberStatus.value = 'Número de socio no encontrado';
        isMemberVerified.value = false;
    }
}

watch(() => form.person_type, (newValue) => {
    if (newValue !== 'member') {
        form.cmec_member_id = '';
        toggleMemberField(newValue);
    }
})

const showConfirmModal = ref(false)

const handleSubmit = () => {
    if (!form.name || !form.email || !form.phone || !form.state || !form.city || !form.person_type) {
        warning('Por favor completa todos los campos obligatorios.')
        return;
    }

    if (showMemberField.value && !isMemberVerified.value) {
        warning('Por favor verifica tu número de socio para continuar.')
        return;
    }
    
    showConfirmModal.value = true
}

const handleConfirm = () => {
    showConfirmModal.value = false
    form.post(route('public.event.store', { eventType: props.event_type, eventId: props.event.id }), {
        preserveScroll: true,
        onStart: () => isSubmitting.value = true,
        onFinish: () => isSubmitting.value = false,
    })
}
</script>

<template>
    <Head :title="`Registro - ${event_name}`" />
    <div class="min-h-screen m-auto max-w-9/10">
        <!-- Header -->
        <div
            class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800">
            <div class="flex flex-col lg:flex-row">
                <div class="flex flex-1 flex-col justify-center p-8 lg:p-10">
                    <p class="text-sm font-medium text-brand-600">Registro</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-gray-800 dark:text-white/90 sm:text-3xl">
                        {{ event_name }}
                    </h1>
                    <p class="mt-3 max-w-md text-sm leading-relaxed text-gray-500">
                        Completa el formulario para registrarte al evento. Todos los campos marcados son obligatorios.
                    </p>
                </div>

                <!-- Icono -->
                <div class="relative h-40 w-full lg:h-auto lg:w-64 xl:w-72 overflow-hidden">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-brand-600 via-brand-500 to-brand-400 opacity-10 dark:opacity-5" />
                    <div class="absolute inset-0 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="text-brand-200 h-24 w-24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <template v-if="has_online_payment">
            <div class="mt-6">
                <!-- Tipo de asistente -->
                <div
                    class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800">
                    <!-- Encabezado -->
                    <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 px-8 py-5">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Tipo de Asistente</h2>
                            <p class="text-xs text-gray-500">Selecciona tu tipo de registro</p>
                        </div>
                    </div>

                    <!-- Datos -->
                    <div class="px-8 py-6 space-y-6">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tipo de
                                asistente</label>
                            <select v-model="form.person_type" @change="toggleMemberField(form.person_type)"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                <option v-for="type in person_types" :key="type.value" :value="type.value">
                                    {{ type.label }}
                                </option>
                            </select>
                            <span v-if="errors.person_type" class="text-red-500 text-xs flex justify-end mt-1">{{
                                errors.person_type }}</span>
                        </div>

                        <!-- Campo exclusivo para miembros -->
                        <div v-if="showMemberField" class="space-y-3">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Número de socio
                                (CMEC)</label>
                            <div class="flex gap-3">
                                <input type="text" v-model="form.cmec_member_id" @input="errors.cmec_member_id = '', memberStatus = ''"
                                    class="dark:bg-dark-900 h-11 flex-1 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                    placeholder="Ingresa tu número de socio" />
                                <button type="button" @click="validateMember"
                                    class="rounded-lg bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                                    Verificar
                                </button>
                            </div>
                            <span v-if="memberStatus"
                                :class="isMemberVerified ? 'text-green-500' : 'text-amber-500'"
                                class="text-xs flex justify-end">
                                {{ memberStatus }}
                            </span>
                            <span v-if="errors.cmec_member_id" class="text-red-500 text-xs flex justify-end mt-1">{{
                                errors.cmec_member_id }}</span>
                        </div>
                    </div>
                </div>

                <!-- Datos personales -->
                <div
                    class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800">
                    <!-- Encabezado -->
                    <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 px-8 py-5">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                                <line x1="16" y1="13" x2="8" y2="13" />
                                <line x1="16" y1="17" x2="8" y2="17" />
                                <polyline points="10 9 9 9 8 9" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Datos Personales</h2>
                            <p class="text-xs text-gray-500">Información de contacto</p>
                        </div>
                    </div>

                    <!-- Datos -->
                    <div class="px-8 py-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nombre
                                    completo *</label>
                                <input type="text" v-model="form.name" :readonly="isMemberVerified" required @input="errors.name = ''"
                                    :class="{ 'bg-gray-50 dark:bg-gray-800/50': isMemberVerified }"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                <span v-if="errors.name" class="text-red-500 text-xs flex justify-end mt-1">{{ errors.name }}</span>
                            </div>

                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email *</label>
                                <input type="email" v-model="form.email" :readonly="isMemberVerified" required @input="errors.email = ''"
                                    :class="{ 'bg-gray-50 dark:bg-gray-800/50': isMemberVerified }"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                <span v-if="errors.email" class="text-red-500 text-xs flex justify-end mt-1">{{ errors.email }}</span>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Teléfono
                                    *</label>
                                <input type="text" v-model="form.phone" :readonly="isMemberVerified" required maxlength="12" @input="errors.phone = ''"
                                    :class="{ 'bg-gray-50 dark:bg-gray-800/50': isMemberVerified }"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                <span v-if="errors.phone" class="text-red-500 text-xs flex justify-end mt-1">{{ errors.phone }}</span>
                            </div>

                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Estado *</label>
                                <input type="text" v-model="form.state" required
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                <span v-if="errors.state" class="text-red-500 text-xs flex justify-end mt-1">{{ errors.state }}</span>
                            </div>

                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Ciudad *</label>
                                <input type="text" v-model="form.city" required
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                <span v-if="errors.city" class="text-red-500 text-xs flex justify-end mt-1">{{ errors.city }}</span>
                            </div>

                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Especialidad</label>
                                <input type="text" v-model="form.specialty"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                <span v-if="errors.specialty" class="text-red-500 text-xs flex justify-end mt-1">{{ errors.specialty }}</span>
                            </div>

                            <div v-if="event_type == 'conference'">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Fecha de
                                    nacimiento</label>
                                <input type="date" v-model="form.birth_date"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                <span v-if="errors.birth_date" class="text-red-500 text-xs flex justify-end mt-1">{{ errors.birth_date }}</span>
                            </div>

                            <div v-if="event_type == 'conference'"">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Necesidades
                                    especiales</label>
                                <input type="text" v-model="form.special_needs"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                <span v-if="errors.special_needs" class="text-red-500 text-xs flex justify-end mt-1">{{ errors.special_needs }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón de envío -->
            <div
                class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800">
                <div class="flex flex-row-reverse gap-3 px-8 py-5">
                    <button @click="handleSubmit" :disabled="isSubmitting"
                        class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span v-if="isSubmitting">Procesando...</span>
                        <span v-else>Continuar al pago</span>
                    </button>
                </div>
            </div>
        </template>
        
        <template v-else>
            <div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-white/[0.03] dark:border dark:border-gray-800">
                <div class="flex flex-col items-center justify-center px-8 py-16 text-center">

                    <!-- Ícono -->
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round">
                            <rect x="2" y="5" width="20" height="14" rx="2"/>
                            <line x1="2" y1="10" x2="22" y2="10"/>
                            <line x1="6" y1="15" x2="6.01" y2="15"/>
                            <line x1="10" y1="15" x2="14" y2="15"/>
                        </svg>
                    </div>

                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        Registro en línea no disponible
                    </h2>

                    <p class="mt-3 max-w-sm text-sm text-gray-500 dark:text-gray-400">
                        Este evento no tiene habilitado el pago en línea por el momento. 
                        Para registrarte comunícate con nosotros.
                    </p>

                    <!-- Puedes agregar info de contacto si la tienes -->
                    <div class="mt-8 flex flex-col sm:flex-row gap-3">
                        <a href="mailto:contacto@cmec.mx"
                            class="rounded-lg border border-gray-200 dark:border-gray-700 px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            Contactar por correo
                        </a>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Modal de confirmación -->
    <Teleport to="body">
        <div v-if="showConfirmModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-gray-900 dark:border dark:border-gray-800">

                <!-- Header -->
                <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 px-6 py-4">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 11l3 3L22 4" />
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Confirmar registro</h2>
                        <p class="text-xs text-gray-500">Verifica que tus datos sean correctos</p>
                    </div>
                </div>

                <!-- Datos -->
                <div class="px-6 py-4 space-y-3">
                    <div class="rounded-xl bg-gray-50 dark:bg-gray-800/50 px-4 py-3 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Nombre</span>
                            <span class="font-medium text-gray-800 dark:text-white/90">{{ form.name }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Email</span>
                            <span class="font-medium text-gray-800 dark:text-white/90">{{ form.email }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Teléfono</span>
                            <span class="font-medium text-gray-800 dark:text-white/90">{{ form.phone }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Estado / Ciudad</span>
                            <span class="font-medium text-gray-800 dark:text-white/90">{{ form.state }}, {{ form.city }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Tipo de asistente</span>
                            <span class="font-medium text-gray-800 dark:text-white/90">
                                {{ person_types.find(t => t.value === form.person_type)?.label }}
                            </span>
                        </div>
                    </div>

                    <!-- Precio -->
                    <div class="rounded-xl bg-brand-50 dark:bg-brand-500/10 px-4 py-3 flex justify-between items-center">
                        <span class="text-sm font-medium text-brand-700 dark:text-brand-400">Total a pagar</span>
                        <span class="text-lg font-bold text-brand-700 dark:text-brand-400">
                            ${{ props.event[props.amounts[form.person_type]]?.toLocaleString('es-MX') }} MXN
                        </span>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex gap-3 border-t border-gray-100 dark:border-gray-800 px-6 py-4">
                    <button @click="showConfirmModal = false"
                        class="flex-1 rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        Editar datos
                    </button>
                    <button @click="handleConfirm" :disabled="form.processing"
                        class="flex-1 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50 transition-colors">
                        <span v-if="form.processing">Procesando...</span>
                        <span v-else>Confirmar y pagar</span>
                    </button>
                </div>
            </div>
        </div>
    </Teleport>

    <Alerta :show="alertState.show" :message="alertState.message" :title="alertState.title" :type="alertState.type"
        :buttonText="alertState.buttonText" :cancelText="alertState.cancelText"
        @confirm="alertState.onConfirm ? alertState.onConfirm() : hideAlert()" @cancel="hideAlert()"
        @close="hideAlert()" />
</template>
