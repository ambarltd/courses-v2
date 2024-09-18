<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject;

class Monthly {
    public static function fromProperties(): self {
        return new self();
    }
}