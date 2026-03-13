<?php

namespace App\System\NPC;

use App\System\PresetLibrary\AbstractPreset;
use App\System\PresetLibrary\PresetDataType;

class NpcDialogPreset extends AbstractPreset
{
    //first node inserted will be always the root node.

    /** @var DialogNode[]  */
    private array $nodes;

    private DialogNode $rootNode;

    public function __construct(string $name)
    {
        parent::__construct(PresetDataType::NPC_DIALOG, $name);
    }

    public function addNode(DialogNode $node): void
    {
        $this->nodes[$node->id] = $node;
        $this->rootNode = $this->rootNode ?? $node;
    }

    public function getNode(?string $id): ?DialogNode
    {
        return $this->nodes[$id] ?? null;
    }

    public function getRootNode(): ?DialogNode
    {
        return $this->rootNode ?? null;
    }

}