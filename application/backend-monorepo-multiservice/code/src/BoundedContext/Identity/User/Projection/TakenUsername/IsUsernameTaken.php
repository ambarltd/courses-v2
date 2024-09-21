<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\IsUsernameTaken as SUIsUsernameTaken;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

class IsUsernameTaken implements SUIsUsernameTaken
{
    /**
     * @var DocumentManager
     */
    private $projectionDocumentManager;

    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    public function isUsernameTaken(string $username): bool
    {
        try {
            $takenUsername = $this->projectionDocumentManager
                ->createQueryBuilder(TakenUsername::class)
                ->field('canonicalUsername')->equals(strtolower($username))
                ->getQuery()
                ->getSingleResult();

            if ($takenUsername instanceof TakenUsername) {
                return true;
            }

            if (null === $takenUsername) {
                return false;
            }

            throw new \Exception();
        } catch (\Throwable $exception) {
            throw new ProjectionCannotRead($exception);
        }
    }
}
