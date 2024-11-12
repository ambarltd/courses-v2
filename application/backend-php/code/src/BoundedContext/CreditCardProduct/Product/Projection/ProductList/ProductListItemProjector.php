<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCardProduct\Product\Projection\ProductList;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\Event\ProductActivated;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\Event\ProductDeactivated;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\Event\ProductDefined;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\CommonException\ProjectionCannotProcess;
use Galeas\Api\Service\QueueProcessor\EventProjector;

class ProductListItemProjector extends EventProjector
{
    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    public function project(Event $event): void
    {
        try {
            switch (true) {
                case $event instanceof ProductDefined:
                    $this->saveOne(
                        ProductListItem::fromProperties(
                            $event->aggregateId()->id(),
                            $event->name(),
                            false,
                            $event->paymentCycle(),
                            $event->annualFeeInCents(),
                            $event->creditLimitInCents(),
                            $event->reward()
                        )
                    );
                    break;
                case $event instanceof ProductActivated:
                    $productListItem = $this->getOne(ProductListItem::class, ['id' => $event->aggregateId()->id()]);
                    $this->saveOne($productListItem?->activate());
                    break;
                case $event instanceof ProductDeactivated:
                    $productListItem = $this->getOne(ProductListItem::class, ['id' => $event->aggregateId()->id()]);
                    $this->saveOne($productListItem?->deactivate());
                    break;
            }
            $this->commitProjection($event, 'CreditCardProduct_Product_ProductList');
        } catch (\Throwable $throwable) {
            throw new ProjectionCannotProcess($throwable);
        }
    }

    /**
     * @throws \Exception
     */
    private function findItem(string $id): ProductListItem
    {
        $productListItem = $this->projectionDocumentManager
            ->createQueryBuilder(ProductListItem::class)
            ->field('id')->equals($id)
            ->getQuery()
            ->getSingleResult()
        ;
        if ($productListItem instanceof ProductListItem) {
            return $productListItem;
        }

        throw new \Exception();
    }
}
