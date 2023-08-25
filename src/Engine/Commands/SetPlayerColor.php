<?php

declare(strict_types=1);

namespace App\Engine\Commands;

use App\Engine\Component\DefaultColor;
use App\Engine\Component\PlayerCommandQueue;
use App\Engine\Entity\EntityManager;
use App\System\Helpers\ConsoleColorPalette;

readonly class SetPlayerColor implements InvokableCommandInterface
{
    public function __construct(
        private EntityManager $entityManager,
        private string $entityId,
        private string $color,
    ) {
    }

    public function __invoke(PlayerCommandQueue $playerCommandQueue)
    {
        $entityId = $this->entityId;

        $entity = $this->entityManager->getEntityById($entityId);

        if ($entity) {
            $this->entityManager->updateEntityComponents(
                $entityId,
                new DefaultColor(ConsoleColorPalette::tryFrom($this->color) ?? ConsoleColorPalette::SYSTEM_YELLOW)
            );
        }
    }
}
