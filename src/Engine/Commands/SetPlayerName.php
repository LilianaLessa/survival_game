<?php

declare(strict_types=1);

namespace App\Engine\Commands;

use App\Engine\Component\InGameName;
use App\Engine\Component\PlayerCommandQueue;
use App\Engine\Component\WorldActor;
use App\Engine\Entity\EntityManager;
use App\System\Direction;

readonly class SetPlayerName implements InvokableCommandInterface
{
    public function __construct(
        private EntityManager $entityManager,
        private string $entityId,
        private string $name,
    ) {
    }

    public function __invoke(PlayerCommandQueue $playerCommandQueue)
    {
        $entityId = $this->entityId;
        $newName = $this->name;

        $entity = $this->entityManager->getEntityById($entityId);

        if ($entity) {
            $this->entityManager->updateEntityComponents(
                $entityId,
                new InGameName($newName)
            );
        }
    }
}
