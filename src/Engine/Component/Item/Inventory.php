<?php

declare(strict_types=1);

namespace App\Engine\Component\Item;

use App\Engine\Component\ComponentInterface;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityCollection;
use App\Engine\Entity\EntityManager;

class Inventory implements ComponentInterface
{
    private EntityCollection $items;

    public function __construct()
    {
        $this->items = new EntityCollection();
    }

    public function addItem(EntityManager $entityManager, Entity $item)
    {
        /** @var ItemOnInventory $itemOnInventory */
        if ($itemOnInventory = $item->getComponent(ItemOnInventory::class)) {
            !$itemOnInventory->getItemBlueprint()->isStackable() ?
                $this->items->addEntity($item) :
                $this->handleStackableItem($entityManager, $itemOnInventory, $item);
        }
    }

    public function getItems(): EntityCollection
    {
        return $this->items;
    }

    private function handleStackableItem(
        EntityManager   $entityManager,
        ItemOnInventory $newItemOnInventory,
        Entity          $newItem,
    ): void {
        /**
         * @var Entity $itemEntity
         */
        foreach ($this->items as $itemEntity) {
            /**
             * @var ItemOnInventory $itemOnInventory
             */
            $itemOnInventory = $itemEntity->getComponent(ItemOnInventory::class);
            $itemBluePrint = $itemOnInventory->getItemBlueprint();

            if ($newItemOnInventory->getItemBlueprint()->getInternalName() === $itemBluePrint->getInternalName()) {
                //todo items are not completing the stack before creating a new one
                //     if the amount can't fit without dividing, it will create a new stack.
                //     fix it!
                $currentAmount = $itemOnInventory->getAmount();
                $newAmount = $currentAmount + $newItemOnInventory->getAmount();
                if (
                    $itemBluePrint->isStackable() &&
                    $newAmount < $itemBluePrint->getStackSize()
                ) {
                    $updatedEntity = $entityManager->updateEntityComponents(
                        $itemEntity->getId(),
                        new ItemOnInventory(
                            $itemBluePrint,
                            $newAmount,
                        )
                    );

                    $this->items->addEntity($updatedEntity);

                    $entityManager->removeEntity($newItem->getId());

                    return;
                }
            }
        }

        //no suitable stack found.
        $this->items->addEntity($newItem);
    }
}
