<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\CreditCardProduct\Product\Projection\ProductList;

use Galeas\Api\BoundedContext\CreditCardProduct\Product\Projection\ProductList\ListProducts;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\Projection\ProductList\ProductListItem;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ProjectionAndReactionIntegrationTest;

class ListProductsTest extends ProjectionAndReactionIntegrationTest
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
            true
        ));
        $this->getProjectionDocumentManager()->flush();

        Assert::assertEquals(
            [
                [
                    'id' => 'id123',
                    'name' => 'Nameabc',
                    'isActive' => true,
                ],
            ],
            $listProductsService->list()
        );
    }
}
