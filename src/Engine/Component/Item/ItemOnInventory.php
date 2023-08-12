<?php

declare(strict_types=1);

namespace App\Engine\Component\Item;

use App\Engine\Component\ComponentInterface;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\Item\ItemBlueprint;

//todo stackable items and unique items.
class ItemOnInventory implements ComponentInterface
{
    public function __construct(private ItemBlueprint $itemBlueprint, private int $amount)
    {
    }

    public static function createFromItemOnGround(EntityManager $entityManager, ItemOnGround $itemOnGround): Entity
    {
        return $entityManager->createEntity(
            new self(
                $itemOnGround->getItemBlueprint(),
                $itemOnGround->getAmount(),
            ),
        );
    }

    public function getItemBlueprint(): ItemBlueprint
    {
        return $this->itemBlueprint;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }
}
