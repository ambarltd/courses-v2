<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCardProduct\Product\QueryHandler;

use Galeas\Api\BoundedContext\CreditCardProduct\Product\Projection\ProductList\ListProducts;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\Query\ListProductsQuery;

class ListProductsQueryHandler {

    private ListProducts $listProducts;

    public function __construct(ListProducts $listProducts)
    {
        $this->listProducts = $listProducts;
    }
    public function handle(ListProductsQuery $listProductsQuery): array
    {
        return $this->listProducts->list();
    }
}