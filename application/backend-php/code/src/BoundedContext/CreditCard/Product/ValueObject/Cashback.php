<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCard\Product\ValueObject;

class Cashback
{
    public static function fromProperties(): self
    {
        return new self();
    }
}
