<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import OrderSummary from '@/components/OrderSummary.vue';
import ProductArt from '@/components/ProductArt.vue';
import { useT } from '@/composables/useT';
import StoreLayout from '@/layouts/StoreLayout.vue';
import type { CartSummary } from '@/types/store';
import { Head, useForm } from '@inertiajs/vue3';
import { Banknote, LoaderCircle, Lock } from 'lucide-vue-next';

const props = defineProps<{ cart: CartSummary; cities: string[] }>();

const { t } = useT();

const form = useForm({
    customer_name: '',
    phone: '',
    email: '',
    city: props.cities[0],
    address: '',
    notes: '',
});

const submit = () => form.post(route('checkout.store'));

const input =
    'mt-1.5 block h-11 w-full rounded-xl border border-stone-300 bg-white px-3.5 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20';
</script>

<template>
    <Head :title="t('checkout')" />

    <StoreLayout>
        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
            <h1 class="text-3xl font-bold tracking-tight">{{ t('checkout') }}</h1>

            <form class="mt-8 grid gap-8 lg:grid-cols-[1fr_24rem]" @submit.prevent="submit">
                <div class="space-y-6">
                    <section class="rounded-2xl bg-white p-6 ring-1 ring-stone-200">
                        <h2 class="text-lg font-semibold">{{ t('delivery_details') }}</h2>
                        <div class="mt-5 grid gap-5 sm:grid-cols-2">
                            <label class="block text-sm font-medium">
                                {{ t('full_name') }}
                                <input v-model="form.customer_name" :class="input" required autocomplete="name" />
                                <InputError :message="form.errors.customer_name" />
                            </label>
                            <label class="block text-sm font-medium">
                                {{ t('phone') }}
                                <input
                                    v-model="form.phone"
                                    :class="input"
                                    type="tel"
                                    required
                                    autocomplete="tel"
                                    dir="ltr"
                                    placeholder="+962 7X XXX XXXX"
                                />
                                <InputError :message="form.errors.phone" />
                            </label>
                            <label class="block text-sm font-medium sm:col-span-2">
                                {{ t('email_optional') }}
                                <input v-model="form.email" :class="input" type="email" autocomplete="email" dir="ltr" />
                                <InputError :message="form.errors.email" />
                            </label>
                            <label class="block text-sm font-medium">
                                {{ t('city') }}
                                <select v-model="form.city" :class="input">
                                    <option v-for="c in cities" :key="c" :value="c">{{ c }}</option>
                                </select>
                                <InputError :message="form.errors.city" />
                            </label>
                            <label class="block text-sm font-medium">
                                {{ t('address') }}
                                <input
                                    v-model="form.address"
                                    :class="input"
                                    required
                                    autocomplete="street-address"
                                    :placeholder="t('address_placeholder')"
                                />
                                <InputError :message="form.errors.address" />
                            </label>
                            <label class="block text-sm font-medium sm:col-span-2">
                                {{ t('notes_optional') }}
                                <textarea
                                    v-model="form.notes"
                                    rows="3"
                                    class="mt-1.5 block w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                                />
                                <InputError :message="form.errors.notes" />
                            </label>
                        </div>
                    </section>

                    <section class="rounded-2xl bg-white p-6 ring-1 ring-stone-200">
                        <h2 class="text-lg font-semibold">{{ t('payment') }}</h2>
                        <div class="mt-4 flex items-center gap-4 rounded-xl border-2 border-primary bg-primary/5 p-4">
                            <Banknote class="h-6 w-6 text-primary" />
                            <div>
                                <p class="font-semibold">{{ t('cod') }}</p>
                                <p class="text-sm text-stone-500">{{ t('cod_text') }}</p>
                            </div>
                        </div>
                    </section>
                </div>

                <aside class="h-fit space-y-5 rounded-2xl bg-white p-6 ring-1 ring-stone-200 lg:sticky lg:top-24">
                    <h2 class="text-lg font-semibold">{{ t('order_summary') }}</h2>
                    <ul class="space-y-3">
                        <li v-for="line in cart.lines" :key="line.id" class="flex items-center gap-3 text-sm">
                            <span class="relative h-12 w-12 shrink-0 overflow-hidden rounded-lg">
                                <ProductArt :id="line.id" :image="line.image" :emoji="line.emoji" :name="line.name" />
                            </span>
                            <span class="min-w-0 flex-1 truncate"
                                >{{ line.name }} <span class="text-stone-400">× {{ line.quantity }}</span></span
                            >
                            <span class="font-medium tabular-nums">{{ line.line_total }}</span>
                        </li>
                    </ul>
                    <OrderSummary :subtotal="cart.subtotal" :shipping="cart.shipping" :total="cart.total" />
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex h-12 w-full items-center justify-center gap-2 rounded-full bg-primary font-semibold text-white shadow-lg shadow-primary/20 hover:bg-primary/90 disabled:opacity-70"
                    >
                        <LoaderCircle v-if="form.processing" class="h-5 w-5 animate-spin" />
                        <Lock v-else class="h-4 w-4" />
                        {{ t('place_order') }} · {{ cart.total }}
                    </button>
                </aside>
            </form>
        </div>
    </StoreLayout>
</template>
