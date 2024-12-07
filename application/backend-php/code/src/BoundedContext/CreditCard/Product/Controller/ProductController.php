<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCard\Product\Controller;

use Galeas\Api\BoundedContext\CreditCard\Product\Query\ListProductsQuery;
use Galeas\Api\BoundedContext\CreditCard\Product\QueryHandler\ListProductsQueryHandler;
use Galeas\Api\CommonController\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
class ProductController extends BaseController
{
    private ListProductsQueryHandler $listProductsQueryHandler;

    public function __construct(
        ListProductsQueryHandler $listProductsQueryHandler,
    ) {
        $this->listProductsQueryHandler = $listProductsQueryHandler;
    }

    /**
     * @RequestSchema(name="V1_CreditCard_Product_ListProducts")
     *
     * @ResponseSchema(name="V1_CreditCard_Product_ListProducts")
     */
    #[Route('/credit_card/product/list-products', name: 'V1_CreditCard_Product_ListProducts', methods: ['POST'])]
    public function listProducts(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_CreditCard_Product_ListProducts.json',
            'Response/V1_CreditCard_Product_ListProducts.json',
            ListProductsQuery::class,
            $this->listProductsQueryHandler,
            null,
            Response::HTTP_OK
        );
    }
}
