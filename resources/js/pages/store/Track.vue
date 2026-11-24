<script setup lang="ts">
import OrderDetailsCard from '@/components/OrderDetailsCard.vue';
import { useT } from '@/composables/useT';
import StoreLayout from '@/layouts/StoreLayout.vue';
import type { OrderDetails } from '@/types/store';
import { Head, router } from '@inertiajs/vue3';
import { Check, PackageSearch, SearchX, XCircle } from 'lucide-vue-next';
import { computed, reactive } from 'vue';

const props = defineProps<{
    query: { number: string; phone: string };
    order: OrderDetails | null;
    notFound: boolean;
    timeline: { value: string; label: string }[];
}>();

const { t } = useT();

const form = reactive({ ...props.query });
const submit = () => router.get(route('orders.track'), form, { preserveState: true });

const currentStep = computed(() => props.timeline.findIndex((s) => s.value === props.order?.status));

const input =
    'mt-1.5 block h-11 w-full rounded-xl border border-stone-300 bg-white px-3.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20';
</script>

<template>
    <Head :title="t('track_title')" />

    <StoreLayout>
        <div class="mx-auto max-w-2xl px-4 py-14 sm:px-6">
            <div class="text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/10"
                    ><PackageSearch class="h-7 w-7 text-primary"
                /></span>
                <h1 class="mt-4 text-3xl font-bold tracking-tight">{{ t('track_title') }}</h1>
                <p class="mt-2 text-stone-500">{{ t('track_text') }}</p>
            </div>

            <form
                class="mt-8 grid gap-4 rounded-2xl bg-white p-6 ring-1 ring-stone-200 sm:grid-cols-[1fr_1fr_auto] sm:items-end"
                @submit.prevent="submit"
            >
                <label class="block text-sm font-medium">
                    {{ t('order_number') }}
                    <input v-model="form.number" :class="input" required placeholder="MTJ-XXXXXX" dir="ltr" class="uppercase" />
                </label>
                <label class="block text-sm font-medium">
                    {{ t('phone') }}
                    <input v-model="form.phone" :class="input" type="tel" required dir="ltr" />
                </label>
                <button type="submit" class="h-11 rounded-xl bg-primary px-6 font-semibold text-white hover:bg-primary/90">{{ t('track') }}</button>
            </form>

            <div v-if="notFound" class="mt-6 flex items-center gap-3 rounded-2xl bg-red-50 p-4 text-sm text-red-700 ring-1 ring-red-200">
                <SearchX class="h-5 w-5 shrink-0" /> {{ t('order_not_found') }}
            </div>

            <template v-if="order">
                <!-- Status timeline -->
                <div class="mt-8 rounded-2xl bg-white p-6 ring-1 ring-stone-200">
                    <div v-if="order.status === 'cancelled'" class="flex items-center gap-3 font-semibold text-stone-600">
                        <XCircle class="h-6 w-6 text-stone-400" /> {{ order.status_label }}
                    </div>
                    <ol v-else class="flex items-start">
                        <li v-for="(step, i) in timeline" :key="step.value" class="relative flex flex-1 flex-col items-center text-center">
                            <div
                                v-if="i > 0"
                                class="absolute end-1/2 top-4 h-0.5 w-full -translate-y-1/2"
                                :class="i <= currentStep ? 'bg-primary' : 'bg-stone-200'"
                            />
                            <span
                                class="relative z-10 flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold"
                                :class="i <= currentStep ? 'bg-primary text-white' : 'bg-stone-100 text-stone-400'"
                            >
                                <Check v-if="i < currentStep" class="h-4 w-4" />
                                <template v-else>{{ i + 1 }}</template>
                            </span>
                            <span class="mt-2 text-xs font-medium" :class="i === currentStep ? 'text-stone-900' : 'text-stone-500'">{{
                                step.label
                            }}</span>
                        </li>
                    </ol>
                </div>

                <div class="mt-6"><OrderDetailsCard :order="order" /></div>
            </template>
        </div>
    </StoreLayout>
</template>
