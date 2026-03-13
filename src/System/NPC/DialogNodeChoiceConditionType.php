<?php

namespace App\System\NPC;
use App\Engine\Component\Item\Inventory;
use App\Engine\Component\Item\ItemOnInventory;
use App\Engine\Entity\Entity;

enum DialogNodeChoiceConditionType: String
{
    case NPC_STATE = "npcState";
    case PLAYER_HAS_ITEM = "playerHasItem";

    /**
     * @return callable(Entity, NpcState, ChoiceConditionParameterBag): bool
     */
    public function getConditionHandler(): \Closure
    {
        return match($this) {
            //default => (fn (Entity $triggerEntity, NpcState $state, ChoiceConditionParameterBag $parameterBag) => true),
            self::PLAYER_HAS_ITEM => function (Entity $triggerEntity, NpcState $state, ChoiceConditionParameterBag $parameterBag) : bool
            {
                $result = false;
                [$item, $quantity] = $parameterBag->parameters;
                $itemName = $item->value;
                $quantityType = $quantity->name;
                $quantity = $quantity->value;
                /** @var ?Inventory $inventory */
                $inventory = $triggerEntity->getComponent(Inventory::class);

                if ($inventory !== null) {
                    $items = $inventory->getItems()->getEntitiesWithComponents(ItemOnInventory::class);
                    /** @var ItemOnInventory $item */
                    foreach ($items as [$item]) {
                        if ($item->getItemBlueprint()->getName() === $itemName) {
                            $amount = $item->getAmount();
                            $compareMethod = match ($quantityType) {
                                default => fn ($amount, $required) => $amount == $required,
                                "minQuantity" => fn ($amount, $required) => $amount >= $required,
                            };
                            $result = $compareMethod($amount, $quantity);
                            break;
                        }
                    }
                }

                return $result;
            },
            self::NPC_STATE => function (Entity $triggerEntity, NpcState $state, ChoiceConditionParameterBag $parameterBag) : bool {
                $result = true;
                foreach($parameterBag->parameters as $parameter) { //LOGICAL AND
                    switch ($parameter->name) {
                        case "flagOn":
                            $flags = $state->getFlags();
                            $value = $flags[$parameter->value] ?? false;
                            if ($value === false) {
                                $result = false;
                            }
                            break;
                        case "flagOff":
                            $flags = $state->getFlags();
                            $value = $flags[$parameter->value] ?? false;
                            if ($value === true) {
                                $result = false;
                            }
                            break;
                        default:
                            break;
                    }
                }

                return $result;
            },
        };
    }
}
