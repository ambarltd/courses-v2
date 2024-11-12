<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\ValueObject\VerifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\ValueObject\VerifiedEmailButRequestedNewEmail;
use Galeas\Api\CommonException\ProjectionCannotRead;

class GetUserDetails
{
    private DocumentManager $projectionDocumentManager;

    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    /**
     * @return array{userId: string, primaryEmailStatus: array{unverifiedEmail: array{email: string}}|array{verifiedButRequestedNewEmail: array{requestedEmail: string, verifiedEmail: string}}|array{verifiedEmail: array{email: string}}}
     *
     * @throws ProjectionCannotRead
     */
    public function getUserDetails(string $userId): array
    {
        try {
            $userDetails = $this->projectionDocumentManager
                ->createQueryBuilder(UserDetails::class)
                ->field('id')->equals($userId)
                ->getQuery()
                ->getSingleResult()
            ;

            if (!$userDetails instanceof UserDetails) {
                throw new \Exception('Expected UserDetails instance, got nothing.');
            }
            if (
                $userDetails->verifiedEmail() !== null
                && $userDetails->unverifiedEmail() !== null
            ) {
                return [
                    'userId' => $userDetails->getUserId(),
                    'primaryEmailStatus' => [
                        'verifiedButRequestedNewEmail' => [
                            'requestedEmail' => $userDetails->unverifiedEmail(),
                            'verifiedEmail' => $userDetails->verifiedEmail(),
                        ],
                    ]
                ];
            }

            if ($userDetails->verifiedEmail() !== null && $userDetails->unverifiedEmail() === null) {
                return [
                    'userId' => $userDetails->getUserId(),
                    'primaryEmailStatus' => [
                        'verifiedEmail' => [
                            'email' => $userDetails->verifiedEmail(),
                        ],
                    ],
                ];
            }

            if ($userDetails->verifiedEmail() === null && $userDetails->unverifiedEmail() !== null) {
                return [
                    'userId' => $userDetails->getUserId(),
                    'primaryEmailStatus' => [
                        'unverifiedEmail' => [
                            'email' => $userDetails->unverifiedEmail(),
                        ],
                    ],
                ];
            }

            throw new \Exception('Expected UserDetails to have non null details.');
        } catch (\Throwable $exception) {
            throw new ProjectionCannotRead($exception);
        }
    }
}
