<?php

return [

    'name' => env('STORE_NAME', 'Matjar'),

    'currency' => [
        'code' => 'JOD',
        'symbol_en' => 'JOD',
        'symbol_ar' => 'د.أ',
        'subunits' => 1000, // 1 JOD = 1000 fils
        'decimals' => 2,
    ],

    // Amounts in fils.
    'shipping' => [
        'flat_rate' => (int) env('STORE_SHIPPING_FLAT', 3000),
        'free_over' => (int) env('STORE_FREE_SHIPPING_OVER', 50000),
    ],

    'max_quantity_per_item' => 10,

    'locales' => ['en', 'ar'],

    'cities' => ['Amman', 'Irbid', 'Zarqa', 'Aqaba', 'Salt', 'Madaba', 'Jerash', 'Karak'],

];
