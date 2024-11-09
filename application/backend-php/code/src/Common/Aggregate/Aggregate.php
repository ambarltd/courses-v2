<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Aggregate;

use Galeas\Api\Common\Id\Id;

interface Aggregate
{
    public function aggregateId(): Id;

    public function aggregateVersion(): int;
}
