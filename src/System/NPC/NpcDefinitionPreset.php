<?php

namespace App\System\NPC;

use App\System\Helpers\ConsoleColorPalette;
use App\System\Helpers\Point2D;
use App\System\PresetLibrary\AbstractPreset;
use App\System\PresetLibrary\PresetDataType;

class NpcDefinitionPreset extends AbstractPreset
{
    private ConsoleColorPalette $defaultColor;
    private string $inGameName;
    private Point2D $position;

    /** @var InteractionTrigger[]  */
    private array $triggers = [];

    /** @var AbstractInteractionHandler[] */
    private array $handlers = [];

    /** @var array<string, DialogNode> */
    private array $dialogTree = [];

    public function __construct(
        string $name,
        private readonly ?string $symbol,
    )
    {
        parent::__construct(PresetDataType::NPC_DEFINITION, $name);

        $this->defaultColor = ConsoleColorPalette::defaultForeground();
        $this->inGameName = $name;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getDefaultColor(): ConsoleColorPalette
    {
        return $this->defaultColor;
    }

    public function setDefaultColor(ConsoleColorPalette $defaultColor): self
    {
        $this->defaultColor = $defaultColor;
        return $this;
    }

    public function getInGameName(): string
    {
        return $this->inGameName;
    }

    public function setInGameName(string $inGameName): self
    {
        $this->inGameName = $inGameName;
        return $this;
    }

    public function getPosition(): Point2D
    {
        return $this->position;
    }

    public function setPosition(Point2D $position): void
    {
        $this->position = $position;
    }

    public function addToDialogTree(DialogNode $dialogNode): void
    {
        $this->dialogTree[$dialogNode->id] = $dialogNode;
    }

    public function getDialogTree(): array
    {
        return $this->dialogTree;
    }

    public function addTrigger(InteractionTrigger $trigger): void
    {
        $this->triggers[$trigger->getType()->value] = $trigger;
    }

    public function addHandler(AbstractInteractionHandler $handler): void
    {
        $this->handlers[$handler->id] = $handler;
    }

    public function getHandlerByTrigger(InteractionTriggerType $triggerType): ?AbstractInteractionHandler
    {
        $trigger = $this->triggers[$triggerType->value] ?? null;
        $handlerId = $trigger?->getInteractionHandlerId();

        return $this->handlers[$handlerId] ?? null;
    }

    /**
     * @return InteractionTrigger[]
     */
    public function getTriggers(): array
    {
        return $this->triggers;
    }
}