<script setup lang="ts">
import ProductArt from '@/components/ProductArt.vue';
import ProductCard from '@/components/ProductCard.vue';
import { useT } from '@/composables/useT';
import StoreLayout from '@/layouts/StoreLayout.vue';
import type { ProductCard as Card, Category } from '@/types/store';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Banknote, ChevronLeft, ChevronRight, LoaderCircle, Minus, Plus, RotateCcw, ShoppingBag, Truck } from 'lucide-vue-next';

const props = defineProps<{
    product: Omit<Card, 'category'> & { description: string | null; stock: number; max_quantity: number; category: Category };
    related: Card[];
}>();

const { t } = useT();

const form = useForm({ product_id: props.product.id, quantity: 1 });
const step = (delta: number) => (form.quantity = Math.min(props.product.max_quantity, Math.max(1, form.quantity + delta)));
const add = () => form.post(route('cart.store'), { preserveScroll: true, onSuccess: () => form.reset('quantity') });
</script>

<template>
    <Head :title="product.name" />

    <StoreLayout>
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
            <nav class="flex items-center gap-1 text-sm text-stone-500" aria-label="Breadcrumb">
                <Link :href="route('shop')" class="hover:text-stone-900">{{ t('nav_shop') }}</Link>
                <ChevronRight class="h-4 w-4 rtl:hidden" /><ChevronLeft class="hidden h-4 w-4 rtl:block" />
                <Link :href="route('shop', { category: product.category.slug })" class="hover:text-stone-900">{{ product.category.name }}</Link>
            </nav>

            <div class="mt-6 grid gap-10 lg:grid-cols-2">
                <div class="group relative aspect-square overflow-hidden rounded-3xl border border-stone-200">
                    <ProductArt :id="product.id" :image="product.image" :emoji="product.emoji" :name="product.name" size="lg" />
                    <span v-if="product.discount" class="absolute start-4 top-4 rounded-full bg-rose-600 px-3 py-1 text-sm font-bold text-white">
                        {{ t('sale') }} <span dir="ltr">−{{ product.discount }}%</span>
                    </span>
                </div>

                <div class="flex flex-col">
                    <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ product.name }}</h1>

                    <div class="mt-4 flex items-baseline gap-3">
                        <span class="text-3xl font-bold tabular-nums text-primary">{{ product.price }}</span>
                        <span v-if="product.compare_at_price" class="text-lg tabular-nums text-stone-400 line-through">{{
                            product.compare_at_price
                        }}</span>
                    </div>

                    <p
                        class="mt-3 text-sm font-medium"
                        :class="!product.in_stock ? 'text-red-600' : product.low_stock ? 'text-amber-700' : 'text-emerald-700'"
                    >
                        {{ !product.in_stock ? t('out_of_stock') : product.low_stock ? t('only_left', { count: product.stock }) : t('in_stock') }}
                    </p>

                    <p v-if="product.description" class="mt-6 leading-relaxed text-stone-600">{{ product.description }}</p>

                    <form v-if="product.in_stock" class="mt-8 flex flex-wrap items-center gap-3" @submit.prevent="add">
                        <div class="flex items-center rounded-full border border-stone-300 bg-white" role="group" :aria-label="t('quantity')">
                            <button
                                type="button"
                                class="flex h-12 w-12 items-center justify-center rounded-full hover:bg-stone-100"
                                aria-label="−"
                                @click="step(-1)"
                            >
                                <Minus class="h-4 w-4" />
                            </button>
                            <span class="w-8 text-center font-semibold tabular-nums">{{ form.quantity }}</span>
                            <button
                                type="button"
                                class="flex h-12 w-12 items-center justify-center rounded-full hover:bg-stone-100"
                                aria-label="+"
                                @click="step(1)"
                            >
                                <Plus class="h-4 w-4" />
                            </button>
                        </div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex h-12 flex-1 items-center justify-center gap-2 rounded-full bg-primary px-8 font-semibold text-white shadow-lg shadow-primary/20 transition hover:bg-primary/90 sm:flex-none"
                        >
                            <LoaderCircle v-if="form.processing" class="h-5 w-5 animate-spin" />
                            <ShoppingBag v-else class="h-5 w-5" />
                            {{ t('add_to_cart') }}
                        </button>
                    </form>

                    <ul class="mt-10 space-y-3 rounded-2xl bg-white p-5 text-sm text-stone-700 ring-1 ring-stone-200">
                        <li class="flex items-center gap-3"><Truck class="h-5 w-5 text-primary" /> {{ t('perk_delivery') }}</li>
                        <li class="flex items-center gap-3"><Banknote class="h-5 w-5 text-primary" /> {{ t('perk_cod') }}</li>
                        <li class="flex items-center gap-3"><RotateCcw class="h-5 w-5 text-primary" /> {{ t('perk_returns') }}</li>
                    </ul>
                </div>
            </div>

            <section v-if="related.length" class="mt-20">
                <h2 class="text-2xl font-bold tracking-tight">{{ t('related') }}</h2>
                <div class="mt-6 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                    <ProductCard v-for="p in related" :key="p.id" :product="p" />
                </div>
            </section>
        </div>
    </StoreLayout>
</template>
