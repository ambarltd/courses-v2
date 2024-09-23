<?php

declare(strict_types=1);

namespace Galeas\Api\CommonController;

/**
 * This is an annotation class.
 * It should be used in Controllers to name the file responsible for the request schema.
 * It is implemented as a class, to help with auto-complete in IDEs.
 * Moreover, Symfony creates errors upon unknown annotations if they're not a class.
 *
 * @Annotation
 */
class RequestSchema
{
    public string $name;
}
