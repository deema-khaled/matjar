<script setup lang="ts">
import ProductCard from '@/components/ProductCard.vue';
import { useT } from '@/composables/useT';
import StoreLayout from '@/layouts/StoreLayout.vue';
import type { ProductCard as Card, Category } from '@/types/store';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight, Banknote, RotateCcw, Truck } from 'lucide-vue-next';

defineProps<{ categories: Category[]; featured: Card[]; freeShippingOver: string }>();

const { t } = useT();
</script>

<template>
    <Head :title="t('tagline')" />

    <StoreLayout>
        <!-- Hero -->
        <section class="relative overflow-hidden bg-gradient-to-br from-teal-900 via-teal-800 to-emerald-700 text-white">
            <div class="pointer-events-none absolute -end-24 -top-24 h-96 w-96 rounded-full bg-emerald-400/20 blur-3xl" />
            <div class="pointer-events-none absolute -bottom-32 start-10 h-80 w-80 rounded-full bg-teal-300/10 blur-3xl" />

            <div class="relative mx-auto grid max-w-7xl items-center gap-10 px-4 py-16 sm:px-6 md:py-24 lg:grid-cols-2">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-sm font-medium ring-1 ring-white/20">
                        <Truck class="h-4 w-4" /> {{ t('hero_badge', { amount: freeShippingOver }) }}
                    </span>
                    <h1 class="mt-5 text-4xl font-bold leading-tight tracking-tight sm:text-5xl">{{ t('hero_title') }}</h1>
                    <p class="mt-4 max-w-lg text-lg text-teal-50/80">{{ t('hero_text') }}</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <Link
                            :href="route('shop')"
                            class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 font-semibold text-teal-900 shadow-lg transition hover:bg-teal-50"
                        >
                            {{ t('hero_cta') }}
                            <ArrowRight class="h-4 w-4 rtl:hidden" /><ArrowLeft class="hidden h-4 w-4 rtl:block" />
                        </Link>
                        <Link
                            :href="route('orders.track')"
                            class="inline-flex items-center rounded-full px-6 py-3 font-semibold ring-1 ring-white/40 hover:bg-white/10"
                        >
                            {{ t('hero_secondary') }}
                        </Link>
                    </div>
                </div>

                <!-- Floating product tiles -->
                <div class="relative hidden h-80 lg:block" aria-hidden="true">
                    <div
                        class="absolute end-40 top-0 flex h-44 w-44 rotate-[-6deg] items-center justify-center rounded-3xl bg-white/95 text-7xl shadow-2xl"
                    >
                        🎧
                    </div>
                    <div
                        class="absolute end-0 top-10 flex h-40 w-40 rotate-[8deg] items-center justify-center rounded-3xl bg-amber-100 text-7xl shadow-2xl"
                    >
                        👟
                    </div>
                    <div
                        class="absolute bottom-0 end-24 flex h-40 w-40 rotate-[3deg] items-center justify-center rounded-3xl bg-rose-100 text-7xl shadow-2xl"
                    >
                        ⌚
                    </div>
                    <div
                        class="absolute bottom-6 end-64 flex h-28 w-28 rotate-[-10deg] items-center justify-center rounded-3xl bg-teal-100 text-5xl shadow-2xl"
                    >
                        ☕
                    </div>
                </div>
            </div>
        </section>

        <!-- Perks -->
        <section class="border-b border-stone-200 bg-white">
            <ul class="mx-auto grid max-w-7xl gap-4 px-4 py-5 text-sm font-medium text-stone-700 sm:grid-cols-3 sm:px-6">
                <li class="flex items-center gap-3"><Truck class="h-5 w-5 text-primary" /> {{ t('perk_delivery') }}</li>
                <li class="flex items-center gap-3"><Banknote class="h-5 w-5 text-primary" /> {{ t('perk_cod') }}</li>
                <li class="flex items-center gap-3"><RotateCcw class="h-5 w-5 text-primary" /> {{ t('perk_returns') }}</li>
            </ul>
        </section>

        <!-- Categories -->
        <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6">
            <h2 class="text-2xl font-bold tracking-tight">{{ t('categories') }}</h2>
            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                <Link
                    v-for="c in categories"
                    :key="c.id"
                    :href="route('shop', { category: c.slug })"
                    class="group flex flex-col items-center gap-3 rounded-2xl border border-stone-200 bg-white p-5 text-center transition hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-md"
                >
                    <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-stone-100 text-4xl transition group-hover:scale-110">{{
                        c.icon
                    }}</span>
                    <span class="font-semibold">{{ c.name }}</span>
                    <span class="text-xs text-stone-500">{{ t('results', { count: c.count ?? 0 }) }}</span>
                </Link>
            </div>
        </section>

        <!-- Featured -->
        <section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6">
            <div class="flex items-end justify-between">
                <h2 class="text-2xl font-bold tracking-tight">{{ t('featured') }}</h2>
                <Link :href="route('shop')" class="text-sm font-semibold text-primary hover:underline">{{ t('view_all') }}</Link>
            </div>
            <div class="mt-6 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                <ProductCard v-for="p in featured" :key="p.id" :product="p" />
            </div>
        </section>
    </StoreLayout>
</template>
