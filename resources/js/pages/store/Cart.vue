<script setup lang="ts">
import OrderSummary from '@/components/OrderSummary.vue';
import ProductArt from '@/components/ProductArt.vue';
import { useT } from '@/composables/useT';
import StoreLayout from '@/layouts/StoreLayout.vue';
import type { CartLine, CartSummary } from '@/types/store';
import { Head, Link, router } from '@inertiajs/vue3';
import { Minus, Plus, ShoppingBag, Trash2, Truck } from 'lucide-vue-next';

defineProps<{ cart: CartSummary }>();

const { t } = useT();

const setQuantity = (line: CartLine, quantity: number) =>
    router.patch(route('cart.update', line.slug), { quantity }, { preserveScroll: true, preserveState: true });

const remove = (line: CartLine) => router.delete(route('cart.destroy', line.slug), { preserveScroll: true });
</script>

<template>
    <Head :title="t('your_cart')" />

    <StoreLayout>
        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
            <h1 class="text-3xl font-bold tracking-tight">{{ t('your_cart') }}</h1>

            <div v-if="!cart.lines.length" class="mt-16 flex flex-col items-center text-center">
                <span class="flex h-20 w-20 items-center justify-center rounded-full bg-stone-100"
                    ><ShoppingBag class="h-9 w-9 text-stone-400"
                /></span>
                <p class="mt-5 text-lg font-semibold">{{ t('cart_empty') }}</p>
                <p class="mt-1 text-stone-500">{{ t('cart_empty_text') }}</p>
                <Link :href="route('shop')" class="mt-6 rounded-full bg-primary px-6 py-3 font-semibold text-white">{{ t('hero_cta') }}</Link>
            </div>

            <div v-else class="mt-8 grid gap-8 lg:grid-cols-[1fr_22rem]">
                <ul class="divide-y divide-stone-200 rounded-2xl bg-white ring-1 ring-stone-200">
                    <li v-for="line in cart.lines" :key="line.id" class="flex gap-4 p-4 sm:p-5">
                        <Link :href="route('products.show', line.slug)" class="h-24 w-24 shrink-0 overflow-hidden rounded-xl">
                            <ProductArt :id="line.id" :image="line.image" :emoji="line.emoji" :name="line.name" />
                        </Link>
                        <div class="flex min-w-0 flex-1 flex-col">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <Link :href="route('products.show', line.slug)" class="font-semibold hover:text-primary">{{ line.name }}</Link>
                                    <p class="text-sm tabular-nums text-stone-500">{{ line.unit_price }}</p>
                                </div>
                                <p class="font-bold tabular-nums">{{ line.line_total }}</p>
                            </div>
                            <div class="mt-auto flex items-center justify-between pt-3">
                                <div class="flex items-center rounded-full border border-stone-300" role="group" :aria-label="t('quantity')">
                                    <button
                                        type="button"
                                        class="flex h-9 w-9 items-center justify-center rounded-full hover:bg-stone-100"
                                        aria-label="−"
                                        @click="setQuantity(line, line.quantity - 1)"
                                    >
                                        <Minus class="h-4 w-4" />
                                    </button>
                                    <span class="w-8 text-center text-sm font-semibold tabular-nums">{{ line.quantity }}</span>
                                    <button
                                        type="button"
                                        class="flex h-9 w-9 items-center justify-center rounded-full hover:bg-stone-100 disabled:opacity-30"
                                        :disabled="line.quantity >= line.max_quantity"
                                        aria-label="+"
                                        @click="setQuantity(line, line.quantity + 1)"
                                    >
                                        <Plus class="h-4 w-4" />
                                    </button>
                                </div>
                                <button type="button" class="flex items-center gap-1 text-sm text-stone-500 hover:text-red-600" @click="remove(line)">
                                    <Trash2 class="h-4 w-4" /> {{ t('remove') }}
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>

                <aside class="h-fit space-y-5 rounded-2xl bg-white p-6 ring-1 ring-stone-200 lg:sticky lg:top-24">
                    <!-- Free delivery progress -->
                    <div>
                        <p class="flex items-center gap-2 text-sm font-medium">
                            <Truck class="h-4 w-4 text-primary" />
                            {{
                                cart.free_shipping_remaining
                                    ? t('free_shipping_left', { amount: cart.free_shipping_remaining })
                                    : t('free_shipping_unlocked')
                            }}
                        </p>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-stone-100">
                            <div class="h-full rounded-full bg-primary transition-all" :style="{ width: `${cart.free_shipping_progress}%` }" />
                        </div>
                    </div>

                    <OrderSummary :subtotal="cart.subtotal" :shipping="cart.shipping" :total="cart.total" />

                    <Link
                        :href="route('checkout')"
                        class="flex h-12 w-full items-center justify-center rounded-full bg-primary font-semibold text-white shadow-lg shadow-primary/20 hover:bg-primary/90"
                    >
                        {{ t('checkout') }}
                    </Link>
                    <Link :href="route('shop')" class="block text-center text-sm font-medium text-stone-500 hover:text-stone-900">{{
                        t('continue_shopping')
                    }}</Link>
                </aside>
            </div>
        </div>
    </StoreLayout>
</template>
