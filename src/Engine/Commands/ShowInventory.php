<?php

declare(strict_types=1);

namespace App\Engine\Commands;

use App\Engine\Component\Item\Inventory;
use App\Engine\Component\Item\ItemOnInventory;
use App\System\Event\Dispatcher;
use App\System\Event\Event\UiMessageEvent;

class ShowInventory implements InvokableCommandInterface
{
    public function __construct(private readonly Inventory $inventory)
    {
    }

    public function __invoke()
    {
        $items = $this->inventory->getItems()->getEntitiesWithComponents(
            ItemOnInventory::class
        );


        $uiMessage = '';

        $uiMessage .= "\nInventory:\n";
        $uiMessage .= "-----------------\n";
        $i = 0;
        /**
        * @var ItemOnInventory $item
         */
        foreach ($items as [$item]) {
            $uiMessage .= sprintf(
                "%d - %dX %s - %s\n",
                ++$i,
                $item->getAmount(),
                $item->getItemBlueprint()->getName(),
                $item->getItemBlueprint()->getShortDescription(),
            );
        }

        $uiMessage .= "\n-----------------\n";

        Dispatcher::dispatch(new UiMessageEvent($uiMessage));
    }
}
