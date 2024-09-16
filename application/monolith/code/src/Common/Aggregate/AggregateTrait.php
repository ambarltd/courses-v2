<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Aggregate;

use Galeas\Api\Common\Id\Id;

trait AggregateTrait
{
    private Id $aggregateId;

    private int $aggregateVersion;


    private function __construct(Id $aggregateId, int $aggregateVersion)
    {
        $this->aggregateId = $aggregateId;
        $this->aggregateVersion = $aggregateVersion;
    }

    public function aggregateId(): Id
    {
        return $this->aggregateId;
    }

    public function aggregateVersion(): int
    {
        return $this->aggregateVersion;
    }
}
