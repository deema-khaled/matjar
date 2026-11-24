<script setup lang="ts">
import { computed } from 'vue';

/**
 * Product image, or, when no photo was uploaded, a soft gradient tile
 * with the product's emoji. The gradient is picked from the id so each
 * product keeps the same colours.
 */
const props = defineProps<{ id: number; image: string | null; emoji: string | null; name: string; size?: 'md' | 'lg' }>();

const gradients = [
    'from-amber-100 to-orange-200',
    'from-teal-100 to-emerald-200',
    'from-sky-100 to-indigo-200',
    'from-rose-100 to-pink-200',
    'from-lime-100 to-green-200',
    'from-violet-100 to-purple-200',
    'from-stone-100 to-stone-300',
];

const gradient = computed(() => gradients[props.id % gradients.length]);
</script>

<template>
    <img v-if="image" :src="image" :alt="name" class="h-full w-full object-cover" loading="lazy" />
    <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br" :class="gradient" role="img" :aria-label="name">
        <span class="drop-shadow-sm transition duration-300 group-hover:scale-110" :class="size === 'lg' ? 'text-[9rem]' : 'text-6xl'">{{
            emoji ?? '🛍️'
        }}</span>
    </div>
</template>
