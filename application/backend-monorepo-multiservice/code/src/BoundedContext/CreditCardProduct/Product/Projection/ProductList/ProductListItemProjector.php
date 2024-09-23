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

class ProductListItemProjector implements EventProjector
{
    private DocumentManager $projectionDocumentManager;

    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    public function project(Event $event): void
    {
        try {
            $id = $event->aggregateId()->id();

            $productListItem = null;

            // ProductDefined -> create an item in the projection database
            if ($event instanceof ProductDefined) {
                $productListItem = ProductListItem::fromProperties(
                    $id,
                    $event->name(),
                    false
                );
            }

            // ProductActivated -> update the item in the projection database
            if ($event instanceof ProductActivated) {
                $productListItem = $this->findItem($id);
                $productListItem->activate();
            }

            // ProductDeactivated -> update the item in the projection database
            if ($event instanceof ProductDeactivated) {
                $productListItem = $this->findItem($id);
                $productListItem->deactivate();
            }

            if (null !== $productListItem) {
                $this->projectionDocumentManager->persist($productListItem);
                $this->projectionDocumentManager->flush();
            }
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
