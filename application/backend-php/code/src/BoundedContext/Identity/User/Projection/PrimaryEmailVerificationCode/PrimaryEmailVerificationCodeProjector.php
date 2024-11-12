<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\CommonException\ProjectionCannotProcess;
use Galeas\Api\Service\QueueProcessor\EventProjector;

class PrimaryEmailVerificationCodeProjector extends EventProjector
{
    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    public function project(Event $event): void
    {
        try {
            switch (true) {
                case $event instanceof SignedUp:
                    $this->saveOne(PrimaryEmailVerificationCode::fromUserIdAndVerificationCode(
                        $event->aggregateId()->id(),
                        null
                    ));

                    break;

                case $event instanceof PrimaryEmailChangeRequested:
                    $primaryEmailVerificationCode = $this->getOne(PrimaryEmailVerificationCode::class, ['id' => $event->aggregateId()->id()]);
                    $this->saveOne($primaryEmailVerificationCode?->setVerificationCode($event->newVerificationCode()));

                    break;

                case $event instanceof PrimaryEmailVerified:
                    $primaryEmailVerificationCode = $this->getOne(PrimaryEmailVerificationCode::class, ['id' => $event->aggregateId()->id()]);
                    $this->saveOne($primaryEmailVerificationCode?->resetVerificationCode());

                    break;
            }
            $this->commitProjection($event, 'Identity_User_PrimaryEmailVerificationCode');
        } catch (\Throwable $exception) {
            throw new ProjectionCannotProcess($exception);
        }
    }
}
