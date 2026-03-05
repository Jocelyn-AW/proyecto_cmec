<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3'
import { useAlert } from '@/composables/useAlert'
import { onMounted } from 'vue';
import EventsTable from '@/Components/EventsTable.vue';

defineOptions({ layout: AuthenticatedLayout })

const { success, errorA } = useAlert()

const props = defineProps({
    academicSessions: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
    flash: { type: Object, default: () => ({}) },
    auth: { type: Object, default: () => ({}) },
    errors: { type: Object, default: () => ({}) },
})

const page = usePage()

onMounted(() => {
    if (page.props.success || props.flash.success) success(page.props.success || props.flash.success)
    if (page.props.error || props.flash.error) errorA(page.props.error || props.flash.error)
})
</script>

<template>

    <Head title="Sesiones Académicas" />

    <EventsTable :paginator="academicSessions" :filters="filters" :only="['academicSessions']"
        route-prefix="academicsessions" title="Sesiones Académicas"
        subtitle="Administra y supervisa las sesiones de capacitación y eventos" eyebrow="Módulo Académico"
        create-label="Nueva Sesión" entity-label="sesión académica" />
</template>