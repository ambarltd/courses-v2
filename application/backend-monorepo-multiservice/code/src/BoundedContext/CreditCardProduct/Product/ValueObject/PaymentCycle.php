<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject;

class PaymentCycle
{
    private Monthly|Quarterly|UnknownFrequency $frequency;

    public function frequency(): Monthly|Quarterly|UnknownFrequency
    {
        return $this->frequency;
    }

    public static function fromProperties(Monthly|Quarterly|UnknownFrequency $frequency): self
    {
        $paymentCycle = new self();
        $paymentCycle->frequency = $frequency;

        return $paymentCycle;
    }
}
