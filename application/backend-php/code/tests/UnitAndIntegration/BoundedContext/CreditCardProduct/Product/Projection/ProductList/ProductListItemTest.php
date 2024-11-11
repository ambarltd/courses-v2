<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\CreditCardProduct\Product\Projection\ProductList;

use Galeas\Api\BoundedContext\CreditCardProduct\Product\Projection\ProductList\ProductListItem;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTest;

class ProductListItemTest extends UnitTest
{
    public function testProductListItem(): void
    {
        $productListItem = ProductListItem::fromProperties(
            'product_id',
            'product_name',
            true,
            'payment_cycle',
            1_000,
            10_000,
            'reward'
        );

        Assert::assertEquals('product_id', $productListItem->id());
        Assert::assertEquals('product_name', $productListItem->name());
        Assert::assertEquals('payment_cycle', $productListItem->paymentCycle());
        Assert::assertEquals(1_000, $productListItem->annualFeeInCents());
        Assert::assertEquals(10_000, $productListItem->creditLimitInCents());
        Assert::assertEquals('reward', $productListItem->reward());
        Assert::assertTrue($productListItem->isActive());

        $productListItem = $productListItem->deactivate();
        Assert::assertFalse($productListItem->isActive());

        $productListItem = $productListItem->activate();
        Assert::assertTrue($productListItem->isActive());
    }
}
