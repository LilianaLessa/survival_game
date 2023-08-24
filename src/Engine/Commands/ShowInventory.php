<?php

declare(strict_types=1);

namespace App\Engine\Commands;

use App\Engine\Component\Item\Inventory;
use App\Engine\Component\Item\ItemOnInventory;
use App\Engine\Component\PlayerCommandQueue;
use App\Engine\Entity\EntityManager;
use App\System\ConsoleColorCode;
use App\System\Event\Dispatcher;
use App\System\Event\Event\UiMessageEvent;
use App\System\Kernel;

class ShowInventory implements InvokableCommandInterface
{
    public function __construct(private readonly Inventory $inventory, private readonly bool $detailed)
    {
    }

    public function __invoke(PlayerCommandQueue $playerCommandQueue)
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
            $itemBlueprint = $item->getItemBlueprint();
            $header = sprintf(
                "%d - %dX %s - %s\n",
                ++$i,
                $item->getAmount(),
                sprintf(
                    "%s%s%s",
                    $itemBlueprint->getRarity()->getColorCode(),
                    $itemBlueprint->getInGameName(),
                    ConsoleColorCode::Color_Off->value,
                ),
                $item->getItemBlueprint()->getShortDescription(),
            );

            $rarityAndLongDescription = sprintf(
                "\t(%s) - %s\n",
                $itemBlueprint->getRarity()->value,
                $itemBlueprint->getDescription(),
            );

            $price = sprintf(
                "\t%dC \033[1;37m%dS \033[1;33m%dG\033[0m\n",
                $itemBlueprint->getItemPrice()->getC(),
                $itemBlueprint->getItemPrice()->getS(),
                $itemBlueprint->getItemPrice()->getG(),
            );
            $uiMessage .= $header;
            if ($this->detailed) {
                $uiMessage .=
                    $rarityAndLongDescription .
                    $price;
            }
        }

        $uiMessage .= "\n-----------------\n";

        Dispatcher::getInstance()->dispatch(new UiMessageEvent($uiMessage, $playerCommandQueue));
    }
}
