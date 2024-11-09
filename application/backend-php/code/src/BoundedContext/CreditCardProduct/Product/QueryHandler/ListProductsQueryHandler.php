<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCardProduct\Product\QueryHandler;

use Galeas\Api\BoundedContext\CreditCardProduct\Product\Projection\ProductList\ListProducts;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\Query\ListProductsQuery;
use Galeas\Api\CommonException\ProjectionCannotRead;

class ListProductsQueryHandler
{
    private ListProducts $listProducts;

    public function __construct(ListProducts $listProducts)
    {
        $this->listProducts = $listProducts;
    }

    /**
     * @return array<array{id: string, name: string, isActive: bool}>
     *
     * @throws ProjectionCannotRead
     */
    public function handle(ListProductsQuery $listProductsQuery): array
    {
        return $this->listProducts->list();
    }
}
