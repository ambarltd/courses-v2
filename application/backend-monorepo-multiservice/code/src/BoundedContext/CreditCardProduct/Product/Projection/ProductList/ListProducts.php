<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCardProduct\Product\Projection\ProductList;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\CommonException\ProjectionCannotRead;

class ListProducts
{
    private DocumentManager $projectionDocumentManager;

    public function __constructor(DocumentManager $projectionDocumentManager): void
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    /**
     * @return array<array{id: string, name: string, isActive: bool}>
     *
     * @throws ProjectionCannotRead
     */
    public function list(): array
    {
        try {
            /** @var ProductListItem[] $items */
            $items = $this->projectionDocumentManager
                ->createQueryBuilder(ProductListItem::class)
                ->getQuery()
                ->getIterator()
            ;

            $list = [];
            foreach ($items as $item) {
                $list[] = [
                    'id' => $item->id(),
                    'name' => $item->name(),
                    'isActive' => $item->isActive(),
                ];
            }

            return $list;
        } catch (\Throwable $exception) {
            throw new ProjectionCannotRead($exception);
        }
    }
}
