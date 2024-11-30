<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCard\Product\Projection\ProductList;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\CommonException\ProjectionCannotRead;

class ListProducts
{
    private DocumentManager $projectionDocumentManager;

    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    /**
     * @return array<array{id: string, name: string, isActive: bool, paymentCycle: string, annualFeeInCents: int, creditLimitInCents: int, reward: string}>
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
                    'paymentCycle' => $item->paymentCycle(),
                    'annualFeeInCents' => $item->annualFeeInCents(),
                    'creditLimitInCents' => $item->creditLimitInCents(),
                    'reward' => $item->reward(),
                ];
            }

            return $list;
        } catch (\Throwable $exception) {
            throw new ProjectionCannotRead($exception);
        }
    }
}
