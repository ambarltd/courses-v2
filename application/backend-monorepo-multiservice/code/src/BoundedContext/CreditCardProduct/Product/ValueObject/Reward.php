<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCardProduct\Product\ValueObject;

class Reward {
    private NoReward|Cashback|Points $type;

    public function type(): Cashback|Points
    {
        return $this->type;
    }
    public static function fromProperties(NoReward|Cashback|Points $type): self {
        $reward = new self();
        $reward->type = $type;

        return $reward;
    }
}