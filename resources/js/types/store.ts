import type { PageProps } from '@inertiajs/core';

export type SharedProps = PageProps & {
    store: string;
    locale: 'en' | 'ar';
    dir: 'ltr' | 'rtl';
    translations: Record<string, string>;
    cartCount: number;
    flash: { success: string | null };
    errors: Record<string, string>;
};

export interface Category {
    id: number;
    slug: string;
    name: string;
    icon: string;
    count?: number;
}

export interface ProductCard {
    id: number;
    slug: string;
    name: string;
    price: string;
    compare_at_price: string | null;
    discount: number | null;
    image: string | null;
    emoji: string | null;
    in_stock: boolean;
    low_stock: number | null;
    category: string | null;
}

export interface CartLine extends ProductCard {
    quantity: number;
    max_quantity: number;
    unit_price: string;
    line_total: string;
}

export interface CartSummary {
    lines: CartLine[];
    count: number;
    subtotal: string;
    shipping: string | null;
    total: string;
    free_shipping_remaining: string | null;
    free_shipping_progress: number;
}

export interface OrderDetails {
    number: string;
    status: string;
    status_label: string;
    customer_name: string;
    phone: string;
    city: string;
    address: string;
    placed_on: string;
    items: { name: string; quantity: number; line_total: string }[];
    subtotal: string;
    shipping: string | null;
    total: string;
}

export interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
    current_page: number;
    last_page: number;
}
