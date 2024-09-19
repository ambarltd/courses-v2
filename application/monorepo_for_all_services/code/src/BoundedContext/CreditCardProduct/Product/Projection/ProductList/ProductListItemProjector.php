<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCardProduct\Product\Projection\ProductList;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\Event\ProductActivated;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\Event\ProductDeactivated;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\Event\ProductDefined;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotProcess;
use Galeas\Api\Service\QueueProcessor\EventProjector;

class ProductListItemProjector implements EventProjector
{
    /**
     * @var DocumentManager
     */
    private $projectionDocumentManager;

    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    public function project(Event $event): void
    {
        try {
            $id = $event->aggregateId()->id();

            if ($event instanceof ProductDefined) {
                $setName = $event->name();
                $setIsActive = false;
            } elseif ($event instanceof ProductActivated) {
                $setName = null;
                $setIsActive = true;
            } elseif ($event instanceof ProductDeactivated) {
                $setName = null;
                $setIsActive = false;
            } else {
                return;
            }

            /** @var ProductListItem $productListItem */
            $productListItem = $this->projectionDocumentManager
                ->createQueryBuilder(ProductListItem::class)
                ->field('id')->equals($id)
                ->getQuery()
                ->getSingleResult();

            if (null === $productListItem) {
                $productListItem = ProductListItem::fromProperties(
                    $id,
                    $setName !== null ? $setName : false,
                    $setIsActive
                );

                $this->projectionDocumentManager->persist($productListItem);
                $this->projectionDocumentManager->flush();

                return;
            }

            if ($setIsActive) {
                $productListItem->activate();
            } else {
                $productListItem->deactivate();
            }

            $this->projectionDocumentManager->persist($productListItem);
            $this->projectionDocumentManager->flush();
        } catch (\Throwable $throwable) {
            throw new ProjectionCannotProcess($throwable);
        }
    }
}
