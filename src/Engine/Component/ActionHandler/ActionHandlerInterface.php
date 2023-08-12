<?php

declare(strict_types=1);

namespace App\Engine\Component\ActionHandler;

use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;

interface ActionHandlerInterface
{
    public function execute(EntityManager $entityManager, Entity $targetEntity): void;
}
