<script setup lang="ts">
import ProductCard from '@/components/ProductCard.vue';
import { useT } from '@/composables/useT';
import StoreLayout from '@/layouts/StoreLayout.vue';
import type { ProductCard as Card, Category, Paginated } from '@/types/store';
import { Head, Link, router } from '@inertiajs/vue3';
import { PackageX } from 'lucide-vue-next';
import { reactive, watch } from 'vue';

const props = defineProps<{
    products: Paginated<Card>;
    categories: Category[];
    activeCategory: Category | null;
    filters: { q: string; category: string | null; sort: string; in_stock: boolean };
}>();

const { t } = useT();

const state = reactive({ sort: props.filters.sort, in_stock: props.filters.in_stock });

const visit = (overrides: Record<string, unknown> = {}) => {
    const params: Record<string, unknown> = {
        q: props.filters.q || undefined,
        category: props.filters.category || undefined,
        sort: state.sort !== 'newest' ? state.sort : undefined,
        in_stock: state.in_stock ? 1 : undefined,
        ...overrides,
    };
    router.get(route('shop'), params, { preserveScroll: true, preserveState: true });
};

watch(state, () => visit());
</script>

<template>
    <Head :title="activeCategory?.name ?? t('all_products')" />

    <StoreLayout>
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        <span v-if="activeCategory">{{ activeCategory.icon }} {{ activeCategory.name }}</span>
                        <span v-else>{{ t('all_products') }}</span>
                    </h1>
                    <p class="mt-1 text-sm text-stone-500">
                        {{ t('results', { count: products.total }) }}<template v-if="filters.q"> · “{{ filters.q }}”</template>
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <label class="flex cursor-pointer items-center gap-2 rounded-full border border-stone-200 bg-white px-3 py-2">
                        <input v-model="state.in_stock" type="checkbox" class="rounded border-stone-300 text-primary focus:ring-primary" />
                        {{ t('in_stock_only') }}
                    </label>
                    <select
                        v-model="state.sort"
                        class="rounded-full border-stone-200 bg-white py-2 pe-8 ps-4 text-sm focus:border-primary focus:ring-primary"
                        aria-label="Sort"
                    >
                        <option value="newest">{{ t('sort_newest') }}</option>
                        <option value="price_asc">{{ t('sort_price_asc') }}</option>
                        <option value="price_desc">{{ t('sort_price_desc') }}</option>
                    </select>
                </div>
            </div>

            <!-- Category chips -->
            <div class="-mx-4 mt-6 flex gap-2 overflow-x-auto px-4 pb-2 sm:mx-0 sm:flex-wrap sm:px-0">
                <button
                    type="button"
                    class="shrink-0 rounded-full px-4 py-2 text-sm font-medium transition"
                    :class="!filters.category ? 'bg-stone-900 text-white' : 'border border-stone-200 bg-white hover:border-stone-400'"
                    @click="visit({ category: undefined, page: undefined })"
                >
                    {{ t('all_products') }}
                </button>
                <button
                    v-for="c in categories"
                    :key="c.id"
                    type="button"
                    class="shrink-0 rounded-full px-4 py-2 text-sm font-medium transition"
                    :class="filters.category === c.slug ? 'bg-stone-900 text-white' : 'border border-stone-200 bg-white hover:border-stone-400'"
                    @click="visit({ category: c.slug, page: undefined })"
                >
                    {{ c.icon }} {{ c.name }}
                </button>
            </div>

            <div v-if="products.data.length" class="mt-8 grid grid-cols-2 gap-4 sm:gap-6 md:grid-cols-3 lg:grid-cols-4">
                <ProductCard v-for="p in products.data" :key="p.id" :product="p" />
            </div>

            <div v-else class="mt-16 flex flex-col items-center text-center">
                <PackageX class="h-12 w-12 text-stone-300" />
                <p class="mt-4 font-semibold">{{ t('no_results') }}</p>
                <Link :href="route('shop')" class="mt-4 rounded-full bg-primary px-5 py-2 text-sm font-semibold text-white">{{
                    t('clear_filters')
                }}</Link>
            </div>

            <nav v-if="products.last_page > 1" class="mt-10 flex flex-wrap justify-center gap-1" aria-label="Pagination">
                <template v-for="link in products.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        preserve-scroll
                        class="min-w-10 rounded-full px-3 py-2 text-center text-sm font-medium"
                        :class="link.active ? 'bg-stone-900 text-white' : 'bg-white ring-1 ring-stone-200 hover:ring-stone-400'"
                    >
                        <span v-html="link.label" />
                    </Link>
                    <span v-else class="min-w-10 rounded-full px-3 py-2 text-center text-sm text-stone-300" v-html="link.label" />
                </template>
            </nav>
        </div>
    </StoreLayout>
</template>
