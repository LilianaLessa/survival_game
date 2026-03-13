<?php

namespace App\System\NPC;
use App\Engine\Component\Item\Inventory;
use App\Engine\Component\Item\ItemOnInventory;
use App\Engine\Entity\Entity;
use App\System\PresetLibrary\PresetDataType;

enum DialogNodeChoiceActionType: String
{
    case NPC_STATE_CHANGE = "npcStateChange";
    case END_NPC_INTERACTION = "endNpcInteraction";
    case CONDITIONAL_NODE = "conditionalNode";

    case ADD_MONEY = "addMoney";
    case ADD_EXP = "addExp";
    case REMOVE_ITEM = "removeItem";
    case ADD_ITEM = "addItem";

    /**
     * todo actions can fail. in this case, the whole action chain should fail and be rolled back.
    */
    /**
     * @return callable(Entity, NpcState, ChoiceActionParameterBag): void
     */
    public function getActionHandler(): \Closure
    {
        return match($this) {
            self::CONDITIONAL_NODE => function (Entity $triggerEntity,  NpcState $state, ChoiceActionParameterBag $parameterBag): void {

                $conditions = $parameterBag->getParameterByName("conditions")?->value ?? [];
                $successNode = $parameterBag->getParameterByName("successNode");
                $failureNode = $parameterBag->getParameterByName("failureNode");

                $result = true;
                foreach ($conditions as $condition) {
                    $condition = self::arrayToObject($condition);
                    $conditionType = DialogNodeChoiceConditionType::tryFrom($condition->type);
                    if ($conditionType) {
                        $params = [];
                        foreach ($condition->params ?? [] as $name => $value) {
                            $params[] = new ChoiceConditionParameter(
                                $name,
                                $value,
                            );
                        }

                        $conditionParameterBag = new ChoiceConditionParameterBag(
                            ...$params,
                        );

                        $handler = $conditionType->getConditionHandler();

                        if ($handler($triggerEntity,$state, $conditionParameterBag) === false) {
                            $result = false;
                            break;
                        }
                    }
                }

                $resultNodeName = ($result ? $successNode : $failureNode)->value;
                /** @var InteractionHandlerDialog $dialogHandler */
                $dialogHandler = $state->npcDefinitionPreset->getHandlerByTrigger(InteractionTriggerType::ON_DIALOG_START);
                /** @var NpcDialogPreset $dialog */
                [$dialog] = $dialogHandler->npcPresetLibrary->getPresetByNameAndTypes(
                    $dialogHandler->dialogName,
                    PresetDataType::NPC_DIALOG
                );

                $state->setCurrentDialogNode($dialog->getNode($resultNodeName));
            },
            self::END_NPC_INTERACTION => function (Entity $triggerEntity, NpcState $state, ChoiceActionParameterBag $parameterBag): void {
                $state->end();
            },
            self::NPC_STATE_CHANGE => function (Entity $triggerEntity, NpcState $state, ChoiceActionParameterBag $parameterBag): void {
                //todo this is debug for npcStateChange, so apply changes directly.
                foreach($parameterBag->parameters as $parameter) {
                    switch ($parameter->name) {
                        case "flagOn":
                            $state->setFlag($parameter->value, true);
                            break;
                        case "flagOff":
                            $state->setFlag($parameter->value, false);
                            break;
                        default:
                            break;
                    }

                }
            },
            self::REMOVE_ITEM => function (Entity $triggerEntity, NpcState $state, ChoiceActionParameterBag $parameterBag): void {

                [$item, $quantity] = $parameterBag->parameters;
                $itemName = $item->value;;
                $quantity = $quantity->value;

                /** @var ?Inventory $inventory */
                $inventory = $triggerEntity->getComponent(Inventory::class);

                if ($inventory !== null) {
                    $items = $inventory->getItems()->getEntitiesWithComponents(ItemOnInventory::class);
                    /** @var ItemOnInventory $item */
                    foreach ($items as [$item]) {
                        if ($item->getItemBlueprint()->getName() === $itemName) {
                            $amount = $item->getAmount(); //todo all the item stacks should be combined when checking amounts
                            if ($amount - $quantity >= 0) {
                                $item->decreaseAmount($quantity);
                            }
                          
                            break;
                        }
                    }
                }
            },
            self::ADD_ITEM => function (Entity $triggerEntity, NpcState $state, ChoiceActionParameterBag $parameterBag): void {
                //todo
            },
            self::ADD_EXP => function (Entity $triggerEntity, NpcState $state, ChoiceActionParameterBag $parameterBag): void {
                //todo
            },
            self::ADD_MONEY => function (Entity $triggerEntity, NpcState $state, ChoiceActionParameterBag $parameterBag): void {
                //todo
            },
            //default => function (Entity $triggerEntity, NpcState $state, ChoiceActionParameterBag $parameterBag): void {}
        };
    }

    private static function arrayToObject(mixed $value): mixed
    {
        // Recurse into arrays
        if (is_array($value)) {
            // Detect associative vs list
            $isAssoc = array_keys($value) !== range(0, count($value) - 1);

            if ($isAssoc) {
                $obj = new \stdClass();
                foreach ($value as $key => $item) {
                    $obj->{$key} = self::arrayToObject($item);
                }
                return $obj;
            }

            // Keep numeric-key arrays as arrays, just recurse values
            foreach ($value as $k => $item) {
                $value[$k] = self::arrayToObject($item);
            }
            return $value;
        }

        // Recurse into stdClass as well if needed
        if ($value instanceof \stdClass) {
            foreach ($value as $k => $v) {
                $value->{$k} = self::arrayToObject($v);
            }
        }

        return $value;
    }
}
