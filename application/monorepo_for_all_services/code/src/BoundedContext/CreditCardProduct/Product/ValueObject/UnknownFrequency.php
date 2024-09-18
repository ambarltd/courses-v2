<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject;

class UnknownFrequency {
    public static function fromProperties(): self {
        return new self();
    }
}