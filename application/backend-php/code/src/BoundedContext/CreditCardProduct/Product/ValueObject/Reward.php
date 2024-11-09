<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject;

class Reward
{
    private Cashback|NoReward|Points $type;

    public function type(): Cashback|NoReward|Points
    {
        return $this->type;
    }

    public static function fromProperties(Cashback|NoReward|Points $type): self
    {
        $reward = new self();
        $reward->type = $type;

        return $reward;
    }
}
