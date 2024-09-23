<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Id;

use Galeas\Api\Primitive\PrimitiveCreation\Id\IdCreator;
use Galeas\Api\Primitive\PrimitiveCreation\NoRandomnessAvailable;
use Galeas\Api\Primitive\PrimitiveValidation\Id\IdValidator;

class Id
{
    private string $id;

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * Universally unique identifier.
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Factory method.
     * Should create a new Id by being passed a string.
     *
     * @throws InvalidId
     */
    public static function fromId(string $id): self
    {
        if (false === IdValidator::isValid($id)) {
            throw new InvalidId();
        }

        return new self($id);
    }

    public static function createNewByHashing(string $stringToHash): self
    {
        return new self(IdCreator::createByHashingString($stringToHash));
    }

    /**
     * @throws NoRandomnessAvailable
     */
    public static function createNew(): self
    {
        return new self(IdCreator::create());
    }
}
