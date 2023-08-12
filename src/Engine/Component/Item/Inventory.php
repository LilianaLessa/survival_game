<?php

declare(strict_types=1);

namespace App\Engine\Component\Item;

use App\Engine\Component\ComponentInterface;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityCollection;

class Inventory implements ComponentInterface
{
    private EntityCollection $items;

    public function __construct()
    {
        $this->items = new EntityCollection();
    }

    public function addItem(Entity $item)
    {
        if ($item->getComponent(ItemOnInventory::class)) {
            $this->items->addEntity($item);
        }
    }

    public function getItems(): EntityCollection
    {
        return $this->items;
    }
}
