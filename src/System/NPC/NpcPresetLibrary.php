<?php

namespace App\System\NPC;

use App\System\Helpers\ConsoleColorPalette;
use App\System\Helpers\Point2D;
use App\System\PresetLibrary\AbstractPreset;
use App\System\PresetLibrary\AbstractPresetLibrary;
use App\System\PresetLibrary\PresetDataType;

class NpcPresetLibrary extends AbstractPresetLibrary
{
    public function getAllDefinitions(): array
    {
        return $this->presetsByTypeAndName[PresetDataType::NPC_DEFINITION->value] ?? [];
    }

    public function getAllDialogs(): array
    {
        return $this->presetsByTypeAndName[PresetDataType::NPC_DIALOG->value] ?? [];
    }

    protected function createPreset(?PresetDataType $presetDataType, object $rawPreset): AbstractPreset
    {
        $factory = match ($presetDataType) {
            PresetDataType::NPC_DEFINITION => fn (object $rawPreset) => $this->createNpcDefinitionPreset($rawPreset),
            PresetDataType::NPC_DIALOG => fn (object $rawPreset) => $this->createNpcDialogPreset($rawPreset),
            default => fn (object $rawPreset) => new class ($rawPreset) extends AbstractPreset {},
        };

        return $factory($rawPreset);
    }

    protected function getPresetTypesToLoad(): array
    {
        return [
            PresetDataType::NPC_DIALOG,
            PresetDataType::NPC_DEFINITION,
        ];
    }

    private function createNpcDefinitionPreset(object $rawPreset): NpcDefinitionPreset
    {
        $npcPreset = new NpcDefinitionPreset(
            name: $rawPreset->name,
            symbol: $rawPreset->symbol,
        );

        $npcPreset->setDefaultColor(
            ConsoleColorPalette::tryFrom(
                $rawPreset->defaultColor ?? ''
            ) ?? ConsoleColorPalette::defaultForeground()
        );

        $npcPreset->setPosition(new Point2D(
            $rawPreset->position->x,
            $rawPreset->position->y
        ));

        $triggers = $rawPreset->triggers ?? [];
        foreach ($triggers as $trigger) {
            $triggerType = InteractionTriggerType::tryFrom($trigger->type);
            if ($triggerType) {
                $currentTrigger = new InteractionTrigger(
                    $triggerType,
                    $trigger->handler
                );
                $npcPreset->addTrigger($currentTrigger);
            }
        }

        $handlers =  $rawPreset->handlers ?? [];
        foreach ($handlers as $handler) {
            $handlerType = InteractionHandlerType::tryFrom($handler->type);
            if ($handlerType) {
                $handler = $handlerType->buildFromRawPreset($handler, $this);
                $npcPreset->addHandler($handler);
            }
        }

        return $npcPreset;
    }

    private function createNpcDialogPreset(object $rawPreset): NpcDialogPreset
    {
        $npcDialogPreset = new NpcDialogPreset(
            $rawPreset->name
        );

        foreach ($rawPreset->nodes as $node) {
            $currentNode = $npcDialogPreset->getNode($node->id) ?? new DialogNode(
                $node->id
            );

            $currentNode->setText($node->text);
            $npcDialogPreset->addNode($currentNode);

            $currentChoices = $node->choices ?? [];
            foreach ($currentChoices as $choice) {
                $choiceNode = $npcDialogPreset->getNode($choice->nextNode) ?? new DialogNode(
                    $choice->nextNode,
                );

                $actions = [];
                $currentActions = $choice->actions ?? [];
                foreach ($currentActions as $action) {
                    $actionType = DialogNodeChoiceActionType::tryFrom($action->type);
                    if ($actionType) {
                        $arrayParams = json_decode(json_encode($action->params ?? []), true);
                        $params = [];
                        foreach ($arrayParams as $name => $value) {
                            $params[] = new ChoiceActionParameter(
                                $name,
                                $value,
                            );
                        }

                        $parameterBag = new ChoiceActionParameterBag(
                            ...$params,
                        );

                        $actions[] = new DialogNodeChoiceAction(
                            $actionType,
                            $parameterBag
                        );
                    }
                }

                $conditions = [];
                $currentConditions = $choice->conditions ?? [];
                foreach ($currentConditions as $condition) {
                    $conditionType = DialogNodeChoiceConditionType::tryFrom($condition->type);
                    if ($conditionType) {
                        $arrayParams = json_decode(json_encode($condition->params ?? []), true);
                        $params = [];
                        foreach ($arrayParams as $name => $value) {
                            $params[] = new ChoiceConditionParameter(
                                $name,
                                $value,
                            );
                        }

                        $parameterBag = new ChoiceConditionParameterBag(
                            ...$params,
                        );

                        $conditions[] = new DialogNodeChoiceCondition(
                            $conditionType,
                            $parameterBag
                        );
                    }
                }

                $choice = new DialogNodeChoice(
                    $choice->id,
                    $choice->text,
                    $choiceNode,
                    $actions,
                    $conditions
                );

                $currentNode->addChoice($choice);
                $npcDialogPreset->addNode($choiceNode);
            }

            //todo add actions
        }

        return  $npcDialogPreset;
    }
}