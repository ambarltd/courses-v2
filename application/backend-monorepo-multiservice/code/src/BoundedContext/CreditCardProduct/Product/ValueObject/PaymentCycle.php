<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject;

class PaymentCycle {
    private UnknownFrequency|Monthly|Quarterly $frequency;

    public function frequency(): Monthly|Quarterly
    {
        return $this->frequency;
    }
    public static function fromProperties(UnknownFrequency|Monthly|Quarterly $frequency): self {
        $paymentCycle = new self();
        $paymentCycle->frequency = $frequency;

        return $paymentCycle;
    }
}