<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCard\Product\Projection\ProductList;

class ProductListItem
{
    private string $id;

    private string $name;
    private string $paymentCycle;

    private int $annualFeeInCents;

    private int $creditLimitInCents;

    private string $reward;
    private bool $isActive;

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function activate(): self
    {
        $this->isActive = true;

        return $this;
    }

    public function deactivate(): self
    {
        $this->isActive = false;

        return $this;
    }

    public function paymentCycle(): string
    {
        return $this->paymentCycle;
    }

    public function annualFeeInCents(): int
    {
        return $this->annualFeeInCents;
    }

    public function creditLimitInCents(): int
    {
        return $this->creditLimitInCents;
    }

    public function reward(): string
    {
        return $this->reward;
    }

    public static function fromProperties(
        string $productId,
        string $name,
        bool $isActive,
        string $paymentCycle,
        int $annualFeeInCents,
        int $creditLimitInCents,
        string $reward
    ): self {
        $productListItem = new self();
        $productListItem->id = $productId;
        $productListItem->name = $name;
        $productListItem->isActive = $isActive;
        $productListItem->paymentCycle = $paymentCycle;
        $productListItem->annualFeeInCents = $annualFeeInCents;
        $productListItem->creditLimitInCents = $creditLimitInCents;
        $productListItem->reward = $reward;

        return $productListItem;
    }
}
