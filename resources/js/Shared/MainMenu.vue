<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import icon from '@/Shared/Icon.vue'

const isUrl = (url) => {
    let page = usePage();
    const currentUrl = page.url.startsWith('/') ? page.url.substring(1) : page.url;

    if (url === '') {
        return currentUrl === '';
    }
    return currentUrl.startsWith(url);
}

</script>

<template>
    <aside class="">
        <div class="mb-4">
            <Link class="group flex items-center py-3" href="/">
            <icon name="dashboard" class="mr-2 w-4 h-4"
                :class="isUrl('') ? 'fill-white' : 'fill-indigo-400 group-hover:fill-white'" />
            <div :class="isUrl('') ? 'text-white' : 'text-indigo-300 group-hover:text-white'">Dashboard</div>
            </Link>
        </div>
        <div class="mb-4">
            <Link class="group flex items-center py-3" href="/organizations">
            <icon name="office" class="mr-2 w-4 h-4"
                :class="isUrl('organizations') ? 'fill-white' : 'fill-indigo-400 group-hover:fill-white'" />
            <div :class="isUrl('organizations') ? 'text-white' : 'text-indigo-300 group-hover:text-white'">Organizations
            </div>
            </Link>
        </div>
        <div class="mb-4">
            <Link class="group flex items-center py-3" href="/contacts">
            <icon name="users" class="mr-2 w-4 h-4"
                :class="isUrl('contacts') ? 'fill-white' : 'fill-indigo-400 group-hover:fill-white'" />
            <div :class="isUrl('contacts') ? 'text-white' : 'text-indigo-300 group-hover:text-white'">Contacts</div>
            </Link>
        </div>
        <div class="mb-4">
            <Link class="group flex items-center py-3" href="/reports">
            <icon name="printer" class="mr-2 w-4 h-4"
                :class="isUrl('reports') ? 'fill-white' : 'fill-indigo-400 group-hover:fill-white'" />
            <div :class="isUrl('reports') ? 'text-white' : 'text-indigo-300 group-hover:text-white'">Reports</div>
            </Link>
        </div>
    </aside>

</template>
