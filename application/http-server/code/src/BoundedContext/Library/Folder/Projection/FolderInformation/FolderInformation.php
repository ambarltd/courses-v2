<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation;

class FolderInformation
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string | null
     */
    private $parent;

    /**
     * @var string
     */
    private $ownerId;

    private function __construct(
        string $folderId,
        string $name,
        ?string $parent,
        string $ownerId
    ) {
        $this->id = $folderId;
        $this->name = $name;
        $this->parent = $parent;
        $this->ownerId = $ownerId;
    }

    public function folderId(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function updateName(string $newName): self
    {
        $this->name = $newName;

        return $this;
    }

    public function parent(): ?string
    {
        return $this->parent;
    }

    /**
     * @return $this
     */
    public function updateParent(?string $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function ownerId(): string
    {
        return $this->ownerId;
    }

    public static function fromProperties(
        string $folderId,
        string $name,
        ?string $parent,
        string $ownerId
    ): self {
        return new self($folderId, $name, $parent, $ownerId);
    }
}
