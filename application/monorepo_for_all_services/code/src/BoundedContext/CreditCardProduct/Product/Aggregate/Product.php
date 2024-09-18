<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCardProduct\Product\Aggregate;

use Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject\PaymentCycle;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject\Reward;
use Galeas\Api\Common\Aggregate\Aggregate;
use Galeas\Api\Common\Aggregate\AggregateTrait;
use Galeas\Api\Common\Id\Id;

class Product implements Aggregate
{
    use AggregateTrait;

    private string $name;
    private int $interestInBasisPoints;
    private int $annualFeeInCents;
    private PaymentCycle $paymentCycle;
    private int $creditLimitInCents;
    private int $maxBalanceTransferAllowedInCents;
    private Reward $reward;

    private string $cardBackgroundHex;

    private bool $isActive;

    public static function fromProperties(
        Id $aggregateId,
        int $aggregateVersion,
        string $name,
        int $interestInBasisPoints, 
        int $annualFeeInCents, 
        PaymentCycle $paymentCycle, 
        int $creditLimitInCents, 
        int $maxBalanceTransferAllowedInCents,
        Reward $reward,
        string $cardBackgroundHex,
        bool $isActive
    ): self {
        $product = new self($aggregateId, $aggregateVersion);
        $product->name = $name;
        $product->interestInBasisPoints = $interestInBasisPoints;
        $product->annualFeeInCents = $annualFeeInCents;
        $product->paymentCycle = $paymentCycle;
        $product->creditLimitInCents = $creditLimitInCents;
        $product->maxBalanceTransferAllowedInCents = $maxBalanceTransferAllowedInCents;
        $product->reward = $reward;
        $product->cardBackgroundHex = $cardBackgroundHex;
        $product->isActive = $isActive;

        return $product;
    }


    public function name(): string
    {
        return $this->name;
    }


    public function interestInBasisPoints(): int
    {
        return $this->interestInBasisPoints;
    }


    public function annualFeeInCents(): int
    {
        return $this->annualFeeInCents;
    }

    public function paymentCycle(): PaymentCycle
    {
        return $this->paymentCycle;
    }


    public function creditLimitInCents(): int
    {
        return $this->creditLimitInCents;
    }

    public function maxBalanceTransferAllowedInCents(): int
    {
        return $this->maxBalanceTransferAllowedInCents;
    }

    public function reward(): Reward
    {
        return $this->reward;
    }


    public function cardBackgroundHex(): string
    {
        return $this->cardBackgroundHex;
    }


    public function isActive(): bool
    {
        return $this->isActive;
    }
}
