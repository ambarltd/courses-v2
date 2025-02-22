<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCard\Product\Event;

use Galeas\Api\BoundedContext\CreditCard\Product\Aggregate\Product;
use Galeas\Api\Common\Event\Event;

interface EventCreatedProduct extends Event
{
    public function createProduct(): Product;
}
