<?php

declare(strict_types=1);

namespace App\Engine\Commands;

use App\Engine\Component\PlayerCommandQueue;
use App\Engine\Component\WorldActor;
use App\Engine\Entity\EntityManager;
use App\System\Direction;

readonly class WorldAction implements InvokableCommandInterface
{
    public function __construct(
        private EntityManager $entityManager,
        private string        $entityId,
        private string        $actionType,
        private Direction     $direction,
    ) {
    }

    public function __invoke(PlayerCommandQueue $playerCommandQueue)
    {
        $entity = $this->entityManager->getEntityById($this->entityId);
        if ($entity) {
            /** @var ?WorldActor $worldActor */
            $worldActor = $entity->getComponent(WorldActor::class);
            $worldActor?->addToQueue($this);
        }
    }

    public function getActionType(): string
    {
        return $this->actionType;
    }

    public function getDirection(): Direction
    {
        return $this->direction;
    }
}
