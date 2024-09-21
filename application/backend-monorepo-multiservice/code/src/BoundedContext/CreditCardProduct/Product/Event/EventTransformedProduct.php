<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCardProduct\Product\Event;

use Galeas\Api\BoundedContext\CreditCardProduct\Product\Aggregate\Product;
use Galeas\Api\Common\Event\Event;

interface EventTransformedProduct extends Event
{
    public function transformProduct(Product $product): Product;
}
