<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Aggregate;

use Galeas\Api\Common\Id\Id;

trait AggregateTrait
{
    /**
     * @var Id
     */
    private $id;

    private function __construct(Id $id)
    {
        $this->id = $id;
    }

    public function id(): Id
    {
        return $this->id;
    }
}
