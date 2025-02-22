<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCard\Product\Event;

use Galeas\Api\BoundedContext\CreditCard\Product\Aggregate\Product;
use Galeas\Api\Common\Event\EventTrait;
use Galeas\Api\Common\Id\Id;

class ProductActivated implements EventTransformedProduct
{
    use EventTrait;

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
    ): self {
        return new self(
            $eventId,
            $aggregateId,
            $aggregateVersion,
            $causationId,
            $correlationId,
            $recordedOn,
            $metadata
        );
    }

    public function transformProduct(Product $product): Product
    {
        return Product::fromProperties(
            $product->aggregateId(),
            $this->aggregateVersion(),
            $product->name(),
            $product->interestInBasisPoints(),
            $product->annualFeeInCents(),
            $product->paymentCycle(),
            $product->creditLimitInCents(),
            $product->maxBalanceTransferAllowedInCents(),
            $product->reward(),
            $product->cardBackgroundHex(),
            true
        );
    }
}
