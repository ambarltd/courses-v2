<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\CommonException\ProjectionCannotRead;

class UserIdFromPrimaryEmailVerificationCode
{
    private DocumentManager $projectionDocumentManager;

    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    /**
     * @throws ProjectionCannotRead
     */
    public function userIdFromPrimaryEmailVerificationCode(string $primaryEmailVerificationCode): ?string
    {
        try {
            $userIdToPrimaryEmailVerificationCode = $this->projectionDocumentManager
                ->createQueryBuilder(PrimaryEmailVerificationCode::class)
                ->field('primaryEmailVerificationCode')->equals($primaryEmailVerificationCode)
                ->getQuery()
                ->getSingleResult()
            ;

            if ($userIdToPrimaryEmailVerificationCode instanceof PrimaryEmailVerificationCode) {
                return $userIdToPrimaryEmailVerificationCode->getUserId();
            }

            if (null === $userIdToPrimaryEmailVerificationCode) {
                return null;
            }

            throw new \Exception();
        } catch (\Throwable $exception) {
            throw new ProjectionCannotRead($exception);
        }
    }
}
