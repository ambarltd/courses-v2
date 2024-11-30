<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCard\Product\Event;

use Galeas\Api\CommonException\BadRequestException;

class InvalidPaymentCycle extends BadRequestException
{
    public static function getErrorIdentifier(): string
    {
        return 'CreditCard_Product_DefineProduct_InvalidPaymentCycle';
    }
}
