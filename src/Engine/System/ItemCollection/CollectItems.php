<?php

declare(strict_types=1);

namespace App\Engine\System\ItemCollection;

use App\Engine\Component\Item\Inventory;
use App\Engine\Component\Item\ItemCollector;
use App\Engine\Component\Item\ItemOnGround;
use App\Engine\Component\Item\ItemOnInventory;
use App\Engine\Component\Player;
use App\Engine\Entity\EntityManager;
use App\Engine\System\WorldSystemInterface;
use App\System\Event\Dispatcher;
use App\System\Event\Event\UiMessageEvent;
use App\System\World;

class CollectItems implements WorldSystemInterface
{

    public function __construct(private readonly World $world, private readonly EntityManager $entityManager)
    {
    }

    public function process(): void
    {
        //todo loop over world
        $worldEntityMap = $this->world->getEntityMap();

        foreach ($worldEntityMap as $x => $row) {
            foreach ($row as $y => $entityCollection) {
                $itemCollectors = $entityCollection->getEntitiesWithComponents(
                    ItemCollector::class
                );

                /** @var ItemOnGround[] $itemsToCollect */
                $itemsToCollect = $entityCollection->getEntitiesWithComponents(
                    ItemOnGround::class
                );
                $itemCollectorId = array_keys($itemCollectors)[0] ?? null;
                $itemCollector = array_shift($itemCollectors);

                if ($itemCollector && count($itemsToCollect)) {
                    /**
                     * @var string $itemToCollectEntityId
                     * @var ItemOnGround $itemOnGround
                     */
                    foreach ($itemsToCollect as $itemToCollectEntityId => [$itemOnGround]) {
                        $this->entityManager->removeEntity($itemToCollectEntityId);
                        $collectorEntity = $this->entityManager->getEntityById($itemCollectorId);

                        /** @var ?Inventory $targetInventory */
                        $collectorEntity->getComponent(Inventory::class)?->addItem(
                            $this->entityManager,
                            ItemOnInventory::createFromItemOnGround($this->entityManager, $itemOnGround)
                        );

                        if ($collectorEntity->getComponent(Player::class)) {
                            Dispatcher::dispatch(
                                new UiMessageEvent(
                                    sprintf(
                                        "You got %dx %s.\n",
                                        $itemOnGround->getAmount(),
                                        $itemOnGround->getItemBlueprint()->getInternalName(),
                                    )
                                )
                            );
                        }
                    }
                }
            }
        }

        // TODO: Implement process() method.
    }
}
