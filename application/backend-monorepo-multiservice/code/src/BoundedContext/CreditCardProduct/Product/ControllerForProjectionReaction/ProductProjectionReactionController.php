<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCardProduct\Product\ControllerForProjectionReaction;

use Galeas\Api\BoundedContext\CreditCardProduct\Product\Projection\ProductList\ProductListItemProjector;
use Galeas\Api\CommonController\ProjectionReactionController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/credit_card_product/product')]
class ProductProjectionReactionController extends ProjectionReactionController
{
    private ProductListItemProjector $productListItemProjector;

    public function __construct(ProductListItemProjector $productListItemProjector)
    {
        $this->productListItemProjector = $productListItemProjector;
    }

    #[Route('/projection/product_list_item', name: 'product_list_item', methods: ['POST'])]
    public function hashedPassword(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            $this->productListItemProjector,
            200
        );
    }
}
