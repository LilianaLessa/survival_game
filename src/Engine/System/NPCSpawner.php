<?php

namespace App\Engine\System;

use App\Engine\Component\Npc;
use App\Engine\Entity\EntityManager;
use App\System\NPC\NpcDefinitionPreset;
use App\System\NPC\NpcManager;

class NPCSpawner implements WorldSystemInterface
{
    public function __construct(
        private readonly NpcManager $npcManager,
        private readonly EntityManager $entityManager,
    )
    {
    }

    public function process(): void
    {
        //get all npcs from npc library
        $npcs = $this->npcLibrary->getAll();

        foreach ($npcs as $npc) {
            $this->spawn($npc);
        }
    }

    private function spawn(NpcDefinitionPreset $npc): void
    {
        //check if an NPC is already spawned
        if (!$this->npcManager->exists($npc)) {
            //create entity for npc
            $entity = Npc::createFromPreset($npc, $this->entityManager);
            $this->npcManager->register($npc, $entity);
        }
    }
}