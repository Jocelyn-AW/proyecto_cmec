<script setup>
import { ref } from 'vue';
import Sidebar from '@/Shared/Sidebar.vue';
import AppHeader from '@/Shared/AppHeader.vue';
import { useSidebarProvider } from '@/composables/useSidebar';
import Backdrop from '@/Shared/Backdrop.vue';

useSidebarProvider();

</script>

<template>
    <div class="min-h-screen bg-gray-100 xl:flex">
        <Sidebar />
        <Backdrop />
        <div class="flex-1 transition-all duration-300 ease-in-out lg:ml-[90px]">
            <AppHeader />
            <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
                <!-- Page Content -->
                <slot></slot>
            </div>
        </div>
    </div>
</template>
