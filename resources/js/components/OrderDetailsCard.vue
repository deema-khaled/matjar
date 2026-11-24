<script setup lang="ts">
import OrderSummary from '@/components/OrderSummary.vue';
import { useT } from '@/composables/useT';
import type { OrderDetails } from '@/types/store';
import { MapPin, Phone } from 'lucide-vue-next';

defineProps<{ order: OrderDetails }>();

const { t } = useT();
</script>

<template>
    <div class="rounded-2xl bg-white p-6 ring-1 ring-stone-200">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <p class="text-sm text-stone-500">{{ t('order_number') }}</p>
                <p class="font-mono text-lg font-bold tracking-wider" dir="ltr">{{ order.number }}</p>
            </div>
            <p class="text-sm text-stone-500">{{ t('placed_on', { date: order.placed_on }) }}</p>
        </div>

        <div class="mt-4 grid gap-2 text-sm text-stone-600 sm:grid-cols-2">
            <p class="flex items-center gap-2"><MapPin class="h-4 w-4 text-stone-400" /> {{ order.city }}, {{ order.address }}</p>
            <p class="flex items-center gap-2">
                <Phone class="h-4 w-4 text-stone-400" /> <span dir="ltr">{{ order.phone }}</span>
            </p>
        </div>

        <h3 class="mt-6 text-sm font-semibold text-stone-500">{{ t('items') }}</h3>
        <ul class="mt-2 divide-y divide-stone-100 text-sm">
            <li v-for="(item, i) in order.items" :key="i" class="flex justify-between gap-3 py-2">
                <span
                    >{{ item.name }} <span class="text-stone-400">× {{ item.quantity }}</span></span
                >
                <span class="font-medium tabular-nums">{{ item.line_total }}</span>
            </li>
        </ul>

        <div class="mt-4 border-t border-stone-200 pt-4">
            <OrderSummary :subtotal="order.subtotal" :shipping="order.shipping" :total="order.total" />
        </div>
    </div>
</template>
