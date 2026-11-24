<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Store Admin',
            'email' => 'admin@matjar.test',
            'is_admin' => true,
        ]);

        foreach ($this->catalog() as $i => [$category, $products]) {
            $cat = Category::create([...$category, 'sort_order' => $i]);

            foreach ($products as [$en, $ar, $emoji, $price, $compareAt, $stock, $featured, $descEn, $descAr]) {
                $cat->products()->create([
                    'name_en' => $en,
                    'name_ar' => $ar,
                    'emoji' => $emoji,
                    'price' => $price * 1000,
                    'compare_at_price' => $compareAt ? $compareAt * 1000 : null,
                    'stock' => $stock,
                    'is_featured' => $featured,
                    'description_en' => $descEn,
                    'description_ar' => $descAr,
                ]);
            }
        }

        $this->seedOrders();
    }

    /** 60 days of orders so the admin dashboard has real-looking charts. */
    private function seedOrders(): void
    {
        $products = Product::all();

        for ($day = 60; $day >= 0; $day--) {
            $count = fake()->numberBetween($day > 30 ? 0 : 1, $day > 30 ? 3 : 6);

            for ($n = 0; $n < $count; $n++) {
                $createdAt = now()->subDays($day)->setTime(fake()->numberBetween(9, 22), fake()->numberBetween(0, 59));
                if ($createdAt->isFuture()) {
                    $createdAt = now()->subMinutes(fake()->numberBetween(5, 300)); // today's orders stay in the past
                }
                $status = match (true) {
                    $day > 7 => fake()->randomElement([OrderStatus::Delivered, OrderStatus::Delivered, OrderStatus::Delivered, OrderStatus::Cancelled]),
                    $day > 2 => fake()->randomElement([OrderStatus::Shipped, OrderStatus::Delivered, OrderStatus::Processing]),
                    default => fake()->randomElement([OrderStatus::Pending, OrderStatus::Pending, OrderStatus::Processing]),
                };

                $lines = $products->random(fake()->numberBetween(1, 3))->map(fn (Product $p) => [
                    'product' => $p,
                    'quantity' => $qty = fake()->numberBetween(1, 2),
                    'line_total' => $p->price * $qty,
                ]);
                $subtotal = $lines->sum('line_total');
                $shipping = $subtotal >= config('store.shipping.free_over') ? 0 : config('store.shipping.flat_rate');

                $order = Order::factory()->status($status)->create([
                    'subtotal' => $subtotal,
                    'shipping' => $shipping,
                    'total' => $subtotal + $shipping,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                foreach ($lines as $line) {
                    $order->items()->create([
                        'product_id' => $line['product']->id,
                        'product_name' => $line['product']->name_en,
                        'unit_price' => $line['product']->price,
                        'quantity' => $line['quantity'],
                        'line_total' => $line['line_total'],
                    ]);
                }
            }
        }
    }

    /** Prices in JOD; converted to fils above. */
    private function catalog(): array
    {
        return [
            [['name_en' => 'Audio', 'name_ar' => 'الصوتيات', 'slug' => 'audio', 'icon' => '🎧'], [
                ['Wireless Headphones Pro', 'سماعات لاسلكية برو', '🎧', 89, 119, 24, true, 'Active noise cancelling, 40-hour battery and fast USB-C charging.', 'عزل نشط للضوضاء، بطارية تدوم 40 ساعة وشحن سريع USB-C.'],
                ['True Wireless Earbuds', 'سماعات أذن لاسلكية', '🎶', 39, null, 40, false, 'Compact earbuds with a pocket-size charging case.', 'سماعات صغيرة مع علبة شحن بحجم الجيب.'],
                ['Bluetooth Speaker Mini', 'سماعة بلوتوث صغيرة', '🔊', 25, 32, 3, false, 'Waterproof speaker with deep bass for outdoor trips.', 'سماعة مقاومة للماء بصوت جهير عميق للرحلات.'],
                ['Studio Microphone', 'ميكروفون استوديو', '🎙️', 65, null, 12, false, 'USB condenser mic for podcasts, calls and streaming.', 'ميكروفون USB للبودكاست والمكالمات والبث المباشر.'],
            ]],
            [['name_en' => 'Shoes', 'name_ar' => 'الأحذية', 'slug' => 'shoes', 'icon' => '👟'], [
                ['Everyday Running Shoes', 'حذاء جري يومي', '👟', 55, 70, 30, true, 'Lightweight cushioning made for daily runs and long walks.', 'توسيد خفيف مصمم للجري اليومي والمشي الطويل.'],
                ['Classic Leather Sneakers', 'حذاء رياضي جلد كلاسيك', '👞', 48, null, 18, false, 'Clean white leather sneakers that go with everything.', 'حذاء جلد أبيض أنيق يناسب كل الإطلالات.'],
                ['Hiking Boots', 'حذاء هايكنج', '🥾', 79, null, 9, false, 'Grippy, water-resistant boots for trails around Jordan.', 'حذاء متين ومقاوم للماء لمسارات المشي في الأردن.'],
                ['Comfort Slides', 'شبشب مريح', '🩴', 12, 15, 0, false, 'Soft foam slides for home and the beach.', 'شبشب من الإسفنج الناعم للبيت والبحر.'],
            ]],
            [['name_en' => 'Watches', 'name_ar' => 'الساعات', 'slug' => 'watches', 'icon' => '⌚'], [
                ['Smart Watch Series 5', 'ساعة ذكية سلسلة 5', '⌚', 129, 149, 15, true, 'Heart rate, sleep tracking and 7-day battery life.', 'قياس نبض القلب وتتبع النوم وبطارية تدوم 7 أيام.'],
                ['Minimal Analog Watch', 'ساعة أنالوج بسيطة', '🕰️', 45, null, 20, false, 'A slim stainless steel watch with a leather strap.', 'ساعة نحيفة من الستانلس ستيل بسوار جلد.'],
                ['Kids Fitness Band', 'سوار رياضي للأطفال', '📿', 22, null, 4, false, 'Step counter and reminders, built for small wrists.', 'عداد خطوات وتنبيهات مصمم لمعاصم الأطفال.'],
            ]],
            [['name_en' => 'Bags', 'name_ar' => 'الحقائب', 'slug' => 'bags', 'icon' => '👜'], [
                ['Laptop Backpack', 'حقيبة ظهر للابتوب', '🎒', 42, 55, 25, true, 'Padded 15" laptop sleeve, USB port and hidden pocket.', 'جيب مبطن للابتوب 15 إنش، منفذ USB وجيب مخفي.'],
                ['Leather Tote Bag', 'حقيبة يد جلد', '👜', 59, null, 10, false, 'Roomy everyday tote in soft genuine leather.', 'حقيبة يومية واسعة من الجلد الطبيعي الناعم.'],
                ['Travel Duffel', 'حقيبة سفر', '🧳', 49, null, 7, false, 'Weekend bag with a separate shoe compartment.', 'حقيبة لعطلة نهاية الأسبوع مع جيب منفصل للأحذية.'],
            ]],
            [['name_en' => 'Home', 'name_ar' => 'المنزل', 'slug' => 'home', 'icon' => '🏠'], [
                ['Scented Candle Set', 'طقم شموع معطرة', '🕯️', 18, 24, 35, true, 'Three soy candles: oud, jasmine and vanilla.', 'ثلاث شموع صويا: عود وياسمين وفانيلا.'],
                ['Ceramic Plant Pot', 'أصيص نباتات سيراميك', '🪴', 14, null, 22, false, 'Hand-glazed pot with drainage hole and saucer.', 'أصيص مطلي يدويًا مع فتحة تصريف وصحن.'],
                ['Cotton Throw Blanket', 'بطانية قطن', '🛋️', 29, null, 14, false, 'Soft woven blanket for sofa evenings.', 'بطانية قطنية ناعمة لسهرات الكنبة.'],
                ['Desk Lamp LED', 'مصباح مكتب LED', '💡', 27, 35, 2, false, 'Dimmable warm-to-cool light with USB charging port.', 'إضاءة قابلة للتعتيم من الدافئ للبارد مع منفذ شحن USB.'],
            ]],
            [['name_en' => 'Coffee', 'name_ar' => 'القهوة', 'slug' => 'coffee', 'icon' => '☕'], [
                ['Arabic Coffee with Cardamom', 'قهوة عربية بالهيل', '☕', 8, null, 60, true, '500g of lightly roasted beans ground with cardamom.', '500 غرام بن محمص خفيف مطحون مع الهيل.'],
                ['Pour-Over Kit', 'طقم تقطير القهوة', '🫖', 34, 42, 11, false, 'Glass dripper, filters and a gooseneck kettle.', 'مقطرة زجاجية وفلاتر وغلاية بفوهة رفيعة.'],
                ['Espresso Cups (Set of 4)', 'فناجين إسبريسو (4 قطع)', '🍵', 16, null, 19, false, 'Double-wall glass cups that keep coffee hot.', 'فناجين زجاج مزدوج تحافظ على حرارة القهوة.'],
            ]],
        ];
    }
}
