<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCard\Product\Event;

use Galeas\Api\BoundedContext\CreditCard\Product\Aggregate\Product;
use Galeas\Api\BoundedContext\CreditCard\Product\ValueObject\Cashback;
use Galeas\Api\BoundedContext\CreditCard\Product\ValueObject\Monthly;
use Galeas\Api\BoundedContext\CreditCard\Product\ValueObject\NoReward;
use Galeas\Api\BoundedContext\CreditCard\Product\ValueObject\PaymentCycle;
use Galeas\Api\BoundedContext\CreditCard\Product\ValueObject\Points;
use Galeas\Api\BoundedContext\CreditCard\Product\ValueObject\Quarterly;
use Galeas\Api\BoundedContext\CreditCard\Product\ValueObject\Reward;
use Galeas\Api\BoundedContext\CreditCard\Product\ValueObject\UnknownFrequency;
use Galeas\Api\Common\Event\EventTrait;
use Galeas\Api\Common\Id\Id;

class ProductDefined implements EventCreatedProduct
{
    use EventTrait;

    private string $name;
    private int $interestInBasisPoints;
    private int $annualFeeInCents;
    private string $paymentCycle;
    private int $creditLimitInCents;
    private int $maxBalanceTransferAllowedInCents;
    private string $reward;
    private string $cardBackgroundHex;

    /**
     * @param array<string,mixed> $metadata
     */
    public static function new(
        Id $eventId,
        Id $aggregateId,
        int $aggregateVersion,
        Id $causationId,
        Id $correlationId,
        \DateTimeImmutable $recordedOn,
        array $metadata,
        string $name,
        int $interestInBasisPoints,
        int $annualFeeInCents,
        string $paymentCycle,
        int $creditLimitInCents,
        int $maxBalanceTransferAllowedInCents,
        string $reward,
        string $cardBackgroundHex,
    ): self {
        $event = new self(
            $eventId,
            $aggregateId,
            $aggregateVersion,
            $causationId,
            $correlationId,
            $recordedOn,
            $metadata
        );
        $event->name = $name;
        $event->interestInBasisPoints = $interestInBasisPoints;
        $event->annualFeeInCents = $annualFeeInCents;
        $event->paymentCycle = $paymentCycle;
        $event->creditLimitInCents = $creditLimitInCents;
        $event->maxBalanceTransferAllowedInCents = $maxBalanceTransferAllowedInCents;
        $event->reward = $reward;
        $event->cardBackgroundHex = $cardBackgroundHex;

        return $event;
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

    public function paymentCycle(): string
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

    public function reward(): string
    {
        return $this->reward;
    }

    public function cardBackgroundHex(): string
    {
        return $this->cardBackgroundHex;
    }

    public function createProduct(): Product
    {
        $paymentCycle = match ($this->paymentCycle) {
            'monthly' => PaymentCycle::fromProperties(
                Monthly::fromProperties()
            ),
            'quarterly' => PaymentCycle::fromProperties(
                Quarterly::fromProperties()
            ),
            default => PaymentCycle::fromProperties(
                UnknownFrequency::fromProperties()
            ),
        };
        $reward = match ($this->reward) {
            'points' => Reward::fromProperties(
                Points::fromProperties()
            ),
            'cashback' => Reward::fromProperties(
                Cashback::fromProperties()
            ),
            default => Reward::fromProperties(
                NoReward::fromProperties()
            ),
        };

        return Product::fromProperties(
            $this->aggregateId,
            $this->aggregateVersion,
            $this->name,
            $this->interestInBasisPoints,
            $this->annualFeeInCents,
            $paymentCycle,
            $this->creditLimitInCents,
            $this->maxBalanceTransferAllowedInCents,
            $reward,
            $this->cardBackgroundHex,
            false
        );
    }
}
