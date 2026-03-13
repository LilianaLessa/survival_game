<?php

namespace App\System\NPC;

use App\Engine\Entity\Entity;
use App\System\PresetLibrary\PresetDataType;

class InteractionHandlerDialog extends  AbstractInteractionHandler
{
    public function __construct(
        string $id,
        public readonly string $dialogName,
        public readonly NpcPresetLibrary $npcPresetLibrary
    )
    {
        parent::__construct($id);
    }

    function handle(Entity $triggerEntity, NpcState $npcState): AbstractNpcInteractionResult
    {
        /** @var NpcDialogPreset $dialogPreset */
        [ $dialogPreset ] = $this->npcPresetLibrary->getPresetByNameAndTypes(
            $this->dialogName,
            PresetDataType::NPC_DIALOG
        );

        //todo should the dialog state be separated from other states like flags?

        $currentDialogNode = $npcState->getCurrentDialogNode() ?? $dialogPreset->getRootNode();

        $choices = [];
        $rawChoices = $currentDialogNode->getChoices();
        //todo check here the conditions to show dialog choice
        foreach ($rawChoices as $id => $rawChoice) {
            /** @var DialogNodeChoiceCondition $condition */
            foreach ($rawChoice->conditions as $condition) { //for now logic AND 
                //if any of these is false, continue outer loop, to next choice.
                $handler = $condition->conditionType->getConditionHandler();
                if ($handler($triggerEntity, $npcState, $condition->parameterBag) === false) {
                    continue 2;
                }
            }

            $choices[$id] = $rawChoice->text;
        }

        //todo maybe this shouldn't be set here.
        $npcState->setCurrentDialogNode($currentDialogNode);

        return new NpcInteractionResultDialog(
            $currentDialogNode->getText(),
            $choices
        );

    }
}