<?php

declare(strict_types=1);

namespace App\Engine\Component\Item;

use App\Engine\Component\ComponentInterface;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MapSymbol;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\Item\ItemBlueprint;

readonly class ItemOnGround implements ComponentInterface
{

    private function __construct(private ItemBlueprint $itemBlueprint, private int $amount)
    {
    }

    public static function createItemOnGround(
        EntityManager $entityManager,
        ItemBlueprint $itemBlueprint,
        int $amount,
        int $x,
        int $y
    ): Entity {
        return $entityManager->createEntity(
            new MapPosition($x, $y),
            new self($itemBlueprint, $amount),
            new MapSymbol("?"),
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

    //todo this should be moved to the item collector system.
//    public function onCollect(): int
//    {
//        Dispatcher::dispatch(
//            new UiMessageEvent(
//                sprintf(
//                    "You got %dx %s\n",
//                    $this->amount,
//                    $this->itemBlueprint->getInternalName(),
//                )
//            )
//        );
//    }
}
