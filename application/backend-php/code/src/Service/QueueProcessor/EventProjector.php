<?php

declare(strict_types=1);

namespace Galeas\Api\Service\QueueProcessor;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\CommonException\ProjectionCannotProcess;
use Galeas\Api\Service\ODM\ProjectionIdempotency\ProjectedEvent;

abstract class EventProjector
{
    protected DocumentManager $projectionDocumentManager;

    /**
     * @throws ProjectionCannotProcess
     */
    abstract public function project(Event $event): void;

    /**
     * @template T of object
     *
     * @param class-string<T>      $documentName
     * @param array<string, mixed> $fieldsAndValues
     *
     * @return null|T
     *
     * @throws \RuntimeException
     */
    protected function getOne(string $documentName, array $fieldsAndValues): ?object
    {
        $query = $this->projectionDocumentManager
            ->createQueryBuilder($documentName)
        ;

        foreach ($fieldsAndValues as $field => $value) {
            $query->field($field)->equals($value);
        }

        $result = $query->getQuery()->getSingleResult();

        if (null === $result) {
            return null;
        }

        if ($result instanceof $documentName) {
            return $result;
        }

        throw new \RuntimeException(', got '.\gettype($result));
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function saveOne(?object $object): void
    {
        // makes it easier to sometimes save nothing
        // for syntactic sugar
        if (null === $object) {
            return;
        }
        $this->projectionDocumentManager->persist($object);
    }

    /**
     * @throws \InvalidArgumentException|MongoDBException
     */
    protected function commitProjection(Event $projectedEvent, string $projectionName): void
    {
        $this->projectionDocumentManager
            ->persist(ProjectedEvent::new($projectedEvent, $projectionName))
        ;
        $this->projectionDocumentManager->flush([
            'withTransaction' => true,
        ]);
    }
}
