import type { SharedProps } from '@/types/store';
import { usePage } from '@inertiajs/vue3';

/**
 * t('store_key', { count: 3 }) -> translated string from lang/{en,ar}/store.php,
 * with Laravel-style :placeholders replaced.
 */
export function useT() {
    const page = usePage<SharedProps>();

    const t = (key: string, replace: Record<string, string | number> = {}): string => {
        let text = page.props.translations?.[key] ?? key;
        for (const [name, value] of Object.entries(replace)) {
            text = text.replaceAll(`:${name}`, String(value));
        }
        return text;
    };

    return { t };
}
