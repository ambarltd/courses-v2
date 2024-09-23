<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCardProduct\Product\Command;

class DefineProductCommand
{
    public string $productIdentifierForAggregateIdHash;
    public string $name;
    public int $interestInBasisPoints;
    public int $annualFeeInCents;
    public string $paymentCycle;
    public int $creditLimitInCents;
    public int $maxBalanceTransferAllowedInCents;
    public string $reward;
    public string $cardBackgroundHex;
}
