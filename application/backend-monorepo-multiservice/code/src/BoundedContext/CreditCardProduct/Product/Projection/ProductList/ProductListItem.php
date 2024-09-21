<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\CreditCardProduct\Product\Projection\ProductList;

class ProductListItem
{
    private string $id;

    private string $name;
    private bool $isActive;

    public function id()
    {
        return $this->id;
    }

    public function name()
    {
        return $this->name;
    }

    public function isActive()
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
    public static function fromProperties(
        string $productId,
        string $name,
        bool $isActive
    ): self {
        $productListItem = new self();
        $productListItem->id = $productId;
        $productListItem->name = $name;
        $productListItem->isActive = $isActive;

        return $productListItem;
    }
}
