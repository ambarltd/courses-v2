<?php

declare(strict_types=1);

namespace Tests\UnitAndIntegration\BoundedContext\CreditCard\Product\QueryHandler;

use Galeas\Api\BoundedContext\CreditCard\Product\Projection\ProductList\ListProducts;
use Galeas\Api\BoundedContext\CreditCard\Product\Query\ListProductsQuery;
use Galeas\Api\BoundedContext\CreditCard\Product\QueryHandler\ListProductsQueryHandler;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerUnitTest;

class ListProductsQueryHandlerTest extends HandlerUnitTest
{
    public function testListProductsQueryHandler(): void
    {
        /** @var ListProducts $listProducts */
        $listProducts = $this->mockForCommandHandlerWithReturnValue(
            ListProducts::class,
            'list',
            [
                [
                    'id' => 'product_id',
                    'name' => 'product_name',
                    'isActive' => true,
                ],
            ]
        );

        $listProductsQueryHandler = new ListProductsQueryHandler($listProducts);

        Assert::assertEquals(
            [
                [
                    'id' => 'product_id',
                    'name' => 'product_name',
                    'isActive' => true,
                ],
            ],
            $listProductsQueryHandler->handle(new ListProductsQuery())
        );
    }
}
