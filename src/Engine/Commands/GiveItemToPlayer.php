<?php

declare(strict_types=1);

namespace App\Engine\Commands;

use App\Engine\Component\Item\Inventory;
use App\Engine\Component\Item\ItemOnInventory;
use App\Engine\Component\Player;
use App\Engine\Entity\EntityManager;
use App\System\Event\Dispatcher;
use App\System\Event\Event\UiMessageEvent;
use App\System\Item\ItemPresetLibrary;

class GiveItemToPlayer implements InvokableCommandInterface
{

    public function __construct(
        private readonly EntityManager $entityManager,
        private readonly ItemPresetLibrary $itemManager,
        private readonly string $itemInternalName,
        private readonly int $amount,
    ) {
    }

    public function __invoke()
    {
        $players = $this->entityManager->getEntitiesWithComponents(
            Inventory::class,
            Player::class,
        );
        $itemBluePrint = $this->itemManager->getPresetByName($this->itemInternalName);

        /**
         * @var Inventory $inventory
         */
        foreach ($players as [$inventory]) {
            $inventory->addItem(
                $this->entityManager,
                $this->entityManager->createEntity(
                    new ItemOnInventory(
                        $itemBluePrint,
                        $this->amount,
                    )
                )
            );
        }

        Dispatcher::dispatch(new UiMessageEvent(
            sprintf("Gave %dx %s to players.\n", $this->amount, $this->itemInternalName)
        ));
    }
}
