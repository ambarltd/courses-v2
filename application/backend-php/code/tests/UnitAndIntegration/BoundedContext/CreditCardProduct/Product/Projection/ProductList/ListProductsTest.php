<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\CreditCardProduct\Product\Projection\ProductList;

use Galeas\Api\BoundedContext\CreditCardProduct\Product\Projection\ProductList\ListProducts;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\Projection\ProductList\ProductListItem;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ResetsEventStoreAndProjectionsIntegrationTest;

class ListProductsTest extends ResetsEventStoreAndProjectionsIntegrationTest
{
    public function testListProducts(): void
    {
        /** @var ListProducts $listProductsService */
        $listProductsService = $this->getContainer()
            ->get(ListProducts::class)
        ;

        Assert::assertEquals(
            [],
            $listProductsService->list()
        );

        $this->getProjectionDocumentManager()->persist(ProductListItem::fromProperties(
            'id123',
            'Nameabc',
            true,
            'payment_cycle',
            1_000,
            10_000,
            'reward'
        ));
        $this->getProjectionDocumentManager()->flush();

        Assert::assertEquals(
            [
                [
                    'id' => 'id123',
                    'name' => 'Nameabc',
                    'isActive' => true,
                    'paymentCycle' => 'payment_cycle',
                    'annualFeeInCents' => 1_000,
                    'creditLimitInCents' => 10_000,
                    'reward' => 'reward',
                ],
            ],
            $listProductsService->list()
        );
    }
}
