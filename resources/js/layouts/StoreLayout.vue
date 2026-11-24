<script setup lang="ts">
import { useT } from '@/composables/useT';
import type { SharedProps } from '@/types/store';
import { Link, router, usePage } from '@inertiajs/vue3';
import { AlertCircle, CheckCircle2, Languages, PackageSearch, Search, ShoppingBag, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const page = usePage<SharedProps>();
const { t } = useT();

const search = ref(new URLSearchParams(page.url.split('?')[1] ?? '').get('q') ?? '');
const submitSearch = () => router.get(route('shop'), search.value ? { q: search.value } : {});

const switchLanguage = () => router.post(route('locale', page.props.locale === 'ar' ? 'en' : 'ar'), {}, { preserveScroll: true });

// Toast for flash messages and cart errors.
const toast = computed(() => page.props.errors?.cart ?? page.props.flash?.success ?? null);
const isError = computed(() => Boolean(page.props.errors?.cart));
const hidden = ref(false);
let timer: ReturnType<typeof setTimeout> | undefined;
watch(
    () => [page.props.flash?.success, page.props.errors?.cart],
    () => {
        hidden.value = false;
        clearTimeout(timer);
        timer = setTimeout(() => (hidden.value = true), 4000);
    },
    { immediate: true },
);

const year = new Date().getFullYear();
</script>

<template>
    <div class="flex min-h-screen flex-col bg-stone-50 text-stone-900">
        <header class="sticky top-0 z-40 border-b border-stone-200/80 bg-white/90 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center gap-3 px-4 sm:gap-6 sm:px-6">
                <Link :href="route('home')" class="flex shrink-0 items-center gap-2 text-lg font-bold tracking-tight">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary text-white"><ShoppingBag class="h-5 w-5" /></span>
                    <span class="hidden sm:inline">{{ page.props.store }}</span>
                </Link>

                <nav class="hidden items-center gap-1 text-sm font-medium md:flex">
                    <Link :href="route('shop')" class="rounded-lg px-3 py-2 text-stone-600 hover:bg-stone-100 hover:text-stone-900">{{
                        t('nav_shop')
                    }}</Link>
                    <Link :href="route('orders.track')" class="rounded-lg px-3 py-2 text-stone-600 hover:bg-stone-100 hover:text-stone-900">
                        {{ t('nav_track') }}
                    </Link>
                </nav>

                <form class="relative ms-auto w-full max-w-xs" role="search" @submit.prevent="submitSearch">
                    <Search class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" />
                    <input
                        v-model="search"
                        type="search"
                        :placeholder="t('search_placeholder')"
                        :aria-label="t('search_placeholder')"
                        class="h-10 w-full rounded-full border border-stone-200 bg-stone-50 pe-4 ps-9 text-sm outline-none transition focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20"
                    />
                </form>

                <button
                    type="button"
                    class="flex h-10 shrink-0 items-center gap-1.5 rounded-full px-3 text-sm font-medium text-stone-600 hover:bg-stone-100"
                    @click="switchLanguage"
                >
                    <Languages class="h-4 w-4" /> <span class="hidden sm:inline">{{ t('language') }}</span>
                </button>

                <Link
                    :href="route('cart.show')"
                    class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full hover:bg-stone-100"
                    :aria-label="`${t('cart')} (${page.props.cartCount})`"
                >
                    <ShoppingBag class="h-5 w-5" />
                    <span
                        v-if="page.props.cartCount"
                        class="absolute -end-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1 text-[11px] font-bold text-white"
                    >
                        {{ page.props.cartCount }}
                    </span>
                </Link>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="border-t border-stone-200 bg-white">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-8 text-sm text-stone-500 sm:px-6">
                <div class="flex items-center gap-2">
                    <ShoppingBag class="h-4 w-4 text-primary" />
                    <span>© {{ year }} {{ page.props.store }} · {{ t('footer_text') }}</span>
                </div>
                <Link :href="route('orders.track')" class="flex items-center gap-1 hover:text-stone-900"
                    ><PackageSearch class="h-4 w-4" /> {{ t('nav_track') }}</Link
                >
            </div>
        </footer>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="translate-y-3 opacity-0"
            leave-active-class="transition duration-150"
            leave-to-class="translate-y-3 opacity-0"
        >
            <div
                v-if="toast && !hidden"
                class="fixed bottom-5 start-1/2 z-50 flex w-[calc(100%-2rem)] max-w-md -translate-x-1/2 items-start gap-3 rounded-2xl px-4 py-3 text-sm text-white shadow-xl rtl:translate-x-1/2"
                :class="isError ? 'bg-red-600' : 'bg-stone-900'"
                role="status"
            >
                <AlertCircle v-if="isError" class="mt-0.5 h-4 w-4 shrink-0" />
                <CheckCircle2 v-else class="mt-0.5 h-4 w-4 shrink-0 text-emerald-400" />
                <p class="flex-1">{{ toast }}</p>
                <button type="button" class="opacity-70 hover:opacity-100" aria-label="Close" @click="hidden = true"><X class="h-4 w-4" /></button>
            </div>
        </Transition>
    </div>
</template>
