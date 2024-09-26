<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\CreditCardProduct\Product\Event;

use Galeas\Api\BoundedContext\CreditCardProduct\Product\Event\ProductDefined;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject\Monthly;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject\PaymentCycle;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject\Points;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject\Reward;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class ProductDefinedTest extends TestCase
{
    public function testNewProductDefinedEvent(): void
    {
        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        $aggregateVersion = 1;
        $causationId = Id::createNew();
        $correlationId = Id::createNew();
        $recordedOn = new \DateTimeImmutable();
        $metadata = ['bla' => 'ble'];
        $name = 'Test Product';
        $interestInBasisPoints = 1_500;
        $annualFeeInCents = 5_000;
        $paymentCycle = 'monthly';
        $creditLimitInCents = 100_000;
        $maxBalanceTransferAllowedInCents = 50_000;
        $reward = 'points';
        $cardBackgroundHex = '#FFFFFF';

        $event = ProductDefined::new(
            $eventId,
            $aggregateId,
            $aggregateVersion,
            $causationId,
            $correlationId,
            $recordedOn,
            $metadata,
            $name,
            $interestInBasisPoints,
            $annualFeeInCents,
            $paymentCycle,
            $creditLimitInCents,
            $maxBalanceTransferAllowedInCents,
            $reward,
            $cardBackgroundHex
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
                $name,
                $interestInBasisPoints,
                $annualFeeInCents,
                $paymentCycle,
                $creditLimitInCents,
                $maxBalanceTransferAllowedInCents,
                $reward,
                $cardBackgroundHex,
            ],
            [
                $event->eventId(),
                $event->aggregateId(),
                $event->aggregateVersion(),
                $event->causationId(),
                $event->correlationId(),
                $event->recordedOn(),
                $event->metadata(),
                $event->name(),
                $event->interestInBasisPoints(),
                $event->annualFeeInCents(),
                $event->paymentCycle(),
                $event->creditLimitInCents(),
                $event->maxBalanceTransferAllowedInCents(),
                $event->reward(),
                $event->cardBackgroundHex(),
            ]
        );
    }

    public function testCreateProduct(): void
    {
        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        $aggregateVersion = 1;
        $causationId = Id::createNew();
        $correlationId = Id::createNew();
        $recordedOn = new \DateTimeImmutable();
        $metadata = [];
        $name = 'Test Product';
        $interestInBasisPoints = 1_500;
        $annualFeeInCents = 5_000;
        $paymentCycle = 'monthly';
        $creditLimitInCents = 100_000;
        $maxBalanceTransferAllowedInCents = 50_000;
        $reward = 'points';
        $cardBackgroundHex = '#FFFFFF';

        $event = ProductDefined::new(
            $eventId,
            $aggregateId,
            $aggregateVersion,
            $causationId,
            $correlationId,
            $recordedOn,
            $metadata,
            $name,
            $interestInBasisPoints,
            $annualFeeInCents,
            $paymentCycle,
            $creditLimitInCents,
            $maxBalanceTransferAllowedInCents,
            $reward,
            $cardBackgroundHex
        );

        $product = $event->createProduct();

        Assert::assertEquals(
            [
                $aggregateId,
                $aggregateVersion,
                $name,
                $interestInBasisPoints,
                $annualFeeInCents,
                PaymentCycle::fromProperties(Monthly::fromProperties()),
                $creditLimitInCents,
                $maxBalanceTransferAllowedInCents,
                Reward::fromProperties(Points::fromProperties()),
                $cardBackgroundHex,
                false,
            ],
            [
                $product->aggregateId(),
                $product->aggregateVersion(),
                $product->name(),
                $product->interestInBasisPoints(),
                $product->annualFeeInCents(),
                $product->paymentCycle(),
                $product->creditLimitInCents(),
                $product->maxBalanceTransferAllowedInCents(),
                $product->reward(),
                $product->cardBackgroundHex(),
                $product->isActive(),
            ]
        );
    }
}
