<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCardProduct\Product\Event;

use Galeas\Api\BoundedContext\CreditCardProduct\Product\Aggregate\Product;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject\Cashback;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject\Monthly;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject\PaymentCycle;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject\Points;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject\Quarterly;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject\Reward;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject\UnknownFrequency;
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
    private float $maxBalanceTransferAllowedInCents;
    private string $reward;
    private string $cardBackgroundHex;

    public static function new(
        Id                 $eventId,
        Id                 $aggregateId,
        int                $aggregateVersion,
        Id                 $causationId,
        Id                 $correlationId,
        \DateTimeImmutable $recordedOn,
        array              $metadata,
        string             $name,
        int                $interestInBasisPoints,
        int                $annualFeeInCents,
        string             $paymentCycle,
        int                $creditLimitInCents,
        float              $maxBalanceTransferAllowedInCents,
        string             $reward,
        string             $cardBackgroundHex,
    ): ProductDefined
    {
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


    public function createProduct(): Product
    {
        $paymentCycle = match ($this->paymentCycle) {
            "monthly" => PaymentCycle::fromProperties(
                Monthly::fromProperties()
            ),
            "quarterly" => PaymentCycle::fromProperties(
                Quarterly::fromProperties()
            ),
            default => PaymentCycle::fromProperties(
                UnknownFrequency::fromProperties()
            ),
        };
        $reward = match ($this->reward) {
            "points" => Reward::fromProperties(
                Points::fromProperties()
            ),
            "cashback" => Reward::fromProperties(
                Cashback::fromProperties()
            ),
            default => PaymentCycle::fromProperties(
                UnknownFrequency::fromProperties()
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
