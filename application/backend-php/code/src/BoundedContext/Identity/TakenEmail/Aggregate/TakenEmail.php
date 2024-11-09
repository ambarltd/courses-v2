<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\TakenEmail\Aggregate;

use Galeas\Api\Common\Aggregate\Aggregate;
use Galeas\Api\Common\Aggregate\AggregateTrait;
use Galeas\Api\Common\Id\Id;

class TakenEmail implements Aggregate
{
    use AggregateTrait;

    private string $takenEmailInLowercase;

    private ?Id $takenByUser;

    public function takenEmailInLowercase(): string
    {
        return $this->takenEmailInLowercase;
    }

    public function takenByUser(): ?Id
    {
        return $this->takenByUser;
    }

    public static function fromProperties(
        Id $aggregateId,
        int $aggregateVersion,
        string $takenEmailInLowercase,
        ?Id $takenByUser,
    ): self {
        $user = new self($aggregateId, $aggregateVersion);

        $user->takenEmailInLowercase = $takenEmailInLowercase;
        $user->takenByUser = $takenByUser;

        return $user;
    }
}
