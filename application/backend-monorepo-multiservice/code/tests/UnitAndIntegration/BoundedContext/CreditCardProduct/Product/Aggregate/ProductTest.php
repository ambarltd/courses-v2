<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\CreditCardProduct\Product\Aggregate;

use Galeas\Api\BoundedContext\CreditCardProduct\Product\Aggregate\Product;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject\Monthly;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject\NoReward;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject\PaymentCycle;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject\Reward;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTest;

class ProductTest extends UnitTest
{
    public function testCreate(): void
    {
        $productId = Id::createNew();
        $product = Product::fromProperties(
            $productId,
            1,
            'name',
            1_200,
            5_000,
            PaymentCycle::fromProperties(
                Monthly::fromProperties()
            ),
            150_000,
            10_000,
            Reward::fromProperties(
                NoReward::fromProperties()
            ),
            '#ff00ff',
            false
        );

        Assert::assertEquals(
            [
                $productId,
                1,
                'name',
                1_200,
                5_000,
                PaymentCycle::fromProperties(
                    Monthly::fromProperties()
                ),
                150_000,
                10_000,
                Reward::fromProperties(
                    NoReward::fromProperties()
                ),
                '#ff00ff',
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
