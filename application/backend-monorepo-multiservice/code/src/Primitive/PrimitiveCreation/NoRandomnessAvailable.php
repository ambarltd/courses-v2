<?php

declare(strict_types=1);

namespace Galeas\Api\Primitive\PrimitiveCreation;

use Galeas\Api\CommonException\InternalServerErrorException;

class NoRandomnessAvailable extends InternalServerErrorException
{
    public static function getErrorIdentifier(): string
    {
        return 'Primitive_NoRandomnessAvailable';
    }
}
