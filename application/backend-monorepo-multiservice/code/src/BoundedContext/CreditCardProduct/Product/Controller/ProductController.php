<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCardProduct\Product\Controller;

use Galeas\Api\BoundedContext\CreditCardProduct\Product\Query\ListProductsQuery;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\QueryHandler\ListProductsQueryHandler;
use Galeas\Api\Common\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
class ProductController extends BaseController
{
    public function __construct(
        ListProductsQueryHandler $listProductsQueryHandler,
    ) {
        parent::__construct(
            [
                $listProductsQueryHandler,
            ]
        );
    }

    /**
     * @RequestSchema(name="V1_CreditCardProduct_Product_ListItems")
     * @ResponseSchema(name="V1_CreditCardProduct_Product_ListItems")
     */
    #[Route('/credit_card_product/product/list-items', name: 'V1_CreditCardProduct_Product_ListItems', methods: ['POST'] )]
    public function listItems(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_CreditCardProduct_Product_ListItems.json',
            'Response/V1_CreditCardProduct_Product_ListItems.json',
            ListProductsQuery::class,
            $this->getService(ListProductsQueryHandler::class),
            null,
            Response::HTTP_OK
        );
    }
}
