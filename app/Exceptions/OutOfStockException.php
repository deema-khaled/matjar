<?php

namespace App\Exceptions;

use App\Models\Product;
use RuntimeException;

class OutOfStockException extends RuntimeException
{
    public function __construct(public readonly ?Product $product, public readonly int $available)
    {
        parent::__construct(
            $product
                ? __('store.out_of_stock_line', ['product' => $product->translated('name'), 'count' => $available])
                : __('store.product_unavailable'),
        );
    }
}
