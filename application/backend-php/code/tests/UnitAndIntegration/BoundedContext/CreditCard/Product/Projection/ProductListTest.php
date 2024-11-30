<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\CreditCard\Product\Projection;

use Galeas\Api\BoundedContext\CreditCard\Product\Projection\ProductList\ListProducts;
use Galeas\Api\BoundedContext\CreditCard\Product\Projection\ProductList\ProductListItemProjector;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ResetsEventStoreAndProjectionsIntegrationTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class ProductListTest extends ResetsEventStoreAndProjectionsIntegrationTest
{
    public function testEvents(): void
    {
        $productListProjector = $this->getContainer()
            ->get(ProductListItemProjector::class)
        ;
        $listProducts = $this->getContainer()
            ->get(ListProducts::class)
        ;

        $productDefined = SampleEvents::productDefined();
        $productListProjector->projectIdempotently('test', $productDefined);
        Assert::assertEquals(
            [
                [
                    'id' => $productDefined->aggregateId()->id(),
                    'name' => $productDefined->name(),
                    'isActive' => false,
                    'paymentCycle' => $productDefined->paymentCycle(),
                    'annualFeeInCents' => $productDefined->annualFeeInCents(),
                    'creditLimitInCents' => $productDefined->creditLimitInCents(),
                    'reward' => $productDefined->reward(),
                ],
            ],
            $listProducts->list()
        );

        $productActivated = SampleEvents::productActivated(
            $productDefined->aggregateId(),
            2,
            $productDefined->eventId(),
            $productDefined->eventId()
        );
        $productListProjector->projectIdempotently('test', $productActivated);
        Assert::assertEquals(
            [
                [
                    'id' => $productDefined->aggregateId()->id(),
                    'name' => $productDefined->name(),
                    'isActive' => true,
                    'paymentCycle' => $productDefined->paymentCycle(),
                    'annualFeeInCents' => $productDefined->annualFeeInCents(),
                    'creditLimitInCents' => $productDefined->creditLimitInCents(),
                    'reward' => $productDefined->reward(),
                ],
            ],
            $listProducts->list()
        );

        $productDeactivated = SampleEvents::productDeactivated(
            $productDefined->aggregateId(),
            3,
            $productActivated->eventId(),
            $productDefined->eventId()
        );
        $productListProjector->projectIdempotently('test', $productDeactivated);
        Assert::assertEquals(
            [
                [
                    'id' => $productDefined->aggregateId()->id(),
                    'name' => $productDefined->name(),
                    'isActive' => false,
                    'paymentCycle' => $productDefined->paymentCycle(),
                    'annualFeeInCents' => $productDefined->annualFeeInCents(),
                    'creditLimitInCents' => $productDefined->creditLimitInCents(),
                    'reward' => $productDefined->reward(),
                ],
            ],
            $listProducts->list()
        );
    }
}
