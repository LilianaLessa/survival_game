<?php

namespace App\System\NPC;

use App\Engine\Entity\Entity;

class NpcManager
{
    /**
     * @var array<string, NpcDefinitionPreset> Registered NPCs by entity UUID
     */
    private array $registeredByEntityId = [];

    /**
     * @var array<string, NpcDefinitionPreset> Registered NPCs by preset name
     */
    private array $registeredByPresetName = [];

    /**
     * @var array<string, array<string, NpcState>> $npcStates[playerUUID][npcEntityUUID]
     */
    private array $npcStates = [];

    public function exists(NpcDefinitionPreset $npc): bool
    {
        return isset($this->registeredByPresetName[$npc->getName()]);
    }

    public function register(NpcDefinitionPreset $npc, Entity $entity): void
    {
        $this->registeredByEntityId[$entity->getId()] = $npc;
        $this->registeredByPresetName[$npc->getName()] = $npc;
    }

    public function interact(
        Entity                 $triggerEntity,
        Entity                 $npcEntity, //pass the NPC entity? ?
        InteractionType        $type,
        InteractionTriggerType $triggerType,
        ?array                 $payload = null
    ): ?AbstractNpcInteractionResult {
        $triggerEntityUUID = $triggerEntity->id;
        $npcEntityUUID = $npcEntity->id;

        if (!isset($this->registeredByEntityId[$npcEntityUUID])) {
            return null;
        }

        $npc = $this->registeredByEntityId[$npcEntityUUID];
        $state = $this->getState($triggerEntityUUID, $npcEntityUUID) ?? new NpcState($npc);

        $result = null;
        switch ($type) {
            case InteractionType::DIALOG_CHOICE:
                $choiceId = $payload['choiceId'] ?? null;
                if ($choiceId !== null) {
                    $result = $state->dialogChoose($triggerEntity, $choiceId);
                }
                break;

            case InteractionType::END_INTERACTION:
                $state->end();
                break;

            case InteractionType::START_INTERACTION:
                $result = $state->start($triggerEntity, $triggerType);
                break;

            default:
                // Optionally throw or log unknown interaction
                throw new \Exception(sprintf("InteractionType::%s not found", $type->name));
                break;
        }


        //todo if result has stateChange actions


        $this->npcStates[$triggerEntityUUID][$npcEntityUUID] = $state;

        return $result;
    }

    private function getState(string $playerUUID, string $npcEntityUUID): ?NpcState
    {
        return $this->npcStates[$playerUUID][$npcEntityUUID] ?? null;
    }
}
