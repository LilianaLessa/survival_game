<?php

namespace App\System\NPC;

use App\Engine\Entity\Entity;

abstract class AbstractInteractionHandler
{
   public function __construct(public readonly string $id)
   {
   }

   abstract function handle(Entity $triggerEntity, NpcState $npcState): AbstractNpcInteractionResult;
}