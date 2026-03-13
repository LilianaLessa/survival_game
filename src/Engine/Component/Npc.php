<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\NPC\NpcDefinitionPreset;

class Npc implements ComponentInterface
{
    public function __construct(private readonly NpcDefinitionPreset $npc)
    {
    }

    public static function createFromPreset(
        NpcDefinitionPreset $npc,
        EntityManager $entityManager,
    ): Entity {
        return $entityManager->createEntity(
            new self($npc),
            new MapSymbol($npc->getSymbol()),
            new InGameName($npc->getInGameName()),
            new DefaultColor($npc->getDefaultColor()),
            new MapPosition(...$npc->getPosition()->toArray()),
            new Collideable(),
        );
    }
}
