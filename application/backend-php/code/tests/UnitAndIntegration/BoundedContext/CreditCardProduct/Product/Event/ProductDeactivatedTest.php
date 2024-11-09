<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\CreditCardProduct\Product\Event;

use Galeas\Api\BoundedContext\CreditCardProduct\Product\Aggregate\Product;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\Event\ProductDeactivated;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject\Monthly;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject\PaymentCycle;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject\Points;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject\Reward;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class ProductDeactivatedTest extends TestCase
{
    public function testNewProductActivatedEvent(): void
    {
        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        $aggregateVersion = 2;
        $causationId = Id::createNew();
        $correlationId = Id::createNew();
        $recordedOn = new \DateTimeImmutable();
        $metadata = ['foo' => 'bar'];

        $event = ProductDeactivated::new(
            $eventId,
            $aggregateId,
            $aggregateVersion,
            $causationId,
            $correlationId,
            $recordedOn,
            $metadata
        );

        Assert::assertEquals(
            [
                $eventId,
                $aggregateId,
                $aggregateVersion,
                $causationId,
                $correlationId,
                $recordedOn,
                $metadata,
            ],
            [
                $event->eventId(),
                $event->aggregateId(),
                $event->aggregateVersion(),
                $event->causationId(),
                $event->correlationId(),
                $event->recordedOn(),
                $event->metadata(),
            ]
        );
    }

    public function testTransformProduct(): void
    {
        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        $aggregateVersion = 33;
        $causationId = Id::createNew();
        $correlationId = Id::createNew();
        $recordedOn = new \DateTimeImmutable();
        $metadata = ['foo' => 'bar'];

        $event = ProductDeactivated::new(
            $eventId,
            $aggregateId,
            $aggregateVersion,
            $causationId,
            $correlationId,
            $recordedOn,
            $metadata
        );

        $product = Product::fromProperties(
            $aggregateId,
            1,
            'Test Product',
            1_500,
            5_000,
            PaymentCycle::fromProperties(Monthly::fromProperties()),
            100_000,
            50_000,
            Reward::fromProperties(Points::fromProperties()),
            '#FFFFFF',
            true
        );

        $transformedProduct = $event->transformProduct($product);

        Assert::assertEquals(
            [
                $product->aggregateId(),
                $event->aggregateVersion(),
                $product->name(),
                $product->interestInBasisPoints(),
                $product->annualFeeInCents(),
                $product->paymentCycle(),
                $product->creditLimitInCents(),
                $product->maxBalanceTransferAllowedInCents(),
                $product->reward(),
                $product->cardBackgroundHex(),
                false,
            ],
            [
                $transformedProduct->aggregateId(),
                $transformedProduct->aggregateVersion(),
                $transformedProduct->name(),
                $transformedProduct->interestInBasisPoints(),
                $transformedProduct->annualFeeInCents(),
                $transformedProduct->paymentCycle(),
                $transformedProduct->creditLimitInCents(),
                $transformedProduct->maxBalanceTransferAllowedInCents(),
                $transformedProduct->reward(),
                $transformedProduct->cardBackgroundHex(),
                $transformedProduct->isActive(),
            ]
        );
    }
}
