<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Event;

use Galeas\Api\Common\Id\Id;

interface Event
{
    public function eventId(): Id;

    public function aggregateId(): Id;

    public function aggregateVersion(): int;

    public function causationId(): Id;

    public function correlationId(): Id;

    public function recordedOn(): \DateTimeImmutable;

    public function metadata(): array;
}
