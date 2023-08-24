<?php

declare(strict_types=1);

namespace App\Engine\Commands;

use App\Engine\Component\Item\Inventory;
use App\Engine\Component\Item\ItemOnInventory;
use App\Engine\Component\PlayerCommandQueue;
use App\Engine\Entity\EntityManager;
use App\System\Event\Dispatcher;
use App\System\Event\Event\UiMessageEvent;
use App\System\Item\ItemPresetLibrary;

class GiveItemToPlayer implements InvokableCommandInterface
{
    public function __construct(
        private readonly EntityManager $entityManager,
        private readonly ItemPresetLibrary $itemManager,
        private readonly Inventory $inventory,
        private readonly string $itemInternalName,
        private readonly int $amount,
    ) {
    }

    public function __invoke(PlayerCommandQueue $playerCommandQueue)
    {
        $itemPreset = $this->itemManager->getPresetByName($this->itemInternalName);

        $this->inventory->addItem(
            $this->entityManager,
            $this->entityManager->createEntity(
                new ItemOnInventory(
                    $itemPreset,
                    $this->amount,
                )
            )
        );

        Dispatcher::dispatch(new UiMessageEvent(
            sprintf(
                "Received %dx %s\n",
                $this->amount,
                $this->itemInternalName,
            ),
            $playerCommandQueue
        ));
    }
}
