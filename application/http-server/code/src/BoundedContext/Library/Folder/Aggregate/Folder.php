<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\Aggregate;

use Galeas\Api\Common\Aggregate\Aggregate;
use Galeas\Api\Common\Aggregate\AggregateTrait;
use Galeas\Api\Common\Id\Id;

class Folder implements Aggregate
{
    use AggregateTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Id | null
     */
    private $parent;

    /**
     * @var Id
     */
    private $ownerId;

    /**
     * @var bool
     */
    private $deleted;

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return Id | null
     */
    public function parent(): ?Id
    {
        return $this->parent;
    }

    public function ownerId(): Id
    {
        return $this->ownerId;
    }

    public function deleted(): bool
    {
        return $this->deleted;
    }

    /**
     * @param Id | null $parent
     */
    public static function fromProperties(
        Id $id,
        string $name,
        ?Id $parent,
        Id $ownerId,
        bool $deleted
    ): Folder {
        $folder = new self($id);

        $folder->name = $name;
        $folder->parent = $parent;
        $folder->ownerId = $ownerId;
        $folder->deleted = $deleted;

        return $folder;
    }
}
