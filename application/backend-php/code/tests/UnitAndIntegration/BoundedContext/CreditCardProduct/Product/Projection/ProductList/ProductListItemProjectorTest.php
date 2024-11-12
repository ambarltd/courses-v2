<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\CreditCardProduct\Product\Projection\ProductList;

use Galeas\Api\BoundedContext\CreditCardProduct\Product\Projection\ProductList\ProductListItem;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\Projection\ProductList\ProductListItemProjector;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ResetsEventStoreAndProjectionsIntegrationTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class ProductListItemProjectorTest extends ResetsEventStoreAndProjectionsIntegrationTest
{
    public function testProcessProductDefined(): void
    {
        /** @var ProductListItemProjector $productItemListProjector */
        $productItemListProjector = $this->getContainer()
            ->get(ProductListItemProjector::class)
        ;

        $productDefined = SampleEvents::productDefined();
        $productId = $productDefined->aggregateId()->id();
        $productItemListProjector->project($productDefined);

        Assert::assertEquals(
            ProductListItem::fromProperties(
                $productId,
                $productDefined->name(),
                false,
                $productDefined->paymentCycle(),
                $productDefined->annualFeeInCents(),
                $productDefined->creditLimitInCents(),
                $productDefined->reward()
            ),
            $this->findProductListItem($productId)
        );
    }

    /**
     * @throws \Exception
     */
    private function findProductListItem(string $productId): ?ProductListItem
    {
        $queryBuilder = $this->getProjectionDocumentManager()
            ->createQueryBuilder(ProductListItem::class)
        ;

        $queryBuilder->field('id')->equals($productId);

        $productListItem = $queryBuilder
            ->getQuery()
            ->getSingleResult()
        ;

        if ($productListItem instanceof ProductListItem) {
            return $productListItem;
        }

        if (null === $productListItem) {
            return null;
        }

        throw new \Exception('Unexpected type');
    }
}
