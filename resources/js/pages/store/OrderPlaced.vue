<script setup lang="ts">
import OrderDetailsCard from '@/components/OrderDetailsCard.vue';
import { useT } from '@/composables/useT';
import StoreLayout from '@/layouts/StoreLayout.vue';
import type { OrderDetails } from '@/types/store';
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2 } from 'lucide-vue-next';

defineProps<{ order: OrderDetails }>();

const { t } = useT();
</script>

<template>
    <Head :title="order.number" />

    <StoreLayout>
        <div class="mx-auto max-w-2xl px-4 py-14 sm:px-6">
            <div class="text-center">
                <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100">
                    <CheckCircle2 class="h-9 w-9 text-emerald-600" />
                </span>
                <h1 class="mt-5 text-3xl font-bold tracking-tight">{{ t('thank_you', { name: order.customer_name.split(' ')[0] }) }}</h1>
                <p class="mt-2 text-lg">{{ t('order_placed', { number: order.number }) }}</p>
                <p class="mt-2 text-stone-500">{{ t('order_placed_text', { phone: order.phone }) }}</p>
            </div>

            <div class="mt-10"><OrderDetailsCard :order="order" /></div>

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <Link
                    :href="route('orders.track', { number: order.number, phone: order.phone })"
                    class="rounded-full bg-primary px-6 py-3 font-semibold text-white hover:bg-primary/90"
                >
                    {{ t('track_this_order') }}
                </Link>
                <Link :href="route('shop')" class="rounded-full px-6 py-3 font-semibold ring-1 ring-stone-300 hover:bg-white">{{
                    t('continue_shopping')
                }}</Link>
            </div>
        </div>
    </StoreLayout>
</template>
