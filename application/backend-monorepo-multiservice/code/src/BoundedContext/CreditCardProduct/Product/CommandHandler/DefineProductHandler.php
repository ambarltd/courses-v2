<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCardProduct\Product\CommandHandler;

use Galeas\Api\BoundedContext\CreditCardProduct\Product\Command\DefineProductCommand;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\Event\ProductDefined;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\CommonException\EventStoreCannotRead;
use Galeas\Api\CommonException\EventStoreCannotWrite;
use Galeas\Api\Primitive\PrimitiveCreation\NoRandomnessAvailable;
use Galeas\Api\Service\EventStore\EventStore;

class DefineProductHandler
{
    private EventStore $eventStore;

    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * @throws EventStoreCannotRead|EventStoreCannotWrite|NoRandomnessAvailable
     */
    public function handle(DefineProductCommand $command): void
    {
        $eventId = Id::createNewByHashing($command->productIdentifierForAggregateIdHash);

        $this->eventStore->beginTransaction();
        $alreadyDefined = null !== $this->eventStore->findEvent($eventId->id());

        if ($alreadyDefined) {
            $this->eventStore->completeTransaction();

            return;
        }

        // maybe add validation later, but right now it's a script, so it's okay without it
        $aggregateId = Id::createNew();
        $event = ProductDefined::new(
            $eventId,
            $aggregateId,
            1,
            $eventId,
            $eventId,
            new \DateTimeImmutable('now'),
            [],
            $command->name,
            $command->interestInBasisPoints,
            $command->annualFeeInCents,
            $command->paymentCycle,
            $command->creditLimitInCents,
            $command->maxBalanceTransferAllowedInCents,
            $command->reward,
            $command->cardBackgroundHex
        );

        $this->eventStore->save($event);
        $this->eventStore->completeTransaction();
    }
}
