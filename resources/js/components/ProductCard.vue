<script setup lang="ts">
import ProductArt from '@/components/ProductArt.vue';
import { useT } from '@/composables/useT';
import type { ProductCard } from '@/types/store';
import { Link, useForm } from '@inertiajs/vue3';
import { LoaderCircle, Plus } from 'lucide-vue-next';

const props = defineProps<{ product: ProductCard }>();
const { t } = useT();

const form = useForm({ product_id: props.product.id, quantity: 1 });
const add = () => form.post(route('cart.store'), { preserveScroll: true });
</script>

<template>
    <article
        class="group relative flex flex-col overflow-hidden rounded-2xl border border-stone-200 bg-white transition hover:-translate-y-0.5 hover:shadow-lg"
    >
        <Link :href="route('products.show', product.slug)" class="relative block aspect-square overflow-hidden">
            <ProductArt :id="product.id" :image="product.image" :emoji="product.emoji" :name="product.name" />
            <span v-if="product.discount" class="absolute start-3 top-3 rounded-full bg-rose-600 px-2 py-0.5 text-xs font-bold text-white">
                <span dir="ltr">−{{ product.discount }}%</span>
            </span>
            <span
                v-if="!product.in_stock"
                class="absolute inset-x-0 bottom-0 bg-stone-900/70 py-1.5 text-center text-xs font-semibold uppercase tracking-wide text-white"
            >
                {{ t('out_of_stock') }}
            </span>
        </Link>

        <div class="flex flex-1 flex-col gap-1 p-4">
            <p v-if="product.category" class="text-xs font-medium text-stone-500">{{ product.category }}</p>
            <Link :href="route('products.show', product.slug)" class="line-clamp-2 font-semibold leading-snug hover:text-primary">
                {{ product.name }}
            </Link>
            <p v-if="product.low_stock" class="text-xs font-medium text-amber-700">{{ t('only_left', { count: product.low_stock }) }}</p>

            <div class="mt-auto flex items-end justify-between gap-2 pt-3">
                <div class="leading-tight">
                    <p class="font-bold tabular-nums">{{ product.price }}</p>
                    <p v-if="product.compare_at_price" class="text-xs tabular-nums text-stone-400 line-through">{{ product.compare_at_price }}</p>
                </div>
                <button
                    type="button"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-white shadow-sm transition hover:bg-primary/90 disabled:cursor-not-allowed disabled:bg-stone-300"
                    :disabled="!product.in_stock || form.processing"
                    :aria-label="`${t('add_to_cart')}: ${product.name}`"
                    @click="add"
                >
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    <Plus v-else class="h-5 w-5" />
                </button>
            </div>
        </div>
    </article>
</template>
