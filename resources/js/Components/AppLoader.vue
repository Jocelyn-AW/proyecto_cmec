<script setup>

/* USO
import AppLoader from '@/Components/AppLoader.vue'
<AppLoader />
<AppLoader :size="80" />
<AppLoader color="#10b981" />
<AppLoader center />
 */
const props = defineProps({
    size: {
        type: Number,
        default: 64
    },
    color: {
        type: String,
        default: '#3b82f6' // azul moderno
    },
    center: {
        type: Boolean,
        default: false
    }
})

</script>

<template>
    <div class="loader-wrapper" :class="{ 'loader-center': center }">

        <svg :width="size" :height="size * 0.75" viewBox="0 0 64 48">

            <polyline class="loader-back" points="0.157 23.954, 14 23.954, 21.843 48, 43 0, 50 24, 64 24" />

            <polyline class="loader-front" :style="{ stroke: color }"
                points="0.157 23.954, 14 23.954, 21.843 48, 43 0, 50 24, 64 24" />

        </svg>

    </div>
</template>

<style scoped>
.loader-wrapper svg polyline {
    fill: none;
    stroke-width: 3;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.loader-back {
    stroke: rgba(0, 0, 0, 0.08);
}

.loader-front {
    stroke-dasharray: 48 144;
    stroke-dashoffset: 192;
    animation: loader-dash 1.4s linear infinite;
}

@keyframes loader-dash {

    72.5% {
        opacity: 0;
    }

    100% {
        stroke-dashoffset: 0;
    }

}

.loader-center {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}
</style>