<?php

declare(strict_types=1);

namespace App\Engine\System\ItemCollection;

use App\Engine\Component\Item\Inventory;
use App\Engine\Component\Item\ItemCollector;
use App\Engine\Component\Item\ItemOnGround;
use App\Engine\Component\Item\ItemOnInventory;
use App\Engine\Component\Player;
use App\Engine\Component\PlayerCommandQueue;
use App\Engine\Entity\EntityManager;
use App\Engine\System\WorldSystemInterface;
use App\System\Event\Dispatcher;
use App\System\Event\Event\UiMessageEvent;
use App\System\Kernel;
use App\System\World\WorldManager;

class CollectItems implements WorldSystemInterface
{

    public function __construct(private readonly WorldManager $world, private readonly EntityManager $entityManager)
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

                        /** @var PlayerCommandQueue $playerCommandQueue */
                        $playerCommandQueue = $collectorEntity->getComponent(PlayerCommandQueue::class);
                        if ($playerCommandQueue) {

                            $uiMessage = sprintf(
                                "You got %dx %s.\n",
                                $itemOnGround->getAmount(),
                                $itemOnGround->getItemBlueprint()->getName(),
                            );

                            Dispatcher::getInstance()->dispatch(
                                new UiMessageEvent(
                                    $uiMessage,
                                    $playerCommandQueue
                                )
                            );
                        }
                    }
                }
            }
        }
    }
}
