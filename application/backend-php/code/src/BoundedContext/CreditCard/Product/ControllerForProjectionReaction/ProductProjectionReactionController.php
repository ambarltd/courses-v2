<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCard\Product\ControllerForProjectionReaction;

use Galeas\Api\BoundedContext\CreditCard\Product\Projection\ProductList\ProductListProjector;
use Galeas\Api\CommonController\ProjectionReactionController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/credit_card/product')]
class ProductProjectionReactionController extends ProjectionReactionController
{
    private ProductListProjector $productListProjector;

    public function __construct(ProductListProjector $productListProjector)
    {
        $this->productListProjector = $productListProjector;
    }

    #[Route('/projection/product_list', name: 'product_list', methods: ['POST'])]
    public function hashedPassword(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            $this->productListProjector,
            200
        );
    }
}
