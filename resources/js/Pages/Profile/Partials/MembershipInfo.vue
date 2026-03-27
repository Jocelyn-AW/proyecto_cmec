<script setup>
import { computed } from 'vue';

const props = defineProps({
    member: { type: Object, required: true },
});

// Calculo si la membresia esta activa y cuando tiempo le queda
const status = computed(() => {
    if (!props.member?.expiration_date) return 'unknown';
    const today = new Date();
    const expiry = new Date(props.member.expiration_date);
    const daysLeft = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));
    if (daysLeft < 0) return 'expired';
    if (daysLeft <= 30) return 'expiring';
    return 'active';
});

const statusConfig = computed(() => {
    const configs = {
        active: {
            label: 'Activa',
            bg: 'bg-emerald-50',
            text: 'text-emerald-700',
            dot: 'bg-emerald-500',
            ring: 'ring-emerald-200',
        },
        expiring: {
            label: 'Por vencer',
            bg: 'bg-amber-50',
            text: 'text-amber-700',
            dot: 'bg-amber-500',
            ring: 'ring-amber-200',
        },
        expired: {
            label: 'Vencida',
            bg: 'bg-red-50',
            text: 'text-red-700',
            dot: 'bg-red-500',
            ring: 'ring-red-200',
        },
        unknown: {
            label: 'Sin información',
            bg: 'bg-slate-50',
            text: 'text-slate-500',
            dot: 'bg-slate-400',
            ring: 'ring-slate-200',
        },
    };
    return configs[status.value];
});

const daysRemaining = computed(() => {
    if (!props.member?.expiration_date) return null;
    const today = new Date();
    const expiry = new Date(props.member.expiration_date);
    return Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));
});

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    const date = new Date(dateStr);
    return date.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
};

const fullName = computed(() => {
    const parts = [props.member?.name, props.member?.last_name].filter(Boolean);
    return parts.length ? parts.join(' ') : '—';
});

const location = computed(() => {
    const parts = [props.member?.city, props.member?.state].filter(Boolean);
    return parts.length ? parts.join(', ') : '—';
});
</script>

<template>
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">

        <!-- Header -->
        <div class="flex items-center gap-3 border-b border-slate-100 px-8 py-5">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                    <rect width="20" height="14" x="2" y="5" rx="2" />
                    <path d="M2 10h20" />
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-slate-800">Membresía</h2>
                <p class="text-xs text-slate-500">Información de tu membresía CMEC</p>
            </div>
        </div>

        <!-- Body -->
        <div class="p-8">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">

                <!-- ID + Estado -->
                <div class="flex flex-col gap-4">
                    <!-- CMEC ID -->
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            ID de Miembro
                        </p>
                        <p class="mt-1 text-xl font-bold tracking-tight text-brand-600">
                            {{ member.cmec_member_id ?? '—' }}
                        </p>
                    </div>

                    <!-- Estado badge -->
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1.5">
                            Estado
                        </p>
                        <span :class="[statusConfig.bg, statusConfig.text, statusConfig.ring]"
                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ring-1">
                            <span :class="statusConfig.dot" class="h-1.5 w-1.5 rounded-full" />
                            {{ statusConfig.label }}
                        </span>

                        <!-- dias restantes -->
                        <p v-if="daysRemaining !== null && daysRemaining >= 0" class="mt-1.5 text-xs text-slate-400">
                            {{ daysRemaining === 0
                                ? 'Vence hoy'
                                : `Vence en ${daysRemaining} día${daysRemaining === 1 ? '' : 's'}` }}
                        </p>
                        <p v-else-if="daysRemaining !== null && daysRemaining < 0" class="mt-1.5 text-xs text-red-400">
                            Venció hace {{ Math.abs(daysRemaining) }} día{{ Math.abs(daysRemaining) === 1 ? '' : 's' }}
                        </p>
                    </div>
                </div>

                <!-- Vigencia -->
                <div class="flex flex-col gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Fecha de inscripción
                        </p>
                        <p class="mt-1 text-sm font-medium text-slate-700">
                            {{ formatDate(member.inscription_date) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Fecha de vencimiento
                        </p>
                        <p class="mt-1 text-sm font-medium text-slate-700">
                            {{ formatDate(member.expiration_date) }}
                        </p>
                    </div>
                </div>

                <!-- Datos -->
                <div class="flex flex-col gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Nombre registrado
                        </p>
                        <p class="mt-1 text-sm font-medium text-slate-700">
                            {{ fullName }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Ubicación
                        </p>
                        <p class="mt-1 text-sm font-medium text-slate-700">
                            {{ location }}
                        </p>
                    </div>
                    <div v-if="member.hospital">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Hospital
                        </p>
                        <p class="mt-1 text-sm font-medium text-slate-700">
                            {{ member.hospital }}
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>