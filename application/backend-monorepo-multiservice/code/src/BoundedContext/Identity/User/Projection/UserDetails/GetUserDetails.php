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
     * @return array{userId: string, primaryEmailStatus: array{unverifiedEmail: array{email: string}}|array{verifiedButRequestedNewEmail: array{requestedEmail: string, verifiedEmail: string}}|array{verifiedEmail: array{email: string}}}.
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

            if ($userDetails instanceof UserDetails) {
                $status = $userDetails->getPrimaryEmailStatus();

                switch ($status) {
                    case $status instanceof UnverifiedEmail:
                        $primaryEmailStatus = [
                            'unverifiedEmail' => [
                                'email' => $status->getEmail(),
                            ],
                        ];

                        break;

                    case $status instanceof VerifiedEmail:
                        $primaryEmailStatus = [
                            'verifiedEmail' => [
                                'email' => $status->getEmail(),
                            ],
                        ];

                        break;

                    case $status instanceof VerifiedEmailButRequestedNewEmail:
                        $primaryEmailStatus = [
                            'verifiedButRequestedNewEmail' => [
                                'requestedEmail' => $status->getRequestedEmail(),
                                'verifiedEmail' => $status->getVerifiedEmail(),
                            ],
                        ];

                        break;
                }

                return [
                    'userId' => $userDetails->getUserId(),
                    'primaryEmailStatus' => $primaryEmailStatus,
                ];
            }

            throw new \Exception('Expected UserDetails instance, got nothing.');
        } catch (\Throwable $exception) {
            throw new ProjectionCannotRead($exception);
        }
    }
}
