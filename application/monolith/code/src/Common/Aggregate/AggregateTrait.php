<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Aggregate;

use Galeas\Api\Common\Id\Id;

trait AggregateTrait
{
    private Id $id;

    private int $aggregateVersion;


    private function __construct(Id $id, int $aggregateVersion)
    {
        $this->id = $id;
        $this->aggregateVersion = $aggregateVersion;
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function aggregateVersion(): int
    {
        return $this->aggregateVersion;
    }
}
