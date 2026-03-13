<?php

namespace App\System\NPC;

use App\Engine\Entity\Entity;

class NpcState
{
    /**
     * @var string Current node ID in the dialog tree
     */
    private string $currentNodeId;

    /**
     * @var array<string, bool> Optional flags to track decisions or paths
     */
    private array $flags = [];

    /**
     * @var bool Whether this interaction is ended
     */
    private bool $ended = false;

    /**
     * @var array<string, mixed> Optional custom state data
     */
    private array $data = [];

    private ?DialogNode $currentDialogNode = null;

    /**
     * Constructor initializes with root dialog node
     */
    public function __construct(
        public readonly NpcDefinitionPreset $npcDefinitionPreset,
    ) {
        //todo this needs the initial state of the npc
        $a = 1;
    }



    public function isEnded(): bool
    {
        return $this->ended;
    }

    public function end(): void
    {
        $this->ended = true;
        $this->setCurrentDialogNode(null);
    }

    public function start(Entity $triggerEntity, InteractionTriggerType $trigger): ?AbstractNpcInteractionResult
    {
        $handler = $this->npcDefinitionPreset->getHandlerByTrigger($trigger);
        return $handler?->handle($triggerEntity, $this);
    }

    public function getFlags(): array
    {
        return $this->flags;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getCurrentDialogNode(): ?DialogNode
    {
        return $this->currentDialogNode;
    }

    public function dialogChoose(Entity $triggerEntity, string $choiceId): ?AbstractNpcInteractionResult
    {
        $result = null;
        /** @var  $choice */
        $choice = $this->currentDialogNode?->getChoices()[$choiceId];
        $nextNode = $choice?->nextNode ?? null;

        if ($nextNode) {

            //todo check if any state change action exists.
            //   if so, apply it
            foreach ( $choice->actions as $action) {
                $handler = $action->actionType->getActionHandler();
                $handler($triggerEntity, $this, $action->parameterBag);
            }

            $handler = $this->npcDefinitionPreset->getHandlerByTrigger(InteractionTriggerType::ON_DIALOG_START);
            if ($nextNode->id !== null) {
                $this->currentDialogNode = $nextNode;
            }

            $result = $handler?->handle($triggerEntity, $this);
        }

        return $result;
    }

    public function setCurrentDialogNode(?DialogNode $currentDialogNode): void
    {
        $this->currentDialogNode = $currentDialogNode;
    }

    public function setFlag(string $flagName, bool $value): void
    {
        $this->flags[$flagName] = $value;
    }
}