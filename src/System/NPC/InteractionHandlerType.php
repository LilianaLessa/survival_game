<?php

namespace App\System\NPC;

enum InteractionHandlerType: string
{
    case DIALOG = "dialog";

    public function buildFromRawPreset(object $rawPreset, NpcPresetLibrary $npcPresetLibrary): AbstractInteractionHandler
    {
        $arrayData = json_decode(json_encode($rawPreset), true);
        $factory = match($this) {
            self::DIALOG => fn ($arrayData) => $this->buildDialogHandler($arrayData, $npcPresetLibrary)
        };

        return $factory($arrayData, $npcPresetLibrary);
    }

    private function buildDialogHandler(array $rawPreset, NpcPresetLibrary $npcPresetLibrary): InteractionHandlerDialog
    {
        [
            "id" => $id,
            "dialogName" => $dialogName
        ] = $rawPreset;

        return new InteractionHandlerDialog(
            $id,
            $dialogName,
            $npcPresetLibrary
        );
    }
}
