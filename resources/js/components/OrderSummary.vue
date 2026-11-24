<script setup lang="ts">
import { useT } from '@/composables/useT';

defineProps<{ subtotal: string; shipping: string | null; total: string }>();

const { t } = useT();
</script>

<template>
    <dl class="space-y-3 text-sm">
        <div class="flex justify-between">
            <dt class="text-stone-500">{{ t('subtotal') }}</dt>
            <dd class="font-medium tabular-nums">{{ subtotal }}</dd>
        </div>
        <div class="flex justify-between">
            <dt class="text-stone-500">{{ t('shipping') }}</dt>
            <dd class="font-medium tabular-nums" :class="shipping ? '' : 'text-emerald-700'">{{ shipping ?? t('free') }}</dd>
        </div>
        <div class="flex justify-between border-t border-stone-200 pt-3 text-base">
            <dt class="font-semibold">{{ t('total') }}</dt>
            <dd class="font-bold tabular-nums">{{ total }}</dd>
        </div>
    </dl>
</template>
