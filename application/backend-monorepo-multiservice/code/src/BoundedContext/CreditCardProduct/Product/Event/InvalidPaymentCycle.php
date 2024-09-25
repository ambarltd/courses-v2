<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCardProduct\Product\Event;

use Galeas\Api\CommonException\BadRequestException;

class InvalidPaymentCycle extends BadRequestException
{
    public static function getErrorIdentifier(): string
    {
        return 'CreditCardProduct_Product_DefineProduct_InvalidPaymentCycle';
    }
}
